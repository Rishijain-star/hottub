<?php

namespace App\Http\Controllers\Manufacturer;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditRequest;
use App\Models\Lead;
use App\Models\LeadPurchase;
use App\Models\Notification;
use App\Models\PaymentProcessorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManufacturerController extends Controller
{
    public function overview()
    {
        $me = Auth::user();

        // 1. Available Credits
        $availableCredits = $me->credits;

        // 2. Available Leads (not purchased by me, and < 3 manufacturer purchases)
        $myPurchasedIds = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'manufacturer')->pluck('lead_id');
        $fullLeadIds = LeadPurchase::where('buyer_role', 'manufacturer')
            ->select('lead_id', \DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');
        $declinedLeadIds = \DB::table('declined_leads')->where('user_id', $me->id)->pluck('lead_id');
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->merge($declinedLeadIds)->unique();
        $availableLeads = Lead::where('is_private', false)->whereNotIn('id', $excludeIds)->count();

        $purchasedLeadsCount = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'manufacturer')->count();
        $convertedLeads = Lead::where('assigned_dealer_id', $me->id)->where('status', 'converted')->count();
        $lostLeads = LeadPurchase::where('dealer_id', $me->id)
            ->where('buyer_role', 'manufacturer')
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
        $activeLeads = LeadPurchase::where('dealer_id', $me->id)
            ->where('buyer_role', 'manufacturer')
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

        $conversionRate = $purchasedLeadsCount > 0 ? round(($convertedLeads / $purchasedLeadsCount) * 100, 1) : 0;

        $recentActivity = \App\Models\Notification::where('user_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentRequests = \App\Models\ServiceRequest::where('dealer_id', $me->id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if (request()->ajax() && request()->has('page')) {
            return view('manufacturer.overview', compact('recentActivity'))->render();
        }

        return view('manufacturer.overview', compact(
            'availableCredits',
            'availableLeads',
            'purchasedLeadsCount',
            'activeLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate',
            'recentActivity',
            'recentRequests'
        ));
    }

    public function leads(Request $request)
    {
        $manufacturer = Auth::user();
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

        // If status is provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Available Leads (Marketplace)
        $purchasedLeadIds = LeadPurchase::where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->pluck('lead_id');
        $declinedLeadIds = \Illuminate\Support\Facades\DB::table('declined_leads')->where('user_id', $manufacturer->id)->pluck('lead_id');

        // Private Leads
        $privateLeads = (clone $query)
            ->where('assigned_dealer_id', $manufacturer->id)
            ->where('is_private', true)
            ->orderBy('created_at', 'desc')
            ->paginate(7, ['*'], 'private_page')
            ->withQueryString();

        // Won / Purchased Leads (Excluding Private)
        $myLeadsQuery = Lead::query()
            ->whereIn('id', $purchasedLeadIds)
            ->where('is_private', false)
            ->addSelect(['latest_purchase_date' => LeadPurchase::select('created_at')
                ->whereColumn('lead_id', 'leads.id')
                ->where('dealer_id', $manufacturer->id)
                ->latest()
                ->limit(1)]);

        // If search is provided for My Leads
        if ($request->filled('search')) {
            $s = $request->search;
            $myLeadsQuery->where(function ($q) use ($s) {
                $q
                    ->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        // Apply Status Filter for Purchased Leads
        if ($request->filled('lead_status')) {
            $statusFilter = $request->lead_status;
            if ($statusFilter === 'won') {
                $myLeadsQuery
                    ->where('assigned_dealer_id', $manufacturer->id)
                    ->where('stage', 'Delivered');
            } elseif ($statusFilter === 'closed') {
                $myLeadsQuery->where(function ($q) use ($manufacturer) {
                    $q->whereHas('purchases', function ($sq) use ($manufacturer) {
                        $sq
                            ->where('dealer_id', $manufacturer->id)
                            ->where('buyer_role', 'manufacturer')
                            ->where('stage', 'Lost');
                    })->orWhere(function ($sq) use ($manufacturer) {
                        $sq
                            ->where('status', 'converted')
                            ->where('assigned_dealer_id', '!=', $manufacturer->id);
                    });
                });
            } elseif ($statusFilter === 'active') {
                $myLeadsQuery->whereHas('purchases', function ($sq) use ($manufacturer) {
                    $sq
                        ->where('dealer_id', $manufacturer->id)
                        ->where('buyer_role', 'manufacturer')
                        ->whereNotIn('stage', ['Lost', 'Delivered']);
                });
            }
        }

        $myLeads = $myLeadsQuery
            ->orderBy('latest_purchase_date', 'desc')
            ->paginate(7, ['*'], 'won_page')
            ->withQueryString();

        return view('manufacturer.leads', compact('myLeads', 'privateLeads'));
    }

    public function storePrivateLead(Request $request)
    {
        $manufacturer = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'address' => $data['address'] ?? '',
            'source' => $data['source'] ?? '',
            'status' => 'new',
            'stage' => 'New Lead',
            'assigned_dealer_id' => $manufacturer->id,
            'is_private' => true,
        ]);

        return back()->with('success', 'Private lead created successfully.');
    }

    public function quotes(Request $request)
    {
        $manufacturer = Auth::user();
        $perPage = 6;

        Notification::where('user_id', $manufacturer->id)
            ->where('type', 'available_leads')
            ->where('read', false)
            ->update(['read' => true]);

        // 1. Leads I already purchased
        $myPurchasedIds = LeadPurchase::where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->pluck('lead_id');

        // 2. Leads that reached the manufacturer purchase limit (3)
        $fullLeadIds = LeadPurchase::where('buyer_role', 'manufacturer')
            ->select('lead_id', \DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');

        // 3. Leads I declined
        $declinedLeadIds = \Illuminate\Support\Facades\DB::table('declined_leads')->where('user_id', $manufacturer->id)->pluck('lead_id');

        // 4. Exclude my purchased leads, full leads, declined leads, and private leads
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->merge($declinedLeadIds)->unique();

        // 5. Manufacturers see ALL leads (no postcode restriction, but exclude private)
        $query = Lead::where('is_private', false)
            ->whereNotIn('id', $excludeIds)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            })
            ->orderBy('created_at', 'desc');

        $currentPage = max(1, (int) $request->get('page', 1));
        $items = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // We also need the purchase counts to show in the view
        $counts = LeadPurchase::where('buyer_role', 'manufacturer')
            ->whereIn('lead_id', $items->pluck('id'))
            ->select('lead_id', \DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->pluck('count', 'lead_id');

        $mine = $myPurchasedIds->toArray();

        if ($request->boolean('fragment')) {
            return response()->json([
                'html' => view('manufacturer.partials.quotes-available-list', compact('items', 'counts', 'mine'))->render(),
                'total' => $items->total(),
            ]);
        }

        return view('manufacturer.quotes', compact('items', 'counts', 'mine'));
    }

    public function inventory()
    {
        $me = Auth::user();
        $inventoryCount = \App\Models\HotTub::where('brand_id', $me->id)->count();
        return view('manufacturer.inventory', compact('inventoryCount'));
    }

    public function profile()
    {
        $manufacturer = Auth::user();
        return view('manufacturer.profile', compact('manufacturer'));
    }

    public function buyLead(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();

        // Check if the lead is private
        if ($lead->is_private) {
            return response()->json(['ok' => false, 'msg' => 'This lead is private and not available for purchase.'], 403);
        }

        // Check if the lead has already been converted by another dealer
        if ($lead->status === 'converted') {
            // Automatically decline for this manufacturer so it's removed from their available list
            \Illuminate\Support\Facades\DB::table('declined_leads')->updateOrInsert(
                ['user_id' => $manufacturer->id, 'lead_id' => $lead->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return response()->json([
                'ok' => false,
                'msg' => "This lead has already been closed by another dealer.\nNo charges will be applied for this lead."
            ], 422);
        }

        // Check if the manufacturer has enough credits
        if ($manufacturer->credits < $lead->price) {
            return response()->json(['ok' => false, 'msg' => 'Insufficient credits'], 422);
        }

        $purchased = LeadPurchase::where('lead_id', $lead->id)->where('buyer_role', 'manufacturer')->count();
        if ($purchased >= 3) {
            return response()->json(['ok' => false, 'msg' => 'Lead limit reached', 'count' => $purchased], 422);
        }
        $exists = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if ($exists) {
            return response()->json(['ok' => false, 'msg' => 'Already purchased', 'count' => $purchased], 422);
        }

        // Deduct credits and save the purchase
        $manufacturer->credits -= $lead->price;
        $manufacturer->save();

        LeadPurchase::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'buyer_role' => 'manufacturer',
            'amount' => $lead->price,
        ]);
        $lead->stage = $lead->stage ?: 'New Lead';
        $lead->save();
        $count = LeadPurchase::where('lead_id', $lead->id)->where('buyer_role', 'manufacturer')->count();
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

    public function declineLead(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();

        \Illuminate\Support\Facades\DB::table('declined_leads')->updateOrInsert(
            ['user_id' => $manufacturer->id, 'lead_id' => $lead->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Lead declined successfully.');
    }

    public function maintenancePackages()
    {
        $mfr = Auth::user();
        $packages = \App\Models\MaintenancePackage::where('dealer_id', $mfr->id)->get();
        return view('manufacturer.maintenance-packages', compact('packages'));
    }

    public function storeMaintenancePackage(Request $request)
    {
        $mfr = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        \App\Models\MaintenancePackage::create([
            'dealer_id' => $mfr->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
            'status' => 'active',
        ]);

        return back()->with('success', 'Maintenance package created.');
    }

    public function editMaintenancePackage(\App\Models\MaintenancePackage $package)
    {
        if ($package->dealer_id !== Auth::id())
            abort(403);
        return response()->json(['ok' => true, 'package' => $package]);
    }

    public function updateMaintenancePackage(Request $request, \App\Models\MaintenancePackage $package)
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

    public function destroyMaintenancePackage(\App\Models\MaintenancePackage $package)
    {
        if ($package->dealer_id !== Auth::id())
            abort(403);
        $package->delete();
        return back()->with('success', 'Maintenance package deleted.');
    }

    public function packageRequests(Request $request)
    {
        $mfr = Auth::user();
        $requests = \App\Models\PackageRequest::where('dealer_id', $mfr->id)
            ->with(['customer', 'package'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();
        return view('manufacturer.package-requests', compact('requests'));
    }

    public function updatePackageRequestStatus(Request $request, \App\Models\PackageRequest $packageRequest)
    {
        if ($packageRequest->dealer_id !== Auth::id())
            abort(403);
        $packageRequest->update(['status' => $request->status]);

        if ($request->status === 'active') {
            \App\Models\Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $packageRequest->user_id,
                'content' => 'Your plan has been successfully activated.',
            ]);

            \App\Models\Notification::create([
                'user_id' => $packageRequest->user_id,
                'message' => 'Your package request has been activated.',
            ]);
        }

        return back()->with('success', 'Request status updated.');
    }

    public function serviceRequests(Request $request)
    {
        $mfr = Auth::user();
        $requests = \App\Models\ServiceRequest::where('dealer_id', $mfr->id)
            ->where('status', '!=', 'completed')
            ->with('customer')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();
        return view('manufacturer.service-requests', compact('requests'));
    }

    public function serviceHistory(Request $request)
    {
        $mfr = Auth::user();
        $history = \App\Models\ServiceChecklist::where('dealer_id', $mfr->id)
            ->with('lead')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('lead', function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('completed_at', 'desc')
            ->paginate(7, ['*'], 'checklist_page')
            ->withQueryString();

        $completedRequests = \App\Models\ServiceRequest::where('dealer_id', $mfr->id)
            ->where('status', 'completed')
            ->with('customer')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('completed_at', 'desc')
            ->paginate(7, ['*'], 'request_page')
            ->withQueryString();

        return view('manufacturer.service-history', compact('history', 'completedRequests'));
    }

    public function updateServiceRequestStatus(Request $request, \App\Models\ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->dealer_id !== Auth::id())
            abort(403);
        $serviceRequest->update(['status' => $request->status]);
        return back()->with('success', 'Service request status updated.');
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
            ->where('buyer_role', 'manufacturer')
            ->exists();
    }

    public function leadDetail(Lead $lead)
    {
        $manufacturer = Auth::user();

        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        // Hide details ONLY if it's won by another dealer/manufacturer
        if (!$lead->is_private && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            $lead->name = 'Name Hidden';
            $lead->email = 'Email Hidden';
            $lead->phone = 'Phone Hidden';
            $lead->message = 'Message Hidden';
        }

        $activities = \App\Models\LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Use stage from Lead model for private leads or assigned leads
        $stage = $lead->stage ?: 'New Lead';
        $hasPurchased = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if (!$lead->is_private && $hasPurchased) {
            $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->first();
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
        $manufacturer = Auth::user();
        abort_unless($this->checkLeadAccess($lead, $manufacturer->id), 403);

        // Hide details ONLY if it's won by another dealer/manufacturer
        if (!$lead->is_private && $lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            $lead->name = 'Name Hidden';
            $lead->email = 'Email Hidden';
            $lead->phone = 'Phone Hidden';
            $lead->message = 'Message Hidden';
        }

        // Automatically create tasks if they don't exist
        $this->createDefaultTasks($lead, $manufacturer->id);

        if (empty($manufacturer->timezone) && !empty($manufacturer->postcode)) {
            $geo = app(\App\Services\GeocodingService::class)->geocode($manufacturer->postcode);
            if ($geo && !empty($geo['timezone'])) {
                $manufacturer->timezone = $geo['timezone'];
                $manufacturer->save();
            }
        }

        return view('manufacturer.lead', compact('lead'));
    }

    private function createDefaultTasks(Lead $lead, int $manufacturerId)
    {
        $existingTasks = \App\Models\LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturerId)
            ->where('type', 'task')
            ->whereIn('content', ['Call Customer', 'Follow Up Customer'])
            ->count();

        if ($existingTasks === 0) {
            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturerId,
                'type' => 'task',
                'content' => 'Call Customer',
                'due_date' => now()->addHours(2),
            ]);

            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturerId,
                'type' => 'task',
                'content' => 'Follow Up Customer',
                'due_date' => now()->addDays(7),
            ]);
        }
    }

    public function downloadGuidance(Lead $lead)
    {
        $manufacturer = Auth::user();
        $has = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            abort(403);
        }
        abort_unless($has, 403);
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
        $manufacturer = Auth::user();

        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $data = $request->validate([
            'stage' => ['required', 'string', 'in:New Lead,Contacted,Nurturing,Site Visit,Deposit,Delivered,Lost'],
            'site_visit_required' => ['nullable', 'string', 'in:Yes,No'],
            'site_visit_notes' => ['nullable', 'string'],
        ]);

        $isAssigned = ($lead->assigned_dealer_id === $manufacturer->id);
        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->first();
        $current = $isAssigned ? ($lead->stage ?: 'New Lead') : ($purchase->stage ?? 'New Lead');

        // Logic for Site Visit enhancement
        if ($data['stage'] === 'Site Visit' && isset($data['site_visit_required'])) {
            $content = 'Site Visit Required: ' . $data['site_visit_required'];
            if ($data['site_visit_required'] === 'Yes' && !empty($data['site_visit_notes'])) {
                $content .= '. Notes: ' . $data['site_visit_notes'];
            }

            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturer->id,
                'type' => 'activity',
                'content' => $content
            ]);
        }

        // Logic for Deposit stage enhancement
        if ($data['stage'] === 'Deposit') {
            $lead->deposit_requested_at = now();
            $lead->save();

            // Find the customer to notify
            $customer = \App\Models\User::where('email', $lead->email)->where('role', 'user')->first();
            if ($customer) {
                \App\Models\Notification::create([
                    'user_id' => $customer->id,
                    'message' => "Manufacturer {$manufacturer->name} has requested deposit confirmation for your lead.",
                    'type' => 'deposit_confirmation',
                    'data' => ['lead_id' => $lead->id, 'dealer_id' => $manufacturer->id]
                ]);
            }

            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturer->id,
                'type' => 'activity',
                'content' => 'Deposit confirmation request sent to customer.'
            ]);
        }

        // Restriction: Cannot move to Delivered without deposit confirmation
        if ($data['stage'] === 'Delivered') {
            if (!$lead->deposit_confirmed && !$lead->is_private) {
                return response()->json(['ok' => false, 'msg' => 'Cannot proceed to Delivered stage until customer confirms deposit.']);
            }
            if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
                return response()->json(['ok' => false, 'msg' => 'This lead has been assigned to another dealer.']);
            }
        }

        if ($isAssigned) {
            $lead->stage = $data['stage'];
            $lead->save();
        }

        if ($purchase) {
            $purchase->stage = $data['stage'];
            $purchase->save();

            // Sync to Lead model if it's the winner
            if ($lead->assigned_dealer_id === $manufacturer->id) {
                $lead->stage = $data['stage'];
                $lead->save();
            }
        }

        // Log activity
        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'type' => 'status_change',
            'old_value' => $current,
            'new_value' => $data['stage'],
            'content' => "Stage changed from {$current} to {$data['stage']}"
        ]);

        $msg = null;
        if ($data['stage'] === 'Deposit') {
            $msg = 'A confirmation request has been sent to the customer.';
        }

        return response()->json(['ok' => true, 'stage' => $data['stage'], 'status' => $lead->status, 'msg' => $msg]);
    }

    public function getLeadStatus(Lead $lead)
    {
        return response()->json([
            'deposit_confirmed' => $lead->deposit_confirmed,
        ]);
    }

    public function addLeadActivity(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();

        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $data = $request->validate([
            'type' => 'required|in:note,task,activity',
            'content' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $activity = \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'type' => $data['type'],
            'content' => $data['content'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        if ($data['type'] === 'task') {
            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturer->id,
                'type' => 'task_created',
                'content' => 'Task Created - ' . $data['content'],
            ]);
        }

        return response()->json(['ok' => true, 'activity' => $activity]);
    }

    public function toggleTask(Request $request, \App\Models\LeadActivity $activity)
    {
        $manufacturer = Auth::user();
        if ($activity->dealer_id !== $manufacturer->id)
            return response()->json(['ok' => false], 403);

        $activity->is_completed = !$activity->is_completed;
        $activity->save();

        if ($activity->is_completed) {
            \App\Models\LeadActivity::create([
                'lead_id' => $activity->lead_id,
                'dealer_id' => $manufacturer->id,
                'type' => 'task_completion',
                'content' => 'Task Completed - ' . $activity->content,
            ]);
        }

        return response()->json(['ok' => true, 'is_completed' => $activity->is_completed]);
    }

    public function deliverLead(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();
        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            return response()->json(['ok' => false, 'msg' => 'Lead already converted'], 422);
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

        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->first();

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
        $lead->assigned_dealer_id = $manufacturer->id;
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
            ->where('dealer_id', '!=', $manufacturer->id)
            ->update(['stage' => 'Lost']);

        // Log winner activity
        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'type' => 'delivery_form',
            'content' => 'Lead won and marked as Delivered.'
        ]);

        // Sync to Customer Account
        $customer = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower($lead->email)])->where('role', 'user')->first();

        if (!$customer) {
            // Create a customer account if it doesn't exist
            $customer = \App\Models\User::create([
                'name' => $lead->name,
                'email' => $lead->email,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                'role' => 'user',
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
            ]);
        }

        if ($customer) {
            // Update customer details from the lead if they are linked
            $customer->update([
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
                // Sync delivery data to customer dashboard
                'delivery_details' => $details,
                'invoice_path' => $lead->invoice_path,
                'warranty_path' => $lead->warranty_path,
                'assigned_dealer_id' => $manufacturer->id,
            ]);

            // Create initial chat message
            \App\Models\Message::create([
                'sender_id' => $manufacturer->id,
                'receiver_id' => $customer->id,
                'lead_id' => $lead->id,
                'content' => "Hello, I have successfully processed your hot tub purchase for the {$details['make']} {$details['model']}. You can use this chat for any future work, service, or support regarding your new product.",
            ]);
        }

        return response()->json(['ok' => true, 'stage' => $lead->stage, 'status' => $lead->status, 'details' => $lead->delivery_details]);
    }

    public function payments(Request $request)
    {
        $me = Auth::user();
        $invoices = \App\Models\Invoice::where('dealer_id', $me->id)
            ->when($request->search, function ($q, $search) {
                $q->where('invoice_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();
        return view('manufacturer.payments', compact('invoices'));
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
        return view('manufacturer.invoice', $data);
    }

    public function invoiceDownload(string $invoice)
    {
        $html = view('manufacturer.invoice', [
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

    public function deleteAccount()
    {
        $user = auth()->user();

        // Mark as deleted or perform deletion logic
        $user->delete();

        auth()->logout();
        return redirect()->route('home')->with('success', 'Your account deletion request has been processed.');
    }

    public function credits()
    {
        $me = Auth::user();
        $plans = \App\Models\CreditPlan::where('is_active', true)->orderBy('credits', 'asc')->get();

        $historyQuery = \App\Models\CreditPurchase::where('user_id', $me->id)->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $historyQuery->where('status', $request->status);
        }

        $creditRequests = $historyQuery->paginate(10);

        return view('manufacturer.credits', compact('me', 'plans', 'creditRequests'));
    }

    public function purchasePlan(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $plan = \App\Models\CreditPlan::findOrFail($request->plan_id);

        $stripeSecret = config('services.stripe.secret') ?? env('STRIPE_SECRET_KEY');
        \Stripe\Stripe::setApiKey($stripeSecret);

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => [
                            'name' => $plan->name . " ({$plan->credits} Credits)",
                            'description' => $plan->description,
                        ],
                        'unit_amount' => (int) ($plan->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('manufacturer.credits.success') . '?session_id={CHECKOUT_SESSION_ID}&plan_id=' . $plan->id,
                'cancel_url' => route('manufacturer.credits.cancel'),
                'customer_email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'credits' => $plan->credits,
                ],
            ]);

            return response()->json(['id' => $session->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function purchaseSuccess(\Illuminate\Http\Request $request)
    {
        $sessionId = $request->get('session_id');
        $planId = $request->get('plan_id');
        $user = Auth::user();

        $stripeSecret = config('services.stripe.secret') ?? env('STRIPE_SECRET_KEY');
        \Stripe\Stripe::setApiKey($stripeSecret);

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            $existing = \App\Models\CreditPurchase::where('payment_id', $session->id)->first();
            if ($existing) {
                return redirect()->route('manufacturer.credits')->with('success', 'Credits already added to your account.');
            }

            if ($session->payment_status === 'paid') {
                $plan = \App\Models\CreditPlan::find($planId);

                $user->credits += $plan->credits;
                $user->save();

                \App\Models\CreditPurchase::create([
                    'user_id' => $user->id,
                    'credit_plan_id' => $plan->id,
                    'amount' => $plan->price,
                    'credits_added' => $plan->credits,
                    'payment_id' => $session->id,
                    'status' => 'completed',
                ]);

                return redirect()->route('manufacturer.credits')->with('success', "Success! {$plan->credits} credits have been added to your account.");
            }
        } catch (\Exception $e) {
            return redirect()->route('manufacturer.credits.cancel');
        }

        return redirect()->route('manufacturer.credits');
    }

    public function purchaseCancel()
    {
        return view('manufacturer.credits-cancel');
    }
}
