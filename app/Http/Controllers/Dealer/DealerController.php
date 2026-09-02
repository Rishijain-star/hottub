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
use App\Models\Notification;
use App\Models\PackageRequest;
use App\Models\PaymentProcessorSetting;
use App\Models\ServiceChecklist;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\GeocodingService;
use App\Support\MaintenancePlanDates;
use App\Support\PanelTranslator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

        // If status is provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Available Leads (Marketplace)
        $purchasedLeadIds = LeadPurchase::where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->pluck('lead_id');
        $declinedLeadIds = DB::table('declined_leads')->where('user_id', $dealer->id)->pluck('lead_id');

        // Private Leads
        $privateLeadsQuery = (clone $query)
            ->where('assigned_dealer_id', $dealer->id)
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

        // Won / Purchased Leads (Excluding Private)
        $myLeadsQuery = Lead::query()
            ->whereIn('id', $purchasedLeadIds)
            ->where('is_private', false)
            ->whereHas('purchases', function ($q) use ($dealer) {
                $q->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer');
            })
            ->addSelect(['latest_purchase_date' => LeadPurchase::select('created_at')
                ->whereColumn('lead_id', 'leads.id')
                ->where('dealer_id', $dealer->id)
                ->where('buyer_role', 'dealer')
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
                    ->where('assigned_dealer_id', $dealer->id)
                    ->where('stage', 'Delivered');
            } elseif ($statusFilter === 'closed') {
                $myLeadsQuery->where(function ($q) use ($dealer) {
                    $q->whereHas('purchases', function ($sq) use ($dealer) {
                        $sq
                            ->where('dealer_id', $dealer->id)
                            ->where('buyer_role', 'dealer')
                            ->where('stage', 'Lost');
                    })->orWhere(function ($sq) use ($dealer) {
                        $sq
                            ->where('status', 'converted')
                            ->where('assigned_dealer_id', '!=', $dealer->id);
                    });
                });
            } elseif ($statusFilter === 'active') {
                $myLeadsQuery->whereHas('purchases', function ($sq) use ($dealer) {
                    $sq
                        ->where('dealer_id', $dealer->id)
                        ->where('buyer_role', 'dealer')
                        ->where(function ($activeStages) {
                            $activeStages
                                ->whereNull('stage')
                                ->orWhereNotIn('stage', ['Lost', 'Delivered']);
                        });
                });
            }
        }

        if (!$request->filled('lead_status')) {
            $myLeadsQuery->where(function ($q) use ($dealer) {
                $q
                    ->whereNull('assigned_dealer_id')
                    ->orWhere('assigned_dealer_id', $dealer->id)
                    ->orWhere(function ($won) use ($dealer) {
                        $won
                            ->where('status', 'converted')
                            ->where('assigned_dealer_id', $dealer->id);
                    })
                    ->orWhereHas('purchases', function ($lost) use ($dealer) {
                        $lost
                            ->where('dealer_id', $dealer->id)
                            ->where('buyer_role', 'dealer')
                            ->where('stage', 'Lost');
                    });
            });
        }

        $myLeads = $myLeadsQuery
            ->orderBy('latest_purchase_date', 'desc')
            ->paginate(7, ['*'], 'won_page')
            ->withQueryString();

        return view('dealer.leads', compact('myLeads', 'privateLeads'));
    }

    public function storePrivateLead(Request $request)
    {
        $dealer = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $geo = null;
        if (!empty($data['postcode'])) {
            $geo = app(GeocodingService::class)->geocode($data['postcode']);
        }

        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'lead_postcode' => $data['postcode'] ?? null,
            'lead_lat' => $geo['lat'] ?? null,
            'lead_lng' => $geo['lng'] ?? null,
            'address' => $data['address'] ?? '',
            'source' => $data['source'] ?? '',
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
            'plan_type' => 'required|in:monthly,yearly',
            'is_most_popular' => 'nullable|boolean',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $payload = [
            'dealer_id' => $dealer->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'plan_type' => MaintenancePlanDates::normalizeType($data['plan_type']),
            'is_most_popular' => $request->boolean('is_most_popular'),
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
        ];
        $payload = $this->stripUnavailableMaintenancePackageFields($payload);
        $package = MaintenancePackage::create($payload);

        if (($package->is_most_popular ?? false) && Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
            $package->markAsMostPopular();
        }

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

    public function destroyMaintenancePackage(MaintenancePackage $package)
    {
        if ($package->dealer_id !== Auth::id())
            abort(403);
        $package->delete();
        return back()->with('success', 'Maintenance package deleted.');
    }

    public function packageRequests(Request $request)
    {
        $dealer = Auth::user();
        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            PackageRequest::where('dealer_id', $dealer->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }

        $requests = PackageRequest::where('dealer_id', $dealer->id)
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
        return view('dealer.package-requests', compact('requests'));
    }

    public function updatePackageRequestStatus(Request $request, PackageRequest $packageRequest)
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
            Message::create([
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

    public function destroyPackageRequest(PackageRequest $packageRequest)
    {
        if ($packageRequest->dealer_id !== Auth::id())
            abort(403);
        $packageRequest->delete();
        return back()->with('success', 'Package request cancelled and removed.');
    }

    public function overview()
    {
        $me = Auth::user();
        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            PackageRequest::where('dealer_id', $me->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }
        if (Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
            PackageRequest::where('dealer_id', $me->id)
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

        // 2. Available Leads (within 50 miles, not purchased by me, and < 3 dealer purchases)
        $myPurchasedIds = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'dealer')->pluck('lead_id');
        $fullLeadIds = LeadPurchase::where('buyer_role', 'dealer')
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');
        $declinedLeadIds = DB::table('declined_leads')->where('user_id', $me->id)->pluck('lead_id');
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->merge($declinedLeadIds)->unique();

        $availableQuery = Lead::where('is_private', false)
            ->whereNotIn('id', $excludeIds)
            ->whereNull('assigned_dealer_id')
            // Keep dashboard count aligned with Available Leads page.
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            });
        $this->applyDealerLeadVisibility($availableQuery, $me);
        $availableLeads = $availableQuery->count();

        // 3. My Leads (purchased by me)
        $purchasedLeadsCount = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'dealer')->count();

        // 4. Active Leads — same definition as My Leads → "Active" on purchased leads
        $activeLeads = $this->countDealerActiveLeadsForDashboard($me->id);

        // 5. Converted Leads (won by me)
        $convertedLeads = Lead::where('status', 'converted')
            ->where('assigned_dealer_id', $me->id)
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

        $recentActivity = Notification::where('user_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $recentActivityTotalCount = Notification::where('user_id', $me->id)->count();

        $recentRequests = ServiceRequest::where('dealer_id', $me->id)
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

        return view('dealer.overview', compact(
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
        $dealerId = Auth::id();

        if ($request->boolean('additional')) {
            $first = $this->getInitialDashboardTasks($dealerId);
            $additional = $this->getAdditionalDashboardTasks($dealerId, $first->pluck('id')->all());
            $payload = $additional->map(function (LeadActivity $task) use ($dealerId) {
                $status = $this->resolveTaskLeadStatus($task->lead, $dealerId);

                return [
                    'id' => $task->id,
                    'content' => $task->content,
                    'lead_id' => $task->lead_id,
                    'due_date' => optional($task->due_date)->format('d M Y'),
                    'lead_url' => route('dealer.leads.view', $task->lead_id),
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

        $tasks = $this->getInitialDashboardTasks($dealerId);

        $payload = $tasks->map(function (LeadActivity $task) use ($dealerId) {
            $status = $this->resolveTaskLeadStatus($task->lead, $dealerId);

            return [
                'id' => $task->id,
                'content' => $task->content,
                'lead_id' => $task->lead_id,
                'due_date' => optional($task->due_date)->format('d M Y'),
                'lead_url' => route('dealer.leads.view', $task->lead_id),
                'status_label' => $status['label'],
                'status_class' => $status['class'],
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'tasks' => $payload,
        ]);
    }

    private function dashboardTasksBaseQuery(int $dealerId)
    {
        return LeadActivity::where('dealer_id', $dealerId)
            ->where('type', 'task')
            ->where('is_completed', false)
            ->whereNotNull('lead_id')
            ->whereHas('lead', function ($q) use ($dealerId) {
                $q->where(function ($leadStatus) use ($dealerId) {
                    $leadStatus
                        ->whereNull('status')
                        ->orWhere('status', '!=', 'converted')
                        ->orWhere(function ($convertedLead) use ($dealerId) {
                            $convertedLead
                                ->where('status', 'converted')
                                ->where('assigned_dealer_id', $dealerId);
                        });
                });
            });
    }

    private function countDashboardTasks(int $dealerId): int
    {
        return $this->dashboardTasksBaseQuery($dealerId)->count();
    }

    private function getInitialDashboardTasks(int $dealerId)
    {
        return $this->dashboardTasksBaseQuery($dealerId)
            ->with('lead')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
    }

    /**
     * Tasks beyond the initial 15: prefer those created in the last 2 days; if none, next oldest by created_at.
     *
     * @param  array<int>  $excludeIds
     */
    private function getAdditionalDashboardTasks(int $dealerId, array $excludeIds)
    {
        if ($excludeIds === []) {
            return collect();
        }

        $recent = $this->dashboardTasksBaseQuery($dealerId)
            ->with('lead')
            ->whereNotIn('id', $excludeIds)
            ->where('created_at', '>=', now()->subDays(2))
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        if ($recent->isNotEmpty()) {
            return $recent;
        }

        return $this->dashboardTasksBaseQuery($dealerId)
            ->with('lead')
            ->whereNotIn('id', $excludeIds)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    private function resolveTaskLeadStatus(?Lead $lead, int $dealerId): array
    {
        if (!$lead) {
            return ['label' => 'Active', 'class' => 'active'];
        }

        if ($lead->status === 'converted' && (int) $lead->assigned_dealer_id === $dealerId) {
            return ['label' => 'Won', 'class' => 'won'];
        }

        if ($lead->status === 'converted' && (int) $lead->assigned_dealer_id !== $dealerId) {
            return ['label' => 'Closed', 'class' => 'closed'];
        }

        return ['label' => 'Active', 'class' => 'active'];
    }

    private function countDealerActiveLeadsForDashboard(int $userId): int
    {
        $purchasedLeadIds = LeadPurchase::where('dealer_id', $userId)->where('buyer_role', 'dealer')->pluck('lead_id');
        if ($purchasedLeadIds->isEmpty()) {
            return 0;
        }

        return Lead::query()
            ->whereIn('id', $purchasedLeadIds)
            ->where('is_private', false)
            ->whereHas('purchases', function ($q) use ($userId) {
                $q
                    ->where('dealer_id', $userId)
                    ->where('buyer_role', 'dealer')
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

    public function quotes(Request $request)
    {
        $dealer = Auth::user();
        $perPage = 6;

        Notification::where('user_id', $dealer->id)
            ->where('type', 'available_leads')
            ->where('read', false)
            ->update(['read' => true]);

        // 1. Leads I already purchased
        $myPurchasedIds = LeadPurchase::where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->pluck('lead_id');

        // 2. Leads that reached the dealer purchase limit (3)
        $fullLeadIds = LeadPurchase::where('buyer_role', 'dealer')
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');

        // 3. Leads I declined
        $declinedLeadIds = DB::table('declined_leads')->where('user_id', $dealer->id)->pluck('lead_id');

        // 4. Exclude my purchased leads, full leads, declined leads, and private leads
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->merge($declinedLeadIds)->unique();

        $query = Lead::where('is_private', false)
            ->whereNotIn('id', $excludeIds)
            ->whereNull('assigned_dealer_id')
            // Non-purchasers only see this list; hide leads already won by another dealer (matches buy validation)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            })
            ->orderBy('created_at', 'desc');

        // 4. Distance + 12h + parts-bypass visibility filter for dealers
        $this->applyDealerLeadVisibility($query, $dealer);

        $currentPage = max(1, (int) $request->get('page', 1));
        $items = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // We also need the purchase counts to show in the view
        $counts = LeadPurchase::where('buyer_role', 'dealer')
            ->whereIn('lead_id', $items->pluck('id'))
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->pluck('count', 'lead_id');

        $mine = $myPurchasedIds->toArray();

        if ($request->boolean('fragment')) {
            return response()->json([
                'html' => view('dealer.partials.quotes-available-list', compact('items', 'counts', 'mine'))->render(),
                'total' => $items->total(),
            ]);
        }

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

    public function myCustomers(Request $request)
    {
        $dealer = Auth::user();
        if (Schema::hasColumn('package_requests', 'expiry_date')) {
            PackageRequest::where('dealer_id', $dealer->id)
                ->where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now())
                ->update(['status' => 'expired']);
        }
        if (Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
            PackageRequest::where('dealer_id', $dealer->id)
                ->where('status', 'cancellation_scheduled')
                ->whereNotNull('cancellation_effective_at')
                ->where('cancellation_effective_at', '<=', now())
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
        }

        $customers = Lead::where('status', 'converted')
            ->where('assigned_dealer_id', $dealer->id)
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
                ->where('dealer_id', $dealer->id)
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
            $customerUsersByEmail = User::where('role', User::ROLE_USER)
                ->whereIn('email', $emails)
                ->get()
                ->keyBy(fn (User $u) => strtolower($u->email));
        }
        $customerPlanByUserId = collect();
        if ($customerUsersByEmail->isNotEmpty()) {
            $customerPlanByUserId = PackageRequest::with('package')
                ->where('dealer_id', $dealer->id)
                ->whereIn('user_id', $customerUsersByEmail->pluck('id')->values())
                ->orderByDesc('updated_at')
                ->get()
                ->groupBy('user_id')
                ->map(function ($rows) {
                    return $rows->sortByDesc(function (PackageRequest $row) {
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

        return view('dealer.customers', compact('customers', 'openTasksByLead', 'customerUsersByEmail', 'customerPlanByUserId'));
    }

    public function credits(Request $request)
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

        return view('dealer.credits', compact('me', 'plans', 'creditRequests', 'stripePublishableKey'));
    }

    public function purchasePlan(Request $request)
    {
        $user = Auth::user();
        $plan = \App\Models\CreditPlan::findOrFail($request->plan_id);

        // Get Stripe secret key from settings or config
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
                'success_url' => route('dealer.credits.success') . '?session_id={CHECKOUT_SESSION_ID}&plan_id=' . $plan->id,
                'cancel_url' => route('dealer.credits.cancel'),
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

    public function purchaseSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');
        $user = Auth::user();

        if (!$sessionId) {
            return redirect()->route('dealer.credits')->with('error', 'Payment session could not be verified. Please contact support if payment was deducted.');
        }

        $settings = PaymentProcessorSetting::first();
        $stripeSecret = PaymentProcessorSetting::stripeSecretKey();
        if (! filled($stripeSecret)) {
            return redirect()->route('dealer.credits')->with('error', 'Stripe is not configured.');
        }
        \Stripe\Stripe::setApiKey($stripeSecret);

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            // Check if this payment was already processed
            $existing = \App\Models\CreditPurchase::where('payment_id', $session->id)->first();
            $creditPurchase = $existing;

            if (in_array($session->payment_status, ['paid', 'no_payment_required'], true)) {
                $resolvedPlanId = $request->get('plan_id') ?: ($session->metadata->plan_id ?? ($existing?->credit_plan_id));
                $plan = $resolvedPlanId ? \App\Models\CreditPlan::find($resolvedPlanId) : null;

                if (!$plan) {
                    return redirect()->route('dealer.credits')->with('error', 'Payment was received but plan details could not be matched. Please contact support with your payment reference.');
                }

                // Add credits and create purchase record only once
                if (!$existing) {
                    $user->credits += $plan->credits;
                    $user->save();

                    // Log purchase
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
                    \Log::error('Dealer purchaseSuccess invoice creation failed', [
                        'session_id' => $sessionId,
                        'user_id' => $user?->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                return redirect()->route('dealer.credits')->with('success', "Success! {$plan->credits} credits have been added to your account.");
            }

            return redirect()->route('dealer.credits')->with('error', 'Payment is still processing. Please refresh in a moment.');
        } catch (\Exception $e) {
            \Log::error('Dealer credit purchase success handling failed', [
                'session_id' => $sessionId,
                'user_id' => $user?->id,
                'message' => $e->getMessage(),
            ]);
            return redirect()->route('dealer.credits.cancel');
        }
    }

    public function purchaseCancel()
    {
        return view('dealer.credits-cancel');
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
            if (! PaymentProcessorSetting::stripeIsConfigured()) {
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
        return view('dealer.profile', ['dealer' => Auth::user()]);
    }

    public function buyLead(Request $request, Lead $lead)
    {
        $dealer = Auth::user();

        // Check if the lead is private
        if ($lead->is_private) {
            return response()->json(['ok' => false, 'msg' => 'This lead is private and not available for purchase.'], 403);
        }

        // Check if the lead has already been converted by another dealer
        if ($lead->status === 'converted') {
            // Automatically decline for this dealer so it's removed from their available list
            DB::table('declined_leads')->updateOrInsert(
                ['user_id' => $dealer->id, 'lead_id' => $lead->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            return response()->json([
                'ok' => false,
                'msg' => "This lead has already been closed by another dealer.\nNo charges will be applied for this lead."
            ], 422);
        }

        // Enforce the same visibility policy server-side so direct endpoint hits
        // cannot bypass dealer radius / 12-hour restrictions.
        if (!$this->canDealerAccessLeadByVisibilityRules($lead, $dealer)) {
            return response()->json([
                'ok' => false,
                'msg' => 'This lead is currently outside your availability window.',
            ], 403);
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
            'stage' => 'New Lead',
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

    public function declineLead(Request $request, Lead $lead)
    {
        $dealer = Auth::user();

        DB::table('declined_leads')->updateOrInsert(
            ['user_id' => $dealer->id, 'lead_id' => $lead->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Lead declined successfully.');
    }

    /**
     * Apply the shared visibility rules for dealers' "Available Leads":
     *   - Manufacturers have no restriction (handled in ManufacturerController).
     *   - Parts / Service enquiries → all dealers, no postcode restriction.
     *   - National leads → all dealers.
     *   - Within first 12h → only dealers within 30 miles.
     *   - After 12h with <3 purchases → open to all dealers (exclude already handled via $fullLeadIds).
     */
    private function applyDealerLeadVisibility($query, $dealer): void
    {
        $hasCoords = !empty($dealer->dealer_lat) && !empty($dealer->dealer_lng);

        $cutoff = now()->subHours(12);

        $query->where(function ($outer) use ($dealer, $cutoff, $hasCoords) {
            // Parts / service / national leads bypass postcode restriction.
            $outer
                ->whereJsonContains('interests', 'part')
                ->orWhereJsonContains('interests', 'service')
                ->orWhere('is_national', true)
                // After 12 hours, postcode restriction is removed.
                ->orWhere('created_at', '<=', $cutoff);

            // During first 12h, only dealers inside 30 miles can see local leads.
            if ($hasCoords) {
                $outer->orWhere(function ($radiusScoped) use ($dealer, $cutoff) {
                    $radiusScoped
                        ->where('created_at', '>', $cutoff)
                        ->whereNotNull('lead_lat')
                        ->whereNotNull('lead_lng')
                        ->whereRaw(
                            '(3958.8 * 2 * ASIN(SQRT(POWER(SIN(RADIANS(lead_lat - ?) / 2), 2) + COS(RADIANS(?)) * COS(RADIANS(lead_lat)) * POWER(SIN(RADIANS(lead_lng - ?) / 2), 2)))) <= 30',
                            [$dealer->dealer_lat, $dealer->dealer_lat, $dealer->dealer_lng]
                        );
                });
            }
        });
    }

    private function canDealerAccessLeadByVisibilityRules(Lead $lead, User $dealer): bool
    {
        $interests = $lead->interests;
        if (is_string($interests)) {
            $interests = json_decode($interests, true) ?: [];
        }
        if (!is_array($interests)) {
            $interests = [];
        }

        $isBypass = in_array('part', $interests, true)
            || in_array('service', $interests, true)
            || (bool) $lead->is_national;
        if ($isBypass) {
            return true;
        }

        if ($lead->created_at && $lead->created_at->lte(now()->subHours(12))) {
            return true;
        }

        if (empty($dealer->dealer_lat) || empty($dealer->dealer_lng) || $lead->lead_lat === null || $lead->lead_lng === null) {
            return false;
        }

        $distanceMiles = $this->distanceMiles(
            (float) $dealer->dealer_lat,
            (float) $dealer->dealer_lng,
            (float) $lead->lead_lat,
            (float) $lead->lead_lng
        );

        return $distanceMiles <= 30.0;
    }

    private function distanceMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return app(GeocodingService::class)->distance($lat1, $lng1, $lat2, $lng2);
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

        $serviceChecklist = ServiceChecklist::where('lead_id', $lead->id)
            ->where('dealer_id', $dealer->id)
            ->latest('completed_at')
            ->first();

        $customerAccount = User::whereRaw('LOWER(email) = ?', [strtolower((string) $lead->email)])
            ->where('role', User::ROLE_USER)
            ->first();

        if (empty($dealer->timezone) && !empty($dealer->postcode)) {
            $geo = app(GeocodingService::class)->geocode($dealer->postcode);
            if ($geo && !empty($geo['timezone'])) {
                $dealer->timezone = $geo['timezone'];
                $dealer->save();
            }
        }

        return view('dealer.lead', compact('lead', 'serviceChecklist', 'customerAccount'));
    }

    private function createDefaultTasks(Lead $lead, int $dealerId)
    {
        if ($lead->status === 'converted') {
            return;
        }

        $defaultTaskContents = ['Call Customer', 'Follow Up Customer'];
        $seedContent = 'Default lead follow-up tasks prepared.';
        $hasSeedMarker = LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $dealerId)
            ->where('type', 'activity')
            ->where('content', $seedContent)
            ->exists();

        if ($hasSeedMarker) {
            return;
        }

        $existingTasks = LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $dealerId)
            ->where('type', 'task')
            ->whereIn('content', $defaultTaskContents)
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

        LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $dealerId,
            'type' => 'activity',
            'content' => $seedContent,
        ]);
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
            'stage' => ['required', 'string', 'in:New Lead,Contacted,Nurturing,Site Visit,Deposit,Delivered,Lost'],
            'site_visit_required' => ['nullable', 'string', 'in:Yes,No'],
            'site_visit_notes' => ['nullable', 'string'],
        ]);

        $isAssigned = ($lead->assigned_dealer_id === $dealer->id);
        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $dealer->id)->where('buyer_role', 'dealer')->first();
        $current = $isAssigned ? ($lead->stage ?: 'New Lead') : ($purchase->stage ?? 'New Lead');

        // Logic for Site Visit enhancement
        if ($data['stage'] === 'Site Visit' && isset($data['site_visit_required'])) {
            $content = 'Site Visit Required: ' . $data['site_visit_required'];
            if ($data['site_visit_required'] === 'Yes' && !empty($data['site_visit_notes'])) {
                $content .= '. Notes: ' . $data['site_visit_notes'];
            }

            LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $dealer->id,
                'type' => 'activity',
                'content' => $content
            ]);
        }

        // Logic for Deposit stage enhancement
        if ($data['stage'] === 'Deposit') {
            $lead->deposit_requested_at = now();
            $lead->save();

            // Find the customer to notify
            $customer = User::where('email', $lead->email)->where('role', 'user')->first();
            if ($customer) {
                \App\Models\Notification::create([
                    'user_id' => $customer->id,
                    'message' => 'Dealer '.$dealer->businessDisplayName().' has requested deposit confirmation for your lead.',
                    'type' => 'deposit_confirmation',
                    'data' => ['lead_id' => $lead->id, 'dealer_id' => $dealer->id]
                ]);
            }

            LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $dealer->id,
                'type' => 'activity',
                'content' => 'Deposit confirmation request sent to customer.'
            ]);

            // We update the stage to Deposit, but we don't return yet as we need to update Lead/Purchase below
        }

        // Restriction: Cannot move to Delivered without deposit confirmation
        if ($data['stage'] === 'Delivered') {
            if (!$lead->deposit_confirmed && !$lead->is_private) {
                return response()->json(['ok' => false, 'msg' => 'Cannot proceed to Delivered stage until customer confirms deposit.']);
            }
            if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $dealer->id) {
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
            if ($lead->assigned_dealer_id === $dealer->id) {
                $lead->stage = $data['stage'];
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

        $existingChecklist = ServiceChecklist::where('lead_id', $lead->id)
            ->where('dealer_id', $dealer->id)
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
            ['lead_id' => $lead->id, 'dealer_id' => $dealer->id],
            [
                'checklist_data' => $data['checklist'] ?? [],
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

    public function updateLeadActivity(Request $request, Lead $lead, LeadActivity $activity)
    {
        $dealer = Auth::user();
        if ((int) $activity->lead_id !== (int) $lead->id || (int) $activity->dealer_id !== (int) $dealer->id) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }
        if (!in_array($activity->type, ['note', 'task'], true)) {
            return response()->json(['ok' => false, 'msg' => 'Cannot edit this entry'], 422);
        }
        if (!$this->checkLeadAccess($lead, $dealer->id)) {
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

    public function deleteLeadActivity(Lead $lead, LeadActivity $activity)
    {
        $dealer = Auth::user();
        if ((int) $activity->lead_id !== (int) $lead->id || (int) $activity->dealer_id !== (int) $dealer->id) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }
        if (!in_array($activity->type, ['note', 'task'], true)) {
            return response()->json(['ok' => false, 'msg' => 'Cannot delete this entry'], 422);
        }
        if (!$this->checkLeadAccess($lead, $dealer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $activity->delete();

        return response()->json(['ok' => true]);
    }

    public function deliverLead(Request $request, Lead $lead)
    {
        $dealer = Auth::user();
        if (!$this->checkLeadAccess($lead, $dealer->id)) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        if ($lead->status === 'converted' && (int) $lead->assigned_dealer_id === (int) $dealer->id) {
            return response()->json([
                'ok' => true,
                'stage' => $lead->stage,
                'status' => $lead->status,
                'details' => $lead->delivery_details,
            ]);
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

        LeadActivity::where('lead_id', $lead->id)
            ->where('type', 'task')
            ->where('is_completed', false)
            ->delete();

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

            $autoMessage = 'Congratulations on your purchase! We have successfully delivered your product. How are you finding it?';
            $alreadySent = Message::where('sender_id', $dealer->id)
                ->where('receiver_id', $customer->id)
                ->where('lead_id', $lead->id)
                ->where('content', $autoMessage)
                ->exists();

            if (!$alreadySent) {
                Message::create([
                    'sender_id' => $dealer->id,
                    'receiver_id' => $customer->id,
                    'lead_id' => $lead->id,
                    'content' => $autoMessage,
                ]);
            }
        }

        return response()->json(['ok' => true, 'stage' => $lead->stage, 'status' => $lead->status, 'details' => $lead->delivery_details]);
    }

    public function destroyPrivateLead(Lead $lead)
    {
        $dealer = Auth::user();

        if (!$lead->is_private || (int) $lead->assigned_dealer_id !== (int) $dealer->id) {
            abort(403);
        }

        $lead->delete();

        return back()->with('success', 'Private lead deleted successfully.');
    }

    public function serviceHistory(Request $request)
    {
        $dealer = Auth::user();
        $history = ServiceChecklist::where('dealer_id', $dealer->id)
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

        $completedRequests = ServiceRequest::where('dealer_id', $dealer->id)
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

        return view('dealer.service-history', compact('history', 'completedRequests'));
    }

    public function serviceRequests(Request $request)
    {
        $dealer = Auth::user();
        $requests = ServiceRequest::where('dealer_id', $dealer->id)
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

        \App\Models\Notification::create([
            'user_id' => $serviceRequest->user_id,
            'message' => 'Your service request has been updated to ' . $data['status'] . '.',
        ]);

        return back()->with('success', 'Service request status updated.');
    }

    public function payments(Request $request)
    {
        $me = Auth::user();
        $invoices = Invoice::where('dealer_id', $me->id)
            ->when($request->search, function ($q, $search) {
                $q->where('invoice_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();
        return view('dealer.payments', compact('invoices'));
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

        $vatRate = 0.2;
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

        return view('dealer.invoice', $data);
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
        $user = Auth::user();

        // Mark as deleted or perform deletion logic
        $user->delete();

        Auth::logout();
        return redirect()->route('home')->with('success', 'Your account deletion request has been processed.');
    }
}
