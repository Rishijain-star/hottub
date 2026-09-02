<?php

namespace App\Http\Controllers\Manufacturer;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditRequest;
use App\Models\Lead;
use App\Models\LeadPurchase;
use App\Models\Notification;
use App\Models\PaymentProcessorSetting;
use App\Models\PricingSetting;
use App\Models\ServiceChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\LeadActivity;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use App\Support\MaintenancePlanDates;
use App\Support\PanelTranslator;
use Illuminate\Support\Facades\Schema;

class ManufacturerController extends Controller
{
    public function overview()
    {
        $me = Auth::user();
        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            \App\Models\PackageRequest::where('dealer_id', $me->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }
        if (Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
            \App\Models\PackageRequest::where('dealer_id', $me->id)
                ->where('status', 'cancellation_scheduled')
                ->whereNotNull('cancellation_effective_at')
                ->where('cancellation_effective_at', '<=', now())
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
        }

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
        $availableLeads = Lead::where('is_private', false)
            ->whereNotIn('id', $excludeIds)
            ->whereNull('assigned_dealer_id')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            })
            ->count();

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
        $activeLeads = $this->countManufacturerActiveLeadsForDashboard($me->id);

        $conversionRate = $purchasedLeadsCount > 0 ? round(($convertedLeads / $purchasedLeadsCount) * 100, 1) : 0;

        $recentActivity = Notification::where('user_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $recentActivityTotalCount = Notification::where('user_id', $me->id)->count();

        $recentRequests = \App\Models\ServiceRequest::where('dealer_id', $me->id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $dashboardTasks = $this->getInitialDashboardTasks($me->id);
        $dashboardTasksHasMore = $this->countDashboardTasks($me->id) > 15;
        $maintenanceActivityUnreadCount = Notification::where('user_id', $me->id)
            ->where('read', false)
            ->whereIn('type', [
                'maintenance_plan_purchase',
                'maintenance_plan_cancel_scheduled',
                'maintenance_plan_cancel_immediate',
                'maintenance_plan_reactivated',
            ])
            ->count();

        return view('manufacturer.overview', compact(
            'availableCredits',
            'availableLeads',
            'purchasedLeadsCount',
            'activeLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate',
            'recentActivity',
            'recentActivityTotalCount',
            'recentRequests',
            'dashboardTasks',
            'dashboardTasksHasMore',
            'maintenanceActivityUnreadCount'
        ));
    }

    public function overviewRecentActivityList()
    {
        $items = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(250)
            ->get(['id', 'type', 'data', 'message', 'created_at']);

        return response()->json([
            'ok' => true,
            'items' => $items->map(fn (Notification $n) => [
                'id' => $n->id,
                'message' => PanelTranslator::notificationMessage($n),
                'date' => $n->created_at->format('M d, Y'),
            ])->values(),
        ]);
    }

    public function dashboardTasks(Request $request)
    {
        $uid = Auth::id();

        if ($request->boolean('additional')) {
            $first = $this->getInitialDashboardTasks($uid);
            $additional = $this->getAdditionalDashboardTasks($uid, $first->pluck('id')->all());
            $payload = $additional->map(function (LeadActivity $task) use ($uid) {
                $status = $this->resolveTaskLeadStatus($task->lead, $uid);

                return [
                    'id' => $task->id,
                    'content' => $task->content,
                    'lead_id' => $task->lead_id,
                    'due_date' => optional($task->due_date)->format('d M Y'),
                    'lead_url' => route('manufacturer.leads.view', $task->lead_id),
                    'status_label' => $status['label'],
                    'status_class' => $status['class'],
                ];
            })->values();

            return response()->json([
                'ok' => true,
                'tasks' => $payload,
                'additional' => true,
            ]);
        }

        $tasks = $this->getInitialDashboardTasks($uid);
        $payload = $tasks->map(function (LeadActivity $task) use ($uid) {
            $status = $this->resolveTaskLeadStatus($task->lead, $uid);

            return [
                'id' => $task->id,
                'content' => $task->content,
                'lead_id' => $task->lead_id,
                'due_date' => optional($task->due_date)->format('d M Y'),
                'lead_url' => route('manufacturer.leads.view', $task->lead_id),
                'status_label' => $status['label'],
                'status_class' => $status['class'],
            ];
        })->values();

        return response()->json(['ok' => true, 'tasks' => $payload]);
    }

    private function dashboardTasksBaseQuery(int $userId)
    {
        return LeadActivity::where('dealer_id', $userId)
            ->where('type', 'task')
            ->where('is_completed', false)
            ->whereNotNull('lead_id')
            ->whereHas('lead', function ($q) use ($userId) {
                $q->where(function ($leadStatus) use ($userId) {
                    $leadStatus
                        ->whereNull('status')
                        ->orWhere('status', '!=', 'converted')
                        ->orWhere(function ($convertedLead) use ($userId) {
                            $convertedLead
                                ->where('status', 'converted')
                                ->where('assigned_dealer_id', $userId);
                        });
                });
            });
    }

    private function countDashboardTasks(int $userId): int
    {
        return $this->dashboardTasksBaseQuery($userId)->count();
    }

    private function getInitialDashboardTasks(int $userId)
    {
        return $this->dashboardTasksBaseQuery($userId)
            ->with('lead')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
    }

    /**
     * @param  array<int>  $excludeIds
     */
    private function getAdditionalDashboardTasks(int $userId, array $excludeIds)
    {
        if ($excludeIds === []) {
            return collect();
        }

        $recent = $this->dashboardTasksBaseQuery($userId)
            ->with('lead')
            ->whereNotIn('id', $excludeIds)
            ->where('created_at', '>=', now()->subDays(2))
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        if ($recent->isNotEmpty()) {
            return $recent;
        }

        return $this->dashboardTasksBaseQuery($userId)
            ->with('lead')
            ->whereNotIn('id', $excludeIds)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    /**
     * @return array{label: string, class: string}
     */
    private function resolveTaskLeadStatus(?Lead $lead, int $userId): array
    {
        if (!$lead) {
            return ['label' => 'Active', 'class' => 'active'];
        }

        if ($lead->status === 'converted' && (int) $lead->assigned_dealer_id === $userId) {
            return ['label' => 'Won', 'class' => 'won'];
        }

        if ($lead->status === 'converted' && (int) $lead->assigned_dealer_id !== $userId) {
            return ['label' => 'Closed', 'class' => 'closed'];
        }

        return ['label' => 'Active', 'class' => 'active'];
    }

    /**
     * Matches "My Leads" → Won/Purchased → filter "Active" (pipeline still open, not converted away).
     */
    private function countManufacturerActiveLeadsForDashboard(int $userId): int
    {
        $purchasedLeadIds = LeadPurchase::where('dealer_id', $userId)->where('buyer_role', 'manufacturer')->pluck('lead_id');
        if ($purchasedLeadIds->isEmpty()) {
            return 0;
        }

        return Lead::query()
            ->whereIn('id', $purchasedLeadIds)
            ->where('is_private', false)
            ->whereHas('purchases', function ($q) use ($userId) {
                $q
                    ->where('dealer_id', $userId)
                    ->where('buyer_role', 'manufacturer')
                    ->where(function ($activeStages) {
                        $activeStages
                            ->whereNull('stage')
                            ->orWhereNotIn('stage', ['Lost', 'Delivered']);
                    });
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            })
            ->count();
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
        $privateLeadsQuery = (clone $query)
            ->where('assigned_dealer_id', $manufacturer->id)
            ->where('is_private', true);

        if ($request->filled('private_status')) {
            $privateStatusFilter = $request->private_status;
            if ($privateStatusFilter === 'active') {
                $privateLeadsQuery
                    ->where(function ($q) {
                        $q
                            ->whereNull('status')
                            ->orWhereNotIn('status', ['converted', 'closed']);
                    })
                    ->where(function ($q) {
                        $q
                            ->whereNull('stage')
                            ->orWhere('stage', '!=', 'Lost');
                    });
            } elseif ($privateStatusFilter === 'converted') {
                $privateLeadsQuery->where('status', 'converted');
            } elseif ($privateStatusFilter === 'lost') {
                $privateLeadsQuery->where(function ($q) {
                    $q
                        ->where('status', 'closed')
                        ->orWhere('stage', 'Lost');
                });
            }
        }

        $privateLeads = $privateLeadsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(7, ['*'], 'private_page')
            ->withQueryString();

        // Won / Purchased Leads (Excluding Private) — only leads this manufacturer actually purchased
        $myLeadsQuery = Lead::query()
            ->whereIn('id', $purchasedLeadIds)
            ->where('is_private', false)
            ->whereHas('purchases', function ($q) use ($manufacturer) {
                $q->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer');
            })
            ->addSelect(['latest_purchase_date' => LeadPurchase::select('created_at')
                ->whereColumn('lead_id', 'leads.id')
                ->where('dealer_id', $manufacturer->id)
                ->where('buyer_role', 'manufacturer')
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
                        ->where(function ($activeStages) {
                            $activeStages
                                ->whereNull('stage')
                                ->orWhereNotIn('stage', ['Lost', 'Delivered']);
                        });
                });
            }
        }

        if (!$request->filled('lead_status')) {
            $myLeadsQuery->where(function ($q) use ($manufacturer) {
                $q
                    ->whereNull('assigned_dealer_id')
                    ->orWhere('assigned_dealer_id', $manufacturer->id)
                    ->orWhere(function ($won) use ($manufacturer) {
                        $won
                            ->where('status', 'converted')
                            ->where('assigned_dealer_id', $manufacturer->id);
                    })
                    ->orWhereHas('purchases', function ($lost) use ($manufacturer) {
                        $lost
                            ->where('dealer_id', $manufacturer->id)
                            ->where('buyer_role', 'manufacturer')
                            ->where('stage', 'Lost');
                    });
            });
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

    public function destroyPrivateLead(Lead $lead)
    {
        $manufacturer = Auth::user();

        if (!$lead->is_private || (int) $lead->assigned_dealer_id !== (int) $manufacturer->id) {
            abort(403);
        }

        $lead->delete();

        return back()->with('success', 'Private lead deleted successfully.');
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
            ->whereNull('assigned_dealer_id')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            })
            ->orderBy('created_at', 'desc');

        $currentPage = max(1, (int) $request->get('page', 1));
        $items = $query->paginate($perPage, ['*'], 'page', $currentPage);
        $items->getCollection()->transform(function (Lead $lead) {
            $lead->price = $this->manufacturerLeadPrice($lead);
            return $lead;
        });

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
        $manufacturerLeadPrice = $this->manufacturerLeadPrice($lead);

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
        if ($manufacturer->credits < $manufacturerLeadPrice) {
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
        $manufacturer->credits -= $manufacturerLeadPrice;
        $manufacturer->save();

        LeadPurchase::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'buyer_role' => 'manufacturer',
            'amount' => $manufacturerLeadPrice,
            'stage' => 'New Lead',
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
                'price' => $manufacturerLeadPrice,
                'interests' => $lead->interests,
                'stage' => $lead->stage ?: 'New Lead',
                'status' => $lead->status,
                'delivery_details' => $lead->delivery_details,
            ],
        ]);
    }

    private function manufacturerLeadPrice(Lead $lead): float
    {
        $basePrice = (float) ($lead->price ?? 0);
        $multiplier = $this->manufacturerPriceMultiplier();

        return round($basePrice * $multiplier, 2);
    }

    private function manufacturerPriceMultiplier(): float
    {
        $settings = PricingSetting::query()->first();
        $leadCreditCosts = $settings?->lead_credit_costs ?? [];
        $rawMultiplier = $leadCreditCosts['manufacturer_multiplier'] ?? null;

        if (!is_numeric($rawMultiplier)) {
            return 1.0;
        }

        $multiplier = (float) $rawMultiplier;
        return $multiplier >= 0 ? $multiplier : 1.0;
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
            'plan_type' => 'required|in:monthly,yearly',
            'is_most_popular' => 'nullable|boolean',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $payload = [
            'dealer_id' => $mfr->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'plan_type' => MaintenancePlanDates::normalizeType($data['plan_type']),
            'is_most_popular' => $request->boolean('is_most_popular'),
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
            'status' => 'active',
        ];
        $payload = $this->stripUnavailableMaintenancePackageFields($payload);
        $package = \App\Models\MaintenancePackage::create($payload);

        if (($package->is_most_popular ?? false) && Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
            $package->markAsMostPopular();
        }

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
            'plan_type' => 'required|in:monthly,yearly',
            'is_most_popular' => 'nullable|boolean',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $payload = [
            'name' => $data['name'],
            'price' => $data['price'],
            'plan_type' => MaintenancePlanDates::normalizeType($data['plan_type']),
            'is_most_popular' => $request->boolean('is_most_popular'),
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
        ];
        $payload = $this->stripUnavailableMaintenancePackageFields($payload);
        $package->update($payload);

        if (($package->is_most_popular ?? false) && Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
            $package->markAsMostPopular();
        }

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
        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            \App\Models\PackageRequest::where('dealer_id', $mfr->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }

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
        $data = $request->validate([
            'status' => 'required|in:pending,active,responded,closed,expired',
        ]);

        $update = ['status' => $data['status']];
        if ($data['status'] === 'active') {
            $dates = MaintenancePlanDates::calculate(
                (string) ($packageRequest->package->plan_type ?? 'monthly'),
                $packageRequest->start_date ?: now()
            );
            $update['start_date'] = $dates['start_date'];
            $update['expiry_date'] = $dates['expiry_date'];
            $update['next_due_date'] = $dates['next_due_date'];
        }
        $update = $this->stripUnavailablePackageRequestDateFields($update);

        $packageRequest->update($update);

        if ($data['status'] === 'active') {
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

    private function stripUnavailableMaintenancePackageFields(array $payload): array
    {
        if (!Schema::hasColumn('maintenance_packages', 'plan_type')) {
            unset($payload['plan_type']);
        }
        if (!Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
            unset($payload['is_most_popular']);
        }

        return $payload;
    }

    private function stripUnavailablePackageRequestDateFields(array $payload): array
    {
        if (!Schema::hasColumn('package_requests', 'start_date')) {
            unset($payload['start_date']);
        }
        if (!Schema::hasColumn('package_requests', 'expiry_date')) {
            unset($payload['expiry_date']);
        }
        if (!Schema::hasColumn('package_requests', 'next_due_date')) {
            unset($payload['next_due_date']);
        }

        return $payload;
    }

    public function serviceRequests(Request $request)
    {
        $mfr = Auth::user();
        $requests = \App\Models\ServiceRequest::where('dealer_id', $mfr->id)
            ->with('customer')
            ->when($request->search, function ($q, $search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->query('status') !== 'all', function ($q) use ($request) {
                if ($request->filled('status')) {
                    $q->where('status', $request->status);
                } else {
                    $q->where('status', '!=', 'completed');
                }
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

        $serviceChecklist = ServiceChecklist::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturer->id)
            ->latest('completed_at')
            ->first();

        $customerAccount = User::whereRaw('LOWER(email) = ?', [strtolower((string) $lead->email)])
            ->where('role', User::ROLE_USER)
            ->first();

        if (empty($manufacturer->timezone) && !empty($manufacturer->postcode)) {
            $geo = app(\App\Services\GeocodingService::class)->geocode($manufacturer->postcode);
            if ($geo && !empty($geo['timezone'])) {
                $manufacturer->timezone = $geo['timezone'];
                $manufacturer->save();
            }
        }

        return view('manufacturer.lead', compact('lead', 'serviceChecklist', 'customerAccount'));
    }

    private function createDefaultTasks(Lead $lead, int $manufacturerId)
    {
        if ($lead->status === 'converted') {
            return;
        }

        $defaultTaskContents = ['Call Customer', 'Follow Up Customer'];
        $seedContent = 'Default lead follow-up tasks prepared.';
        $hasSeedMarker = \App\Models\LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturerId)
            ->where('type', 'activity')
            ->where('content', $seedContent)
            ->exists();

        if ($hasSeedMarker) {
            return;
        }

        $existingTasks = \App\Models\LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturerId)
            ->where('type', 'task')
            ->whereIn('content', $defaultTaskContents)
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

        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturerId,
            'type' => 'activity',
            'content' => $seedContent,
        ]);
    }

    public function storeServiceChecklist(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();
        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false], 403);
        }

        $existingChecklist = ServiceChecklist::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturer->id)
            ->first();

        if ($existingChecklist?->completed_at) {
            return response()->json([
                'ok' => false,
                'msg' => 'Checklist has already been saved and locked.',
                'checklist' => $existingChecklist,
            ], 422);
        }

        $data = $request->validate([
            'checklist' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $checklist = ServiceChecklist::updateOrCreate(
            ['lead_id' => $lead->id, 'dealer_id' => $manufacturer->id],
            [
                'checklist_data' => $data['checklist'] ?? [],
                'dealer_notes' => $data['notes'] ?? null,
                'completed_at' => now(),
            ]
        );

        return response()->json(['ok' => true, 'checklist' => $checklist]);
    }

    public function getServiceHistory(Lead $lead)
    {
        $manufacturer = Auth::user();
        $history = ServiceChecklist::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturer->id)
            ->orderBy('completed_at', 'desc')
            ->get();

        return response()->json(['ok' => true, 'history' => $history]);
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
                    'message' => 'Manufacturer '.$manufacturer->businessDisplayName().' has requested deposit confirmation for your lead.',
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

    public function updateLeadActivity(Request $request, Lead $lead, \App\Models\LeadActivity $activity)
    {
        $manufacturer = Auth::user();
        if ((int) $activity->lead_id !== (int) $lead->id || (int) $activity->dealer_id !== (int) $manufacturer->id) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }
        if (!in_array($activity->type, ['note', 'task'], true)) {
            return response()->json(['ok' => false, 'msg' => 'Cannot edit this entry'], 422);
        }
        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $activity->content = $data['content'];
        if ($activity->type === 'task') {
            $activity->due_date = $data['due_date'] ?? $activity->due_date;
        }
        $activity->save();

        return response()->json(['ok' => true]);
    }

    public function deleteLeadActivity(Lead $lead, \App\Models\LeadActivity $activity)
    {
        $manufacturer = Auth::user();
        if ((int) $activity->lead_id !== (int) $lead->id || (int) $activity->dealer_id !== (int) $manufacturer->id) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }
        if (!in_array($activity->type, ['note', 'task'], true)) {
            return response()->json(['ok' => false, 'msg' => 'Cannot delete this entry'], 422);
        }
        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $activity->delete();

        return response()->json(['ok' => true]);
    }

    public function deliverLead(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();
        if (!$this->checkLeadAccess($lead, $manufacturer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        if ($lead->status === 'converted' && (int) $lead->assigned_dealer_id === (int) $manufacturer->id) {
            return response()->json([
                'ok' => true,
                'stage' => $lead->stage,
                'status' => $lead->status,
                'details' => $lead->delivery_details,
            ]);
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
            'invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:51200'],
            'warranty' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:51200'],
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

        LeadActivity::where('lead_id', $lead->id)
            ->where('type', 'task')
            ->where('is_completed', false)
            ->delete();

        // Log winner activity
        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
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
            $autoMessage = "Hello, I have successfully processed your hot tub purchase for the {$details['make']} {$details['model']}. You can use this chat for any future work, service, or support regarding your new product.";
            $alreadySent = Message::where('sender_id', $manufacturer->id)
                ->where('receiver_id', $customer->id)
                ->where('lead_id', $lead->id)
                ->where('content', $autoMessage)
                ->exists();

            if (!$alreadySent) {
                Message::create([
                    'sender_id' => $manufacturer->id,
                    'receiver_id' => $customer->id,
                    'lead_id' => $lead->id,
                    'content' => $autoMessage,
                ]);
            }
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
        $me = Auth::user();

        $inv = Invoice::where('invoice_number', $invoice)
            ->where('dealer_id', $me->id)
            ->firstOrFail();

        $paymentDetails = $inv->payment_details ?? [];
        if (is_string($paymentDetails)) {
            $paymentDetails = json_decode($paymentDetails, true) ?: [];
        }

        $plan = null;
        if (!empty($inv->credit_plan_id)) {
            $plan = \App\Models\CreditPlan::find($inv->credit_plan_id);
        }

        $planName = $inv->plan_name ?? ($plan?->name ?? 'Credit Plan');
        $planDescription = $inv->plan_description ?? ($plan?->description ?? '');

        $creditsQty = (int) ($inv->credits ?? 0);
        $amountTotal = (float) ($inv->amount ?? 0);
        $unitPrice = $creditsQty > 0 ? ($amountTotal / $creditsQty) : $amountTotal;

        $items = [
            [
                'title' => $planName,
                'desc' => $planDescription ?: 'Includes credit access for the purchased plan.',
                'qty' => $creditsQty,
                'unit' => round($unitPrice, 4),
                'total' => $amountTotal,
            ],
        ];

        $paymentMethodTypes = $paymentDetails['payment_method_types'] ?? [];
        if (!is_array($paymentMethodTypes)) {
            $paymentMethodTypes = [$paymentMethodTypes];
        }
        $paymentMethodText = !empty($paymentMethodTypes) ? implode(', ', $paymentMethodTypes) : 'N/A';

        $vatRate = 0.20;
        $netAmount = round($amountTotal / (1 + $vatRate), 2);
        $vatAmount = round($amountTotal - $netAmount, 2);

        $data = [
            'invoice' => $inv->invoice_number,
            'date' => optional($inv->created_at)->format('d/m/Y'),
            'time' => optional($inv->created_at)->format('H:i:s'),
            'customer' => $me->company_name ?? $me->name,
            'status' => $inv->status,
            'items' => $items,
            'currency' => $inv->currency ?? 'GBP',
            'total' => $amountTotal,
            'netAmount' => $netAmount,
            'vatAmount' => $vatAmount,
            'vatRatePercent' => 20,
            'paymentDetails' => $paymentDetails,
            'paymentMethodText' => $paymentMethodText,
            'stripeSessionId' => $inv->stripe_session_id,
            'paymentId' => $inv->payment_id,
        ];

        return view('manufacturer.invoice', $data);
    }

    public function invoiceDownload(string $invoice)
    {
        $dataView = $this->invoice($invoice);
        $html = $dataView->render();
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

    public function myCustomers(Request $request)
    {
        $manufacturer = Auth::user();
        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            \App\Models\PackageRequest::where('dealer_id', $manufacturer->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }
        if (Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
            \App\Models\PackageRequest::where('dealer_id', $manufacturer->id)
                ->where('status', 'cancellation_scheduled')
                ->whereNotNull('cancellation_effective_at')
                ->where('cancellation_effective_at', '<=', now())
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
        }

        $customers = Lead::where('status', 'converted')
            ->where('assigned_dealer_id', $manufacturer->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->search);
                $q->where(function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $openTasksByLead = collect();
        if ($customers->isNotEmpty()) {
            $openTasksByLead = LeadActivity::whereIn('lead_id', $customers->pluck('id'))
                ->where('dealer_id', $manufacturer->id)
                ->where('type', 'task')
                ->where('is_completed', false)
                ->whereNotNull('due_date')
                ->orderBy('due_date')
                ->get()
                ->groupBy('lead_id');
        }

        $customerUsersByEmail = collect();
        if ($customers->isNotEmpty()) {
            $emails = $customers->pluck('email')->filter()->unique();
            $customerUsersByEmail = \App\Models\User::where('role', \App\Models\User::ROLE_USER)
                ->whereIn('email', $emails)
                ->get()
                ->keyBy(fn (\App\Models\User $u) => strtolower($u->email));
        }
        $customerPlanByUserId = collect();
        if ($customerUsersByEmail->isNotEmpty()) {
            $customerPlanByUserId = \App\Models\PackageRequest::with('package')
                ->where('dealer_id', $manufacturer->id)
                ->whereIn('user_id', $customerUsersByEmail->pluck('id')->values())
                ->orderByDesc('updated_at')
                ->get()
                ->groupBy('user_id')
                ->map(function ($rows) {
                    return $rows->sortByDesc(function (\App\Models\PackageRequest $row) {
                        $priority = match ($row->status) {
                            'active' => 5,
                            'cancellation_scheduled' => 4,
                            'cancelled' => 3,
                            'expired' => 2,
                            default => 1,
                        };
                        return $priority * 10000000000 + optional($row->updated_at)->getTimestamp();
                    })->first();
                });
        }

        return view('manufacturer.customers', compact('customers', 'openTasksByLead', 'customerUsersByEmail', 'customerPlanByUserId'));
    }

    public function credits(\Illuminate\Http\Request $request)
    {
        $me = Auth::user();
        $plans = \App\Models\CreditPlan::where('is_active', true)->orderBy('credits', 'asc')->get();
        $settings = PaymentProcessorSetting::first();
        $stripePublishableKey = PaymentProcessorSetting::stripePublishableKey();

        $historyQuery = \App\Models\CreditPurchase::where('user_id', $me->id)->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $historyQuery->where('status', $request->status);
        }

        $creditRequests = $historyQuery->paginate(10);

        return view('manufacturer.credits', compact('me', 'plans', 'creditRequests', 'stripePublishableKey'));
    }

    public function purchasePlan(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $plan = \App\Models\CreditPlan::findOrFail($request->plan_id);

        $settings = PaymentProcessorSetting::first();
        $stripeSecret = PaymentProcessorSetting::stripeSecretKey();
        if (! filled($stripeSecret)) {
            return response()->json(['error' => 'Stripe is not configured. Add keys in .env or Admin → Pricing processor.'], 503);
        }
        \Stripe\Stripe::setApiKey($stripeSecret);

        try {
            $currencyService = app(\App\Services\CurrencyService::class);
            $chargeCurrency = strtoupper($user->preferred_currency ?: 'GBP');
            $chargeAmount = $currencyService->convert((float) $plan->price, 'GBP', $chargeCurrency);
            $decimals = (int) config("localization.currencies.{$chargeCurrency}.decimals", 2);
            $multiplier = (int) (10 ** $decimals);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($chargeCurrency),
                        'product_data' => [
                            'name' => $plan->name . " ({$plan->credits} Credits)",
                            'description' => $plan->description,
                        ],
                        'unit_amount' => (int) round($chargeAmount * $multiplier),
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

        $settings = PaymentProcessorSetting::first();
        $stripeSecret = PaymentProcessorSetting::stripeSecretKey();
        if (! filled($stripeSecret)) {
            return redirect()->route('manufacturer.credits')->with('error', 'Stripe is not configured.');
        }
        \Stripe\Stripe::setApiKey($stripeSecret);

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            $existing = \App\Models\CreditPurchase::where('payment_id', $session->id)->first();
            $creditPurchase = $existing;

            if ($session->payment_status === 'paid') {
                $resolvedPlanId = $planId ?: ($existing?->credit_plan_id);
                $plan = \App\Models\CreditPlan::find($resolvedPlanId);

                if (!$plan) {
                    return redirect()->route('manufacturer.credits')->with('error', 'Payment was received but plan details could not be matched. Please contact support with your payment reference.');
                }

                if (!$existing) {
                    $user->credits += $plan->credits;
                    $user->save();

                    $creditPurchase = \App\Models\CreditPurchase::create([
                        'user_id' => $user->id,
                        'credit_plan_id' => $plan->id,
                        'amount' => $plan->price,
                        'credits_added' => $plan->credits,
                        'payment_id' => $session->id,
                        'status' => 'completed',
                    ]);
                }

                // Create invoice record for Accounting & Invoices section
                try {
                    $stripeSessionId = $session->id ?? $sessionId;
                    $paymentIntent = $session->payment_intent ?? null;

                    $invoiceExistsQuery = Invoice::where('stripe_session_id', $stripeSessionId);
                    if (!empty($paymentIntent)) {
                        $invoiceExistsQuery->orWhere('payment_id', $paymentIntent);
                    }

                    $invoiceExists = $invoiceExistsQuery->first();
                    if (!$invoiceExists) {
                        $invoiceNumber = 'INV-' . now()->format('YmdHis') . '-' . Str::random(6);

                        $amountTotal = null;
                        if (isset($session->amount_total)) {
                            // Stripe amount_total is in the smallest currency unit (pence).
                            $amountTotal = ((float) $session->amount_total) / 100;
                        }

                        $customerEmail = null;
                        if (isset($session->customer_details) && isset($session->customer_details->email)) {
                            $customerEmail = $session->customer_details->email;
                        }

                        Invoice::create([
                            'invoice_number' => $invoiceNumber,
                            'dealer_id' => $user->id,
                            'credits' => $creditPurchase->credits_added,
                            'amount' => $creditPurchase->amount,
                            'status' => 'paid',
                            'payment_id' => $paymentIntent ?: ($session->id ?? null),
                            'credit_plan_id' => $plan->id,
                            'plan_name' => $plan->name,
                            'plan_description' => $plan->description,
                            'currency' => 'GBP',
                            'stripe_session_id' => $stripeSessionId,
                            'payment_details' => [
                                'payment_status' => $session->payment_status,
                                'payment_method_types' => $session->payment_method_types ?? [],
                                'customer_email' => $customerEmail ?: $user->email,
                                'amount_total' => $amountTotal ?? $plan->price,
                            ],
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::error('Manufacturer purchaseSuccess invoice creation failed', [
                        'session_id' => $sessionId,
                        'user_id' => $user?->id,
                        'error' => $e->getMessage(),
                    ]);
                }

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
