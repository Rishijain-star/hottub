<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function overview()
    {
        $user = auth()->user();
        
        // Fetch all converted leads for this customer (email-based)
        $leads = \App\Models\Lead::whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->where('status', 'converted')
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
                ->where('status', 'converted')
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
            if ($lead->assigned_dealer_id) {
                $lead->dealer = \App\Models\User::find($lead->assigned_dealer_id);
                if ($lead->dealer) {
                    $lead->packages = \App\Models\MaintenancePackage::where('dealer_id', $lead->dealer->id)
                        ->where('status', 'active')
                        ->get();
                }
            }
        }

        return view('customer.overview', compact('leads'));
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
        
        \App\Models\PackageRequest::create([
            'user_id' => $user->id,
            'dealer_id' => $dealer_id,
            'package_id' => $package->id,
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Package request submitted to your dealer.');
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

        return view('customer.my-hot-tub', compact('leads'));
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
            'customer_signature' => 'required|string|max:255',
        ]);

        $serviceRequest->update([
            'customer_review' => $data['customer_review'],
            'customer_signature' => $data['customer_signature'],
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
            'message' => 'nullable|string',
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
            $user->update(['profile_image' => $path]);
        }

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function serviceHistory()
    {
        
        $user = auth()->user();
        $lead = \App\Models\Lead::where('email', $user->email)->first();
         $history = [];
        if (!$lead)   return view('customer.service-history', compact('history'));

        $history = \App\Models\ServiceChecklist::where('lead_id', $lead->id)
            ->orderBy('completed_at', 'desc')
            ->get();
         
        return view('customer.service-history', compact('history'));
    }

    public function signService(Request $request, \App\Models\ServiceChecklist $checklist)
    {
        $user = auth()->user();
        $lead = \App\Models\Lead::where('email', $user->email)->first();
        if (!$lead || $checklist->lead_id !== $lead->id) abort(403);

        $request->validate(['signature' => 'required|string']);
        $checklist->update(['customer_signature' => $request->signature]);
        
        return response()->json(['ok' => true]);
    }
}
