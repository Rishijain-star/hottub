<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditRequest;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadPurchase;
use App\Models\MaintenancePackage;
use App\Models\Message;
use App\Models\PackageRequest;
use App\Models\PaymentProcessorSetting;
use App\Models\ServiceChecklist;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DealerController extends Controller
{
    public function leads(Request $request)
    {
        $dealer = Auth::user();
        $query = Lead::query();

        // If search is provided
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q
                    ->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        // Available Leads (Marketplace)
        $purchasedLeadIds = LeadPurchase::where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->pluck('lead_id');
        $availableLeads = (clone $query)
            ->where('is_private', false)
            ->whereNull('assigned_dealer_id')
            ->whereNotIn('id', $purchasedLeadIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Private Leads
        $privateLeads = (clone $query)
            ->where('assigned_dealer_id', $dealer->id)
            ->where('is_private', true)
            ->orderBy('created_at', 'desc')
            ->paginate(4, ['*'], 'private_page');

        // Won / Purchased Leads (Excluding Private)
        $myLeads = (clone $query)
            ->whereIn('id', $purchasedLeadIds)
            ->where('is_private', false)
            ->orderBy('updated_at', 'desc')
            ->paginate(7, ['*'], 'won_page');

        return view('dealer.leads', compact('availableLeads', 'myLeads', 'privateLeads'));
    }

    public function storePrivateLead(Request $request)
    {
        $dealer = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',
        ]);

        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'status' => 'new',
            'stage' => 'New Lead',
            'assigned_dealer_id' => $dealer->id,
            'is_private' => true,
        ]);

        return back()->with('success', 'Private lead created successfully.');
    }

    public function maintenancePackages()
    {
        $dealer = Auth::user();
        $packages = MaintenancePackage::where('dealer_id', $dealer->id)->get();
        return view('dealer.maintenance-packages', compact('packages'));
    }

    public function storeMaintenancePackage(Request $request)
    {
        $dealer = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        MaintenancePackage::create([
            'dealer_id' => $dealer->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
        ]);

        return back()->with('success', 'Maintenance package created.');
    }

    public function editMaintenancePackage(MaintenancePackage $package)
    {
        if ($package->dealer_id !== Auth::id())
            abort(403);
        return response()->json(['ok' => true, 'package' => $package]);
    }

    public function updateMaintenancePackage(Request $request, MaintenancePackage $package)
    {
        if ($package->dealer_id !== Auth::id())
            abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $package->update([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
        ]);

        return back()->with('success', 'Maintenance package updated.');
    }

    public function destroyMaintenancePackage(MaintenancePackage $package)
    {
        if ($package->dealer_id !== Auth::id())
            abort(403);
        $package->delete();
        return back()->with('success', 'Maintenance package deleted.');
    }

    public function packageRequests()
    {
        $dealer = Auth::user();
        $requests = PackageRequest::where('dealer_id', $dealer->id)
            ->with(['customer', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('dealer.package-requests', compact('requests'));
    }

    public function updatePackageRequestStatus(Request $request, PackageRequest $packageRequest)
    {
        if ($packageRequest->dealer_id !== Auth::id())
            abort(403);
        $packageRequest->update(['status' => $request->status]);
        return back()->with('success', 'Request status updated.');
    }

    public function overview()
    {
        $me = Auth::user();

        // 1. Available Credits
        $availableCredits = $me->credits;

        // 2. Available Leads (within 50 miles, not purchased by me, and < 3 dealer purchases)
        $myPurchasedIds = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'dealer')->pluck('lead_id');
        $fullLeadIds = LeadPurchase::where('buyer_role', 'dealer')
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->unique();

        $availableQuery = Lead::where('is_private', false)->whereNotIn('id', $excludeIds);
        if ($me->dealer_lat && $me->dealer_lng) {
            $availableQuery->where(function ($q) use ($me) {
                $q
                    ->where('is_national', true)
                    ->orWhereRaw('(3958.8 * acos(cos(radians(?)) * cos(radians(lead_lat)) * cos(radians(lead_lng) - radians(?)) + sin(radians(?)) * sin(radians(lead_lat)))) <= 50', [
                        $me->dealer_lat,
                        $me->dealer_lng,
                        $me->dealer_lat
                    ]);
            });
        }
        $availableLeads = $availableQuery->count();

        // 3. My Leads (purchased by me)
        $purchasedLeadsCount = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'dealer')->count();

        // 4. Active Leads (purchased by me, not won by anyone, not manually marked lost)
        $activeLeads = LeadPurchase::where('dealer_id', $me->id)
            ->where('buyer_role', 'dealer')
            ->where(function ($q) {
                $q
                    ->whereNull('stage')
                    ->orWhereNotIn('stage', ['Delivered', 'Lost']);
            })
            ->whereHas('lead', function ($q) {
                $q->where(function ($sq) {
                    $sq
                        ->whereNull('status')
                        ->orWhere('status', '!=', 'converted');
                });
            })
            ->count();

        // 5. Converted Leads (won by me)
        $convertedLeads = Lead::where('assigned_dealer_id', $me->id)
            ->where('status', 'converted')
            ->count();

        // 6. Lost Leads (purchased by me, but won by someone else OR manually marked lost)
        $lostLeads = LeadPurchase::where('dealer_id', $me->id)
            ->where('buyer_role', 'dealer')
            ->where(function ($q) use ($me) {
                $q
                    ->where('stage', 'Lost')
                    ->orWhereHas('lead', function ($sq) use ($me) {
                        $sq
                            ->where('status', 'converted')
                            ->where('assigned_dealer_id', '!=', $me->id);
                    });
            })
            ->count();

        // 7. Conversion %
        $conversionRate = $purchasedLeadsCount > 0 ? round(($convertedLeads / $purchasedLeadsCount) * 100, 1) : 0.0;

        return view('dealer.overview', compact(
            'availableCredits',
            'availableLeads',
            'purchasedLeadsCount',
            'activeLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate'
        ));
    }

    public function quotes()
    {
        $dealer = Auth::user();
        $perPage = 6;

        // 1. Leads I already purchased
        $myPurchasedIds = LeadPurchase::where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->pluck('lead_id');

        // 2. Leads that reached the dealer purchase limit (3)
        $fullLeadIds = LeadPurchase::where('buyer_role', 'dealer')
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');

        // 3. Exclude my purchased leads and full leads, and private leads
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->unique();

        $query = Lead::where('is_private', false)
            ->whereNotIn('id', $excludeIds)
            ->orderBy('created_at', 'desc');

        // 4. Distance filtering for dealers (if not national)
        // If dealer has lat/lng, only show leads within 50 miles or national leads
        if ($dealer->dealer_lat && $dealer->dealer_lng) {
            $query->where(function ($q) use ($dealer) {
                $q
                    ->where('is_national', true)
                    ->orWhereRaw('(3958.8 * acos(cos(radians(?)) * cos(radians(lead_lat)) * cos(radians(lead_lng) - radians(?)) + sin(radians(?)) * sin(radians(lead_lat)))) <= 50', [
                        $dealer->dealer_lat,
                        $dealer->dealer_lng,
                        $dealer->dealer_lat
                    ]);
            });
        }

        $items = $query->paginate($perPage);

        // We also need the purchase counts to show in the view
        $counts = LeadPurchase::where('buyer_role', 'dealer')
            ->whereIn('lead_id', $items->pluck('id'))
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->pluck('count', 'lead_id');

        $mine = $myPurchasedIds->toArray();

        return view('dealer.quotes', compact('items', 'counts', 'mine'));
    }

    public function inventory()
    {
        $me = Auth::user();
        // Since hot_tubs table doesn't have dealer_id, we'll just return 0 for now or find the correct column
        // Based on HotTub model, it has brand_id. Maybe that's what's intended for manufacturers.
        // For dealers, let's just use 0 to fix the error until schema is clarified.
        $inventoryCount = 0;
        return view('dealer.inventory', compact('inventoryCount'));
    }

    public function credits()
    {
        $me = Auth::user();
        $creditRequests = CreditRequest::where('user_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $packages = CreditPackage::orderBy('position')->get();
        $paymentSettings = \App\Models\PaymentProcessorSetting::first();
        return view('dealer.credits', compact('me', 'creditRequests', 'packages', 'paymentSettings'));
    }

    public function requestCredits(Request $request)
    {
        $me = Auth::user();
        $data = $request->validate([
            'credits' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|in:paypal,stripe',
        ]);

        $creditRequest = CreditRequest::create([
            'user_id' => $me->id,
            'credits' => $data['credits'],
            'amount' => $data['amount'],
            'status' => 'pending',
        ]);

        $settings = \App\Models\PaymentProcessorSetting::first();

        $method = $request->payment_method ?: ($settings ? $settings->active_processor : 'manual');

        if ($method === 'stripe') {
            if (!$settings || !$settings->stripe_secret_key) {
                return $request->ajax() ? response()->json(['ok' => false, 'msg' => 'Stripe payment is not yet configured.']) : back()->with('error', 'Stripe payment is not yet configured by the administrator.');
            }
            try {
                $stripe = new \App\Services\Payment\StripeService();
                $url = $stripe->createCheckoutSession($creditRequest, $settings);
                return $request->ajax() ? response()->json(['ok' => true, 'url' => $url]) : redirect()->away($url);
            } catch (\Exception $e) {
                \Log::info('Stripe Error: ' . $e->getMessage());
                return $request->ajax() ? response()->json(['ok' => false, 'msg' => 'Stripe Error: ' . $e->getMessage()]) : back()->with('error', 'Stripe Error: ' . $e->getMessage());
            }
        }

        if ($method === 'paypal') {
            if (!$settings || !$settings->paypal_client_id) {
                return $request->ajax() ? response()->json(['ok' => false, 'msg' => 'PayPal payment is not yet configured.']) : back()->with('error', 'PayPal payment is not yet configured by the administrator.');
            }
            try {
                $paypal = new \App\Services\Payment\PayPalService();
                $url = $paypal->createCheckoutSession($creditRequest, $settings);
                return $request->ajax() ? response()->json(['ok' => true, 'url' => $url]) : redirect()->away($url);
            } catch (\Exception $e) {
                return $request->ajax() ? response()->json(['ok' => false, 'msg' => 'PayPal Error: ' . $e->getMessage()]) : back()->with('error', 'PayPal Error: ' . $e->getMessage());
            }
        }

        return $request->ajax() ? response()->json(['ok' => true, 'msg' => 'Credit request submitted successfully. It is now awaiting admin approval.']) : back()->with('success', 'Credit request submitted successfully. It is now awaiting admin approval.');
    }

    public function profile()
    {
        $dealer = Auth::user();
        return view('dealer.profile', compact('dealer'));
    }

    public function buyLead(Request $request, Lead $lead)
    {
        $dealer = Auth::user();

        // Check if the lead is private
        if ($lead->is_private) {
            return response()->json(['ok' => false, 'msg' => 'This lead is private and not available for purchase.'], 403);
        }

        // Check if the dealer has enough credits
        if ($dealer->credits < $lead->price) {
            return response()->json(['ok' => false, 'msg' => 'Insufficient credits'], 422);
        }

        $purchased = LeadPurchase::where('lead_id', $lead->id)->where('buyer_role', 'dealer')->count();
        if ($purchased >= 3) {
            return response()->json(['ok' => false, 'msg' => 'Lead limit reached', 'count' => $purchased], 422);
        }
        $exists = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->exists();
        if ($exists) {
            return response()->json(['ok' => false, 'msg' => 'Already purchased', 'count' => $purchased], 422);
        }

        // Deduct credits and save the purchase
        $dealer->credits -= $lead->price;
        $dealer->save();

        LeadPurchase::create([
            'lead_id' => $lead->id,
            'dealer_id' => $dealer->id,
            'buyer_role' => 'dealer',
            'amount' => $lead->price,
        ]);

        if (!$lead->stage) {
            $lead->stage = 'New Lead';
            $lead->status = 'new';
        }
        $lead->save();

        $count = LeadPurchase::where('lead_id', $lead->id)->where('buyer_role', 'dealer')->count();
        return response()->json([
            'ok' => true,
            'count' => $count,
            'limitReached' => $count >= 3,
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
                'message' => $lead->message,
                'price' => $lead->price,
                'interests' => $lead->interests,
                'stage' => $lead->stage ?: 'New Lead',
                'status' => $lead->status,
                'delivery_details' => $lead->delivery_details,
            ],
        ]);
    }

    private function checkLeadAccess(Lead $lead, int $userId): bool
    {
        // 1. If it's private, ONLY the assigned dealer/manufacturer can see it.
        if ($lead->is_private) {
            return ($lead->assigned_dealer_id === $userId);
        }

        // 2. If it's not private, we check for purchase or assigned winner
        if ($lead->assigned_dealer_id === $userId)
            return true;

        return LeadPurchase::where('lead_id', $lead->id)
            ->where('dealer_id', $userId)
            ->where('buyer_role', 'dealer')
            ->exists();
    }

    public function leadDetail(Lead $lead)
    {
        $dealer = Auth::user();

        $hasPurchased = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->exists();
        $isAssigned = ($lead->assigned_dealer_id === $dealer->id);

        if (!$hasPurchased && !$isAssigned) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        // Hide details ONLY if it's won by another dealer
        if (!$lead->is_private && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== $dealer->id) {
            $lead->name = 'Name Hidden';
            $lead->email = 'Email Hidden';
            $lead->phone = 'Phone Hidden';
            $lead->message = 'Message Hidden';
        }

        $activities = LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $dealer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Use stage from Lead model for private leads or assigned leads
        $stage = $lead->stage ?: 'New Lead';
        if (!$lead->is_private && $hasPurchased) {
            $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->first();
            if ($purchase && $purchase->stage)
                $stage = $purchase->stage;
        }

        return response()->json([
            'ok' => true,
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
                'message' => $lead->message,
                'price' => $lead->price,
                'interests' => $lead->interests,
                'stage' => $stage,
                'status' => $lead->status,
                'delivery_details' => $lead->delivery_details,
            ],
            'activities' => $activities
        ]);
    }

    public function viewLead(Lead $lead)
    {
        $dealer = Auth::user();
        abort_unless($this->checkLeadAccess($lead, $dealer->id), 403);

        // Hide details ONLY if it's won by another dealer
        if (!$lead->is_private && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== $dealer->id) {
            $lead->name = 'Name Hidden';
            $lead->email = 'Email Hidden';
            $lead->phone = 'Phone Hidden';
            $lead->message = 'Message Hidden';
        }

        // Automatically create tasks if they don't exist
        $this->createDefaultTasks($lead, $dealer->id);

        if (empty($dealer->timezone) && !empty($dealer->postcode)) {
            $geo = app(GeocodingService::class)->geocode($dealer->postcode);
            if ($geo && !empty($geo['timezone'])) {
                $dealer->timezone = $geo['timezone'];
                $dealer->save();
            }
        }

        return view('dealer.lead', compact('lead'));
    }

    private function createDefaultTasks(Lead $lead, int $dealerId)
    {
        $existingTasks = LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $dealerId)
            ->where('type', 'task')
            ->whereIn('content', ['Call Customer', 'Follow Up Customer'])
            ->count();

        if ($existingTasks === 0) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $dealerId,
                'type' => 'task',
                'content' => 'Call Customer',
                'due_date' => now()->addHours(2),
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $dealerId,
                'type' => 'task',
                'content' => 'Follow Up Customer',
                'due_date' => now()->addDays(7),
            ]);
        }
    }

    public function downloadGuidance(Lead $lead)
    {
        $dealer = Auth::user();
        abort_unless($this->checkLeadAccess($lead, $dealer->id), 403);

        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $dealer->id) {
            abort(403);
        }

        $files = [
            public_path('docs/Water-Treatment-Chemical-Liability-Disclaimer.pdf'),
            public_path('docs/Delivery-Policy.pdf'),
            public_path('docs/Hot-Tub-Installation-Safety-Disclaimer.pdf'),
            public_path('docs/Hot-Tub-Installation-Disclaimer.pdf'),
        ];
        $zipName = 'customer-guidance-' . $lead->id . '.zip';
        $tmp = tempnam(sys_get_temp_dir(), 'cgzip_');
        $zipPath = $tmp . '.zip';
        @unlink($zipPath);
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'Unable to create archive');
        }
        foreach ($files as $path) {
            if (is_file($path)) {
                $zip->addFile($path, basename($path));
            }
        }
        $zip->close();
        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function updateLeadStage(Request $request, Lead $lead)
    {
        $dealer = Auth::user();

        if (!$this->checkLeadAccess($lead, $dealer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $data = $request->validate([
            'stage' => ['required', 'string', 'in:New Lead,Contacted,Nurturing,Sale Pending,Site Visit,Delivered,Lost'],
        ]);

        $isAssigned = ($lead->assigned_dealer_id === $dealer->id);
        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->first();
        $current = 'New Lead';

        if ($isAssigned) {
            $current = $lead->stage ?: 'New Lead';
            $lead->stage = $data['stage'];
            if ($data['stage'] === 'Delivered') {
                $lead->status = 'converted';
            }
            $lead->save();
        }

        if ($purchase) {
            $current = $purchase->stage ?: 'New Lead';
            $purchase->stage = $data['stage'];
            $purchase->save();

            // Sync to Lead model if it's the winner
            if ($lead->assigned_dealer_id === $dealer->id) {
                $lead->stage = $data['stage'];
                if ($data['stage'] === 'Delivered') {
                    $lead->status = 'converted';
                }
                $lead->save();
            }
        }

        // Log activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $dealer->id,
            'type' => 'status_change',
            'old_value' => $current,
            'new_value' => $data['stage'],
            'content' => "Stage changed from {$current} to {$data['stage']}"
        ]);

        return response()->json(['ok' => true, 'stage' => $data['stage'], 'status' => $lead->status]);
    }

    public function addLeadActivity(Request $request, Lead $lead)
    {
        $dealer = Auth::user();

        if (!$this->checkLeadAccess($lead, $dealer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $data = $request->validate([
            'type' => 'required|in:note,task,activity',
            'content' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $activity = LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $dealer->id,
            'type' => $data['type'],
            'content' => $data['content'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        if ($data['type'] === 'task') {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $dealer->id,
                'type' => 'task_created',
                'content' => 'Task Created - ' . $data['content'],
            ]);
        }

        return response()->json(['ok' => true, 'activity' => $activity]);
    }

    public function storeServiceChecklist(Request $request, Lead $lead)
    {
        $dealer = Auth::user();
        if (!$this->checkLeadAccess($lead, $dealer->id)) {
            return response()->json(['ok' => false], 403);
        }

        $data = $request->validate([
            'checklist' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        $checklist = ServiceChecklist::updateOrCreate(
            ['lead_id' => $lead->id, 'dealer_id' => $dealer->id],
            [
                'checklist_data' => $data['checklist'],
                'dealer_notes' => $data['notes'],
                'completed_at' => now()
            ]
        );

        return response()->json(['ok' => true, 'checklist' => $checklist]);
    }

    public function getServiceHistory(Lead $lead)
    {
        $dealer = Auth::user();
        $history = ServiceChecklist::where('lead_id', $lead->id)
            ->where('dealer_id', $dealer->id)
            ->orderBy('completed_at', 'desc')
            ->get();
        return response()->json(['ok' => true, 'history' => $history]);
    }

    public function toggleTask(Request $request, LeadActivity $activity)
    {
        $dealer = Auth::user();
        if ($activity->dealer_id !== $dealer->id)
            return response()->json(['ok' => false], 403);

        $activity->is_completed = !$activity->is_completed;
        $activity->save();

        if ($activity->is_completed) {
            LeadActivity::create([
                'lead_id' => $activity->lead_id,
                'dealer_id' => $dealer->id,
                'type' => 'task_completion',
                'content' => 'Task Completed - ' . $activity->content,
            ]);
        }

        return response()->json(['ok' => true, 'is_completed' => $activity->is_completed]);
    }

    public function deliverLead(Request $request, Lead $lead)
    {
        $dealer = Auth::user();
        if (!$this->checkLeadAccess($lead, $dealer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $dealer->id) {
            return response()->json(['ok' => false, 'msg' => 'Lead already converted by another dealer'], 422);
        }

        $data = $request->validate([
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'shell_colour' => ['nullable', 'string', 'max:255'],
            'cabinet_colour' => ['nullable', 'string', 'max:255'],
            'accessories' => ['nullable', 'string', 'max:500'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'warranty' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $details = [
            'make' => $data['make'],
            'model' => $data['model'],
            'shell_colour' => $data['shell_colour'] ?? null,
            'cabinet_colour' => $data['cabinet_colour'] ?? null,
            'accessories' => $data['accessories'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'delivery_date' => now()->format('Y-m-d'),
        ];

        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->first();

        if ($purchase) {
            if ($request->hasFile('invoice')) {
                $path = $request->file('invoice')->store('leads', 'public');
                $purchase->invoice_path = $path;
            }
            if ($request->hasFile('warranty')) {
                $path = $request->file('warranty')->store('leads', 'public');
                $purchase->warranty_path = $path;
            }
            $purchase->delivery_details = $details;
            $purchase->stage = 'Delivered';
            $purchase->save();
        }

        $lead->status = 'converted';
        $lead->stage = 'Delivered';
        $lead->assigned_dealer_id = $dealer->id;
        $lead->delivery_details = $details;

        if (isset($purchase)) {
            $lead->invoice_path = $purchase->invoice_path;
            $lead->warranty_path = $purchase->warranty_path;
        } else {
            // Handle file uploads for private leads
            if ($request->hasFile('invoice')) {
                $lead->invoice_path = $request->file('invoice')->store('leads', 'public');
            }
            if ($request->hasFile('warranty')) {
                $lead->warranty_path = $request->file('warranty')->store('leads', 'public');
            }
        }

        $lead->save();

        // Mark as lost for other dealers/manufacturers who purchased this lead
        LeadPurchase::where('lead_id', $lead->id)
            ->where('dealer_id', '!=', $dealer->id)
            ->update(['stage' => 'Lost']);

        // Log winner activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $dealer->id,
            'type' => 'delivery_form',
            'content' => 'Lead won and marked as Delivered.'
        ]);

        // Sync to Customer Account
        $customer = User::whereRaw('LOWER(email) = ?', [strtolower($lead->email)])->where('role', 'user')->first();

        if (!$customer) {
            // Create a customer account if it doesn't exist
            $customer = User::create([
                'name' => $lead->name,
                'email' => $lead->email,
                'password' => Hash::make(Str::random(12)),
                'role' => 'user',
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
            ]);
        }

        if ($customer) {
            $customer->update([
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
                // Sync delivery data to customer dashboard
                'delivery_details' => $details,
                'invoice_path' => $lead->invoice_path,
                'warranty_path' => $lead->warranty_path,
                'assigned_dealer_id' => $dealer->id,
            ]);

            Message::create([
                'sender_id' => $dealer->id,
                'receiver_id' => $customer->id,
                'lead_id' => $lead->id,
                'content' => 'Congratulations on your purchase! We have successfully delivered your hot tub. How are you finding it?',
            ]);
        }

        return response()->json(['ok' => true, 'stage' => $lead->stage, 'status' => $lead->status, 'details' => $lead->delivery_details]);
    }

    public function serviceHistory()
    {
        $dealer = Auth::user();
        $history = ServiceChecklist::where('dealer_id', $dealer->id)
            ->with('lead')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        $completedRequests = ServiceRequest::where('dealer_id', $dealer->id)
            ->where('status', 'completed')
            ->with('customer')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('dealer.service-history', compact('history', 'completedRequests'));
    }

    public function serviceRequests()
    {
        $dealer = Auth::user();
        $requests = ServiceRequest::where('dealer_id', $dealer->id)
            ->where('status', '!=', 'completed')
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dealer.service-requests', compact('requests'));
    }

    public function updateServiceRequestStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $dealer = Auth::user();
        if ($serviceRequest->dealer_id !== $dealer->id)
            abort(403);

        $data = $request->validate([
            'status' => 'required|in:processing,under_review,completed',
            'checklist' => 'nullable|array',
        ]);

        $update = [
            'status' => $data['status']
        ];

        if ($data['status'] === 'under_review') {
            $update['checklist_data'] = $data['checklist'];
        }

        if ($data['status'] === 'completed') {
            $update['completed_at'] = now();
        }

        $serviceRequest->update($update);

        return back()->with('success', 'Service request status updated.');
    }

    public function payments()
    {
        $me = Auth::user();
        $invoices = Invoice::where('dealer_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('dealer.payments', compact('invoices'));
    }

    public function invoice(string $invoice)
    {
        $data = [
            'invoice' => $invoice,
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i:s'),
            'customer' => auth()->user()->company_name ?? auth()->user()->name,
            'status' => 'failed',
            'items' => [
                ['title' => 'Lead Generation Credits', 'desc' => 'Hot Tub Buyer Platform Access', 'qty' => 100, 'unit' => 3.0, 'total' => 300.0],
            ],
            'currency' => 'GBP',
            'total' => 300.0,
        ];
        return view('dealer.invoice', $data);
    }

    public function invoiceDownload(string $invoice)
    {
        $html = view('dealer.invoice', [
            'invoice' => $invoice,
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i:s'),
            'customer' => auth()->user()->company_name ?? auth()->user()->name,
            'status' => 'failed',
            'items' => [
                ['title' => 'Lead Generation Credits', 'desc' => 'Hot Tub Buyer Platform Access', 'qty' => 100, 'unit' => 3.0, 'total' => 300.0],
            ],
            'currency' => 'GBP',
            'total' => 300.0,
        ])->render();
        $filename = 'invoice-' . $invoice . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
