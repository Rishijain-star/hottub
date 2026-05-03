<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;
use App\Support\MaintenancePlanDates;

class CustomerController extends Controller
{
    public function overview()
    {
        $user = auth()->user();

        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            \App\Models\PackageRequest::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }
        if (Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
            \App\Models\PackageRequest::where('user_id', $user->id)
                ->where('status', 'cancellation_scheduled')
                ->whereNotNull('cancellation_effective_at')
                ->where('cancellation_effective_at', '<=', now())
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
        }
        
        // Fetch all leads for this customer (email-based) - include non-converted for notifications
        $leads = \App\Models\Lead::whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->whereIn('status', ['new', 'converted'])
            ->orderByRaw("CASE WHEN stage = 'Delivered' THEN 1 ELSE 2 END")
            ->orderBy('updated_at', 'desc')
            ->get();

        // Also check via messages to ensure we don't miss any linked leads (e.g. if email was different but message was sent)
        $messageLeadIds = \App\Models\Message::where('receiver_id', $user->id)
            ->whereNotNull('lead_id')
            ->pluck('lead_id')
            ->unique();
        
        if ($messageLeadIds->isNotEmpty()) {
            $messageLeads = \App\Models\Lead::whereIn('id', $messageLeadIds)
                ->whereIn('status', ['new', 'converted'])
                ->get();
            
            // Merge and ensure uniqueness
            $leads = $leads->merge($messageLeads)->unique('id')->sortByDesc(function($l) {
                return [$l->stage === 'Delivered' ? 1 : 0, $l->updated_at];
            });
        }

        // Attach dealer and their packages to each lead
        foreach ($leads as $lead) {
            $lead->dealer = null;
            $lead->packages = collect();
            
            // Find the active purchase/assigned dealer
            $dealerId = $lead->assigned_dealer_id;
            if (!$dealerId) {
                $activePurchase = \App\Models\LeadPurchase::where('lead_id', $lead->id)->whereNotIn('stage', ['Lost'])->first();
                if ($activePurchase) $dealerId = $activePurchase->dealer_id;
            }

            if ($dealerId) {
                $lead->dealer = \App\Models\User::find($dealerId);
                if ($lead->dealer) {
                    $pkgQuery = \App\Models\MaintenancePackage::where('dealer_id', $lead->dealer->id)
                        ->where('status', 'active');
                    if (Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
                        $pkgQuery->orderByDesc('is_most_popular');
                    }
                    $lead->packages = $pkgQuery->orderBy('price')->get();
                }
            }
        }

        // We only show converted leads in the main UI, but we needed all leads above for potential notification context
        $convertedLeads = $leads->filter(fn($l) => $l->status === 'converted');

        $recentActivity = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        if (request()->ajax() && request()->has('page')) {
            return view('customer.overview', compact('recentActivity'))->render();
        }

        return view('customer.overview', ['leads' => $convertedLeads, 'recentActivity' => $recentActivity]);
    }

    public function confirmDeposit(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'notification_id' => 'required|exists:notifications,id',
            'action' => 'required|in:accept,reject',
        ]);

        $notification = \App\Models\Notification::findOrFail($data['notification_id']);
        if ($notification->user_id !== $user->id) abort(403);

        $leadId = $notification->data['lead_id'] ?? null;
        $lead = \App\Models\Lead::findOrFail($leadId);

        // Lock decision: if lead already has a confirmed deposit, don't allow accepting another
        if ($data['action'] === 'accept' && $lead->deposit_confirmed && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== ($notification->data['dealer_id'] ?? null)) {
            return response()->json(['ok' => false, 'msg' => 'You have already confirmed a deposit with another dealer. This decision is final.'], 422);
        }

        if ($data['action'] === 'accept') {
            $dealerId = $notification->data['dealer_id'];
            
            // 1. Confirm deposit and assign the lead to the winner
            $lead->deposit_confirmed = true;
            $lead->assigned_dealer_id = $dealerId;
            
            // Sync stage from purchase to lead to prevent reset on refresh
            $winnerPurchase = \App\Models\LeadPurchase::where('lead_id', $lead->id)
                ->where('dealer_id', $dealerId)
                ->first();
            
            if ($winnerPurchase) {
                $lead->stage = $winnerPurchase->stage ?: 'Deposit';
            } else {
                $lead->stage = 'Deposit';
            }
            
            $lead->save();

            // 2. Mark the winning purchase as confirmed
            if ($winnerPurchase) {
                $winnerPurchase->stage = $lead->stage;
                $winnerPurchase->save();
            }

            // 3. Mark all OTHER purchases as 'Lost'
            \App\Models\LeadPurchase::where('lead_id', $lead->id)
                ->where('dealer_id', '!=', $dealerId)
                ->update(['stage' => 'Lost']);

            // 3.1. Mark all OTHER pending deposit notifications for this lead as 'read'
            // and update their message to indicate rejection
            \App\Models\Notification::where('user_id', $user->id)
                ->where('type', 'deposit_confirmation')
                ->where('id', '!=', $notification->id)
                ->where('data->lead_id', $lead->id)
                ->update([
                    'read' => true,
                    'message' => "✗ This request was automatically rejected because you confirmed with another dealer."
                ]);

            // 4. Log activities
            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $dealerId,
                'type' => 'activity',
                'content' => "Customer accepted deposit confirmation for this dealer."
            ]);

            // Update current notification message
            $notification->update([
                'read' => true,
                'message' => "✓ You have accepted the deposit confirmation for this dealer."
            ]);

            // Notify the winning dealer
            \App\Models\Notification::create([
                'user_id' => $dealerId,
                'message' => "Customer has accepted your deposit confirmation request for lead: {$lead->name}.",
                'type' => 'deposit_accepted',
                'data' => ['lead_id' => $lead->id]
            ]);

            return response()->json(['ok' => true, 'msg' => 'Deposit confirmed successfully. This lead has been assigned to the dealer.']);
        } else {
            $rejectingDealerId = $notification->data['dealer_id'] ?? null;

            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $rejectingDealerId,
                'type' => 'activity',
                'content' => "Customer rejected deposit confirmation."
            ]);

            $notification->update([
                'read' => true,
                'message' => "✗ You have rejected the deposit confirmation for this dealer."
            ]);

            // ── Notify the dealer who requested the deposit ────────────
            if ($rejectingDealerId) {
                \App\Models\Notification::create([
                    'user_id' => $rejectingDealerId,
                    'message' => "Customer {$lead->name} has rejected your deposit confirmation request.",
                    'type' => 'deposit_rejected',
                    'data' => ['lead_id' => $lead->id, 'customer_id' => $user->id],
                ]);
            }

            // ── Notify every manufacturer who purchased this lead ──────
            $manufacturerIds = \App\Models\LeadPurchase::where('lead_id', $lead->id)
                ->where('buyer_role', 'manufacturer')
                ->pluck('dealer_id')
                ->unique();
            foreach ($manufacturerIds as $manufacturerId) {
                \App\Models\Notification::create([
                    'user_id' => $manufacturerId,
                    'message' => "Customer {$lead->name} has rejected the deposit confirmation for lead #{$lead->id}.",
                    'type' => 'deposit_rejected',
                    'data' => [
                        'lead_id' => $lead->id,
                        'customer_id' => $user->id,
                        'dealer_id' => $rejectingDealerId,
                    ],
                ]);
            }

            return response()->json(['ok' => true, 'msg' => 'Deposit confirmation rejected.']);
        }
    }

    public function markNotificationRead(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $notification = \App\Models\Notification::where('user_id', $user->id)->findOrFail($request->id);
        $notification->update(['read' => true]);
        return response()->json(['ok' => true]);
    }

    public function storePackageRequest(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'package_id' => 'required|exists:maintenance_packages,id',
            'lead_id' => 'required|exists:leads,id',
            'message' => 'nullable|string',
        ]);

        $package = \App\Models\MaintenancePackage::find($data['package_id']);
        $lead = \App\Models\Lead::find($data['lead_id']);

        // Use the dealer linked to the specific lead/product
        $dealer_id = $lead->assigned_dealer_id ?: $package->dealer_id;
        
        $packageRequest = \App\Models\PackageRequest::create([
            'user_id' => $user->id,
            'dealer_id' => $dealer_id,
            'package_id' => $package->id,
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        \App\Models\Notification::create([
            'user_id' => $dealer_id,
            'message' => 'Customer purchased a maintenance plan: ' . $package->name,
            'type' => 'maintenance_plan_purchase',
            'data' => [
                'customer_id' => $user->id,
                'customer_name' => $user->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'package_request_id' => $packageRequest->id,
            ],
        ]);

        return back()->with('success', 'Package request submitted to your dealer.');
    }

    public function cancelPackageRequest(\Illuminate\Http\Request $request, \App\Models\PackageRequest $packageRequest)
    {
        $user = auth()->user();
        if ((int) $packageRequest->user_id !== (int) $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'mode' => 'required|in:immediate,scheduled',
            'reason' => 'required|string|max:2000',
        ]);

        if (!in_array($packageRequest->status, ['active', 'cancellation_scheduled'], true)) {
            return back()->with('error', 'Only active plans can be cancelled.');
        }

        if ($data['mode'] === 'scheduled') {
            $effectiveAt = $packageRequest->expiry_date ?? now();
            $packageRequest->update([
                'status' => 'cancellation_scheduled',
                'cancellation_type' => 'scheduled',
                'cancellation_requested_at' => now(),
                'cancellation_effective_at' => $effectiveAt,
                'cancellation_reason' => trim((string) $data['reason']),
            ]);

            Notification::create([
                'user_id' => $packageRequest->dealer_id,
                'message' => 'Customer scheduled cancellation for ' . ($effectiveAt ? $effectiveAt->format('d M Y') : now()->format('d M Y')),
                'type' => 'maintenance_plan_cancel_scheduled',
                'data' => [
                    'customer_id' => $user->id,
                    'customer_name' => $user->name,
                    'package_request_id' => $packageRequest->id,
                    'effective_at' => $effectiveAt?->toDateTimeString(),
                ],
            ]);

            return back()->with('success', 'Plan will be cancelled at end of term.');
        }

        $packageRequest->update([
            'status' => 'cancelled',
            'cancellation_type' => 'immediate',
            'cancellation_requested_at' => now(),
            'cancellation_effective_at' => now(),
            'cancelled_at' => now(),
            'cancellation_reason' => trim((string) $data['reason']),
        ]);

        Notification::create([
            'user_id' => $packageRequest->dealer_id,
            'message' => 'Customer cancelled the plan immediately',
            'type' => 'maintenance_plan_cancel_immediate',
            'data' => [
                'customer_id' => $user->id,
                'customer_name' => $user->name,
                'package_request_id' => $packageRequest->id,
                'cancelled_at' => now()->toDateTimeString(),
            ],
        ]);

        return back()->with('success', 'Plan cancelled immediately.');
    }

    public function reactivatePackageRequest(\Illuminate\Http\Request $request, \App\Models\PackageRequest $packageRequest)
    {
        $user = auth()->user();
        if ((int) $packageRequest->user_id !== (int) $user->id) {
            abort(403);
        }

        if (!in_array($packageRequest->status, ['cancellation_scheduled', 'cancelled'], true)) {
            return back()->with('error', 'Only cancelled plans can be reactivated.');
        }

        if ($packageRequest->status === 'cancellation_scheduled') {
            $packageRequest->update([
                'status' => 'active',
                'cancellation_type' => null,
                'cancellation_requested_at' => null,
                'cancellation_effective_at' => null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);
        } else {
            $planType = MaintenancePlanDates::normalizeType(optional($packageRequest->package)->plan_type);
            $dates = MaintenancePlanDates::calculate($planType);

            $packageRequest->update([
                'status' => 'active',
                'start_date' => $dates['start_date'],
                'expiry_date' => $dates['expiry_date'],
                'next_due_date' => $dates['next_due_date'],
                'cancellation_type' => null,
                'cancellation_requested_at' => null,
                'cancellation_effective_at' => null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);
        }

        Notification::create([
            'user_id' => $packageRequest->dealer_id,
            'message' => 'Customer reactivated the plan',
            'type' => 'maintenance_plan_reactivated',
            'data' => [
                'customer_id' => $user->id,
                'customer_name' => $user->name,
                'package_request_id' => $packageRequest->id,
                'reactivated_at' => now()->toDateTimeString(),
            ],
        ]);

        return back()->with('success', 'Your plan has been reactivated');
    }

    public function myHotTub()
    {
        $user = auth()->user();
        
        // Fetch all converted leads for this customer (email-based)
        $leads = \App\Models\Lead::whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->where('status', 'converted')
            ->orderByRaw("CASE WHEN stage = 'Delivered' THEN 1 ELSE 2 END")
            ->orderBy('updated_at', 'desc')
            ->get();

        // Also check via messages to ensure we don't miss any linked leads
        $messageLeadIds = \App\Models\Message::where('receiver_id', $user->id)
            ->whereNotNull('lead_id')
            ->pluck('lead_id')
            ->unique();
        
        if ($messageLeadIds->isNotEmpty()) {
            $messageLeads = \App\Models\Lead::whereIn('id', $messageLeadIds)
                ->where('status', 'converted')
                ->get();
            
            // Merge and ensure uniqueness
            $leads = $leads->merge($messageLeads)->unique('id')->sortByDesc(function($l) {
                return [$l->stage === 'Delivered' ? 1 : 0, $l->updated_at];
            });
        }

        $checklists = \App\Models\ServiceChecklist::whereIn('lead_id', $leads->pluck('id'))
            ->with('dealer')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.my-hot-tub', compact('leads', 'checklists'));
    }
    public function serviceRequests()
    {
        $user = auth()->user();
        $requests = \App\Models\ServiceRequest::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $parts = \App\Models\Part::where('status', 'active')->get();
        $services = \App\Models\Service::where('status', 'active')->get();

        // Fetch all products owned by the customer to link to requests
        $leads = \App\Models\Lead::whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->where('status', 'converted')
            ->whereNotNull('assigned_dealer_id')
            ->with('dealer')
            ->get();

        return view('customer.service-requests', compact('requests', 'parts', 'services', 'leads'));
    }

    public function confirmServiceRequest(\Illuminate\Http\Request $request, \App\Models\ServiceRequest $serviceRequest)
    {
        $user = auth()->user();
        if ($serviceRequest->user_id !== $user->id) abort(403);

        $data = $request->validate([
            'customer_review' => 'nullable|string',
            'customer_signature' => 'required|string',
        ]);

        $signature_image = $request->customer_signature;
        if (preg_match('/^data:image\/\w+;base64,/', $signature_image)) {
            $signature_image = substr($signature_image, strpos($signature_image, ',') + 1);
        }
        $signature_image = str_replace(' ', '+', $signature_image);
        $imageName = uniqid('', true) . '.png';
        $raw = base64_decode($signature_image, true);
        if ($raw === false) {
            return back()->with('error', 'Could not read signature. Please try again.');
        }
        \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $imageName, $raw);

        $serviceRequest->update([
            'customer_review' => $data['customer_review'],
            'customer_signature' => 'signatures/' . $imageName,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Service confirmed and marked as completed.');
    }

    public function storeServiceRequest(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'type' => 'required|in:part,service',
            'product_id' => 'required|integer',
            'lead_id' => 'required|exists:leads,id', // Selected product (lead)
            'message' => 'required|string|min:3|max:5000',
        ]);

        // Find linked dealer via the selected lead
        $lead = \App\Models\Lead::where('id', $data['lead_id'])
            ->where('status', 'converted')
            ->first();

        if (!$lead) {
            return back()->with('error', 'Invalid product selected.');
        }

        $dealerId = $lead->assigned_dealer_id;

        $productName = '';
        if ($data['type'] === 'part') {
            $p = \App\Models\Part::find($data['product_id']);
            $productName = $p ? $p->name : 'Unknown Part';
        } else {
            $s = \App\Models\Service::find($data['product_id']);
            $productName = $s ? $s->name : 'Unknown Service';
        }

        // Include the product name (make/model) in the request title for clarity
        $fullProductName = ($lead->delivery_details['make'] ?? 'Product') . ' ' . ($lead->delivery_details['model'] ?? '') . ' - ' . $productName;

        \App\Models\ServiceRequest::create([
            'user_id' => $user->id,
            'dealer_id' => $dealerId,
            'lead_id' => $lead->id,
            'type' => $data['type'],
            'product_id' => $data['product_id'],
            'product_name' => $fullProductName,
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        \App\Models\Notification::create([
            'user_id' => $dealerId,
            'message' => 'New service request from ' . $user->name,
        ]);

        return back()->with('success', 'Service request submitted successfully.');
    }

    public function requestHistory()
    {
        $user = auth()->user();
        $history = \App\Models\ServiceRequest::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get();
        return view('customer.request-history', compact('history'));
    }
    public function messages()
    {
        return view('customer.messages');
    }
    public function profile()
    {
        return view('customer.profile');
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:2000',
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateProfileImage(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->update(['profile_picture' => $path]);
        }

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function serviceHistory()
    {
        return redirect()->route('customer.request-history');
    }

    public function signService(Request $request, \App\Models\ServiceChecklist $checklist)
    {
        $user = auth()->user();
        $lead = \App\Models\Lead::whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->where('id', $checklist->lead_id)
            ->first();
        if (!$lead) {
            abort(403);
        }

        $request->validate(['signature' => 'required|string']);
        $sig = $request->signature;
        if (preg_match('/^data:image\/\w+;base64,/', $sig)) {
            $sig = substr($sig, strpos($sig, ',') + 1);
        }
        $sig = str_replace(' ', '+', $sig);
        $raw = base64_decode($sig, true);
        if ($raw === false || $raw === '') {
            return response()->json(['ok' => false, 'msg' => 'Invalid signature data'], 422);
        }
        $imageName = uniqid('', true) . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/' . $imageName, $raw);
        $checklist->update(['customer_signature' => 'signatures/' . $imageName]);

        return response()->json(['ok' => true]);
    }

    public function deleteAccount()
    {
        $user = auth()->user();
        
        // Mark as deleted or perform deletion logic
        $user->delete();
        
        auth()->logout();
        return redirect()->route('home')->with('success', 'Your account deletion request has been processed.');
    }
}
