<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function overview(Request $request)
    {
        $selectedMonth = $request->query('month', 'all');
        $analytics = $this->collectOverviewAnalytics($selectedMonth);
        $monthOptions = $this->buildMonthOptions();

        return view('admin.overview', array_merge($analytics, [
            'selectedMonth' => $selectedMonth,
            'monthOptions' => $monthOptions,
        ]));
    }

    public function downloadAnalyticsReport(Request $request)
    {
        $selectedMonth = $request->query('month', 'all');
        $analytics = $this->collectOverviewAnalytics($selectedMonth);
        $monthOptions = $this->buildMonthOptions();
        $periodLabel = $selectedMonth === 'all'
            ? 'All Time'
            : ($monthOptions[$selectedMonth] ?? $selectedMonth);

        $pdf = Pdf::loadView('admin.reports.analytics', array_merge($analytics, [
            'periodLabel' => $periodLabel,
            'generatedAt' => now(),
        ]));

        $filename = 'analytics-report-' . ($selectedMonth === 'all' ? 'all-time' : $selectedMonth) . '.pdf';
        return $pdf->download($filename);
    }

    private function collectOverviewAnalytics(string $month = 'all'): array
    {
        $range = $this->resolveMonthRange($month);
        $hasUsersTable = Schema::hasTable('users');
        $hasUserRoleCol = $hasUsersTable && Schema::hasColumn('users', 'role');
        $hasUserStatusCol = $hasUsersTable && Schema::hasColumn('users', 'status');
        $hasHotTubs = Schema::hasTable('hot_tubs');
        $hasBrands = Schema::hasTable('brands');
        $hasLeads = Schema::hasTable('leads');

        $dealersTotal = $hasUserRoleCol ? User::where('role', 'dealer')->count() : 0;
        $dealersApproved = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'dealer')->where('status', 'approved')->count() : 0;
        $dealersPending = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'dealer')->where('status', 'pending')->count() : 0;
        $dealersRevoked = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'dealer')->where('status', 'revoked')->count() : 0;

        $hotTubs = $hasHotTubs ? (int) DB::table('hot_tubs')->count() : 0;
        $brands = $hasBrands ? (int) DB::table('brands')->count() : 0;

        $leadsTotal = 0;
        $dealerPurchasedCount = 0;
        $manufacturerPurchasedCount = 0;
        $totalConverted = 0;
        $activeLeadsCount = 0;
        $overallConversionRate = 0.0;
        $dealerConversionRate = 0.0;
        $manufacturerConversionRate = 0.0;
        $revenue = 0.0;
        $mostPopularModel = 'N/A';
        $mostPopularColour = 'N/A';
        $brandPerformance = collect();
        $dealerRankings = collect();
        $manufacturerRankings = collect();

        if ($hasLeads) {
            $dealerPurchaseQuery = DB::table('lead_purchases')->where('buyer_role', 'dealer');
            $manufacturerPurchaseQuery = DB::table('lead_purchases')->where('buyer_role', 'manufacturer');
            $purchasedLeadIdsQuery = DB::table('lead_purchases')->select('lead_id')->distinct();
            $leadsBase = DB::table('leads');
            $convertedLeadsBase = DB::table('leads')->where('status', 'converted');

            if ($range) {
                [$from, $to] = $range;
                $dealerPurchaseQuery->whereBetween('created_at', [$from, $to]);
                $manufacturerPurchaseQuery->whereBetween('created_at', [$from, $to]);
                $purchasedLeadIdsQuery->whereBetween('created_at', [$from, $to]);
                $leadsBase->whereBetween('created_at', [$from, $to]);
                $convertedLeadsBase->whereBetween('created_at', [$from, $to]);
            }

            $dealerPurchasedCount = (int) $dealerPurchaseQuery->count();
            $manufacturerPurchasedCount = (int) $manufacturerPurchaseQuery->count();

            $purchasedLeadIds = $purchasedLeadIdsQuery->pluck('lead_id');
            $unpurchasedLeadsCount = (clone $leadsBase)->whereNotIn('id', $purchasedLeadIds)->count();
            $leadsTotal = $dealerPurchasedCount + $manufacturerPurchasedCount + $unpurchasedLeadsCount;

            $totalConverted = (int) (clone $convertedLeadsBase)->count();
            $activeLeadsCount = (int) (clone $leadsBase)->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'converted');
            })->count();

            $dealerConverted = DB::table('leads')
                ->join('users', 'leads.assigned_dealer_id', '=', 'users.id')
                ->where('leads.status', 'converted')
                ->where('users.role', 'dealer');
            if ($range) {
                [$from, $to] = $range;
                $dealerConverted->whereBetween('leads.created_at', [$from, $to]);
            }
            $dealerConvertedCount = (int) $dealerConverted->count();

            $manufacturerConverted = DB::table('leads')
                ->join('users', 'leads.assigned_dealer_id', '=', 'users.id')
                ->where('leads.status', 'converted')
                ->where('users.role', 'manufacturer');
            if ($range) {
                [$from, $to] = $range;
                $manufacturerConverted->whereBetween('leads.created_at', [$from, $to]);
            }
            $manufacturerConvertedCount = (int) $manufacturerConverted->count();

            $overallConversionRate = $leadsTotal > 0 ? round(($totalConverted / $leadsTotal) * 100, 1) : 0.0;
            $dealerConversionRate = $leadsTotal > 0 ? round(($dealerConvertedCount / $leadsTotal) * 100, 1) : 0.0;
            $manufacturerConversionRate = $leadsTotal > 0 ? round(($manufacturerConvertedCount / $leadsTotal) * 100, 1) : 0.0;

            $invoiceQuery = DB::table('invoices')->where('status', 'paid');
            if ($range) {
                [$from, $to] = $range;
                $invoiceQuery->whereBetween('created_at', [$from, $to]);
            }
            $revenue = (float) $invoiceQuery->sum('amount');

            $convertedRows = (clone $convertedLeadsBase)->select('delivery_details')->get();
            $modelCounts = [];
            $colourCounts = [];
            $brandCounts = [];
            foreach ($convertedRows as $row) {
                $details = $this->decodeDeliveryDetails($row->delivery_details ?? null);
                $make = trim((string) ($details['make'] ?? ''));
                $model = trim((string) ($details['model'] ?? ''));
                $shell = trim((string) ($details['shell_colour'] ?? ''));
                $cabinet = trim((string) ($details['cabinet_colour'] ?? ''));
                if ($model !== '') {
                    $modelCounts[$model] = ($modelCounts[$model] ?? 0) + 1;
                }
                if ($make !== '') {
                    $brandCounts[$make] = ($brandCounts[$make] ?? 0) + 1;
                }
                foreach ([$shell, $cabinet] as $colour) {
                    if ($colour !== '') {
                        $colourCounts[$colour] = ($colourCounts[$colour] ?? 0) + 1;
                    }
                }
            }

            arsort($modelCounts);
            arsort($colourCounts);
            arsort($brandCounts);
            $mostPopularModel = count($modelCounts) ? array_key_first($modelCounts) : 'N/A';
            $mostPopularColour = count($colourCounts) ? array_key_first($colourCounts) : 'N/A';
            $brandPerformance = collect($brandCounts)
                ->map(fn ($wins, $brand) => ['brand' => $brand, 'wins' => $wins])
                ->values();

            $dealerRankings = $this->buildUserRanking('dealer', $range);
            $manufacturerRankings = $this->buildUserRanking('manufacturer', $range);
        }

        return compact(
            'dealersTotal',
            'dealersApproved',
            'dealersPending',
            'dealersRevoked',
            'hotTubs',
            'brands',
            'leadsTotal',
            'dealerPurchasedCount',
            'manufacturerPurchasedCount',
            'totalConverted',
            'activeLeadsCount',
            'overallConversionRate',
            'dealerConversionRate',
            'manufacturerConversionRate',
            'revenue',
            'mostPopularModel',
            'mostPopularColour',
            'brandPerformance',
            'dealerRankings',
            'manufacturerRankings'
        );
    }

    private function buildUserRanking(string $role, ?array $range)
    {
        $winsQuery = DB::table('users')
            ->leftJoin('leads', function ($join) use ($range) {
                $join->on('users.id', '=', 'leads.assigned_dealer_id')
                    ->where('leads.status', '=', 'converted');
                if ($range) {
                    [$from, $to] = $range;
                    $join->whereBetween('leads.created_at', [$from, $to]);
                }
            })
            ->where('users.role', $role)
            ->groupBy('users.id', 'users.name')
            ->select('users.id', 'users.name', DB::raw('COUNT(leads.id) as wins'));

        $purchaseQuery = DB::table('lead_purchases')
            ->where('buyer_role', $role);
        if ($range) {
            [$from, $to] = $range;
            $purchaseQuery->whereBetween('created_at', [$from, $to]);
        }
        $purchasesByUser = $purchaseQuery
            ->select('dealer_id', DB::raw('COUNT(*) as purchases'))
            ->groupBy('dealer_id')
            ->pluck('purchases', 'dealer_id');

        return $winsQuery->get()->map(function ($row) use ($purchasesByUser) {
            $purchases = (int) ($purchasesByUser[$row->id] ?? 0);
            $wins = (int) $row->wins;
            $conversionRate = $purchases > 0 ? round(($wins / $purchases) * 100, 1) : 0.0;
            return [
                'name' => $row->name,
                'wins' => $wins,
                'purchases' => $purchases,
                'conversion_rate' => $conversionRate,
            ];
        })
            ->sortByDesc(fn ($row) => [$row['wins'], $row['conversion_rate']])
            ->values();
    }

    private function buildMonthOptions(): array
    {
        $options = ['all' => 'All Time'];
        $cursor = now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $options[$cursor->format('Y-m')] = $cursor->format('F Y');
            $cursor->subMonthNoOverflow();
        }
        return $options;
    }

    private function resolveMonthRange(string $month): ?array
    {
        if ($month === 'all') {
            return null;
        }
        try {
            $from = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            return [$from, (clone $from)->endOfMonth()];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function decodeDeliveryDetails($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function supportRequests()
    {
        $hasSupportStatusColumn = Schema::hasColumn('messages', 'support_status');
        // Get all messages where receiver is admin (ID 1)
        // Join with users to see the sender's info and status
        $requests = \App\Models\Message::where('receiver_id', 1)
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->select('messages.*', 'users.name as sender_name', 'users.email as sender_email', 'users.role as sender_role', 'users.status as sender_status', 'users.company_name')
            ->orderBy('messages.created_at', 'desc')
            ->paginate(7);

        return view('admin.support-requests', compact('requests', 'hasSupportStatusColumn'));
    }

    public function approveSupportRequest(\App\Models\Message $message)
    {
        if ((int) $message->receiver_id !== 1) {
            return back()->with('error', 'Invalid support request.');
        }

        $sender = \App\Models\User::find($message->sender_id);
        if (!$sender) {
            return back()->with('error', 'Sender account not found.');
        }

        if ($sender->role === \App\Models\User::ROLE_USER) {
            $sender->status = 'active';
        } elseif (in_array($sender->role, [\App\Models\User::ROLE_DEALER, \App\Models\User::ROLE_MANUFACTURER], true)) {
            $sender->status = 'approved';
        }
        $sender->save();

        if (Schema::hasColumn('messages', 'support_status')) {
            $message->support_status = 'approved';
            $message->save();
        }

        return back()->with('success', 'Support request approved and account resumed.');
    }

    public function rejectSupportRequest(\App\Models\Message $message)
    {
        if ((int) $message->receiver_id !== 1) {
            return back()->with('error', 'Invalid support request.');
        }

        if (Schema::hasColumn('messages', 'support_status')) {
            $message->support_status = 'rejected';
            $message->save();
        }

        return back()->with('success', 'Support request rejected.');
    }

    public function hotTubs()
    {
        $hotTubs = \App\Models\HotTub::with('brand')
            ->orderBy('created_at', 'desc')
            ->paginate(7);

        return view('admin.hot-tubs', compact('hotTubs'));
    }

    public function payments()
    {
        $creditRequests = \App\Models\CreditRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(7);

        $revenue = \App\Models\Invoice::where('status', 'paid')->sum('amount');
        $pending = \App\Models\CreditRequest::where('status', 'pending')->count();
        $completed = \App\Models\CreditRequest::where('status', 'approved')->count();
        $failed = \App\Models\CreditRequest::where('status', 'rejected')->count();

        $invoices = \App\Models\Invoice::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(7);

        return view('admin.payments', compact('creditRequests', 'revenue', 'pending', 'completed', 'failed', 'invoices'));
    }

    public function approveCreditRequest(\App\Models\CreditRequest $request)
    {
        if ($request->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $user = $request->user;
        $user->credits += $request->credits;
        $user->save();

        $request->status = 'approved';
        $request->save();

        // Check if an invoice for this payment ID already exists (from webhook)
        $paymentId = 'MANUAL-CREDIT-' . $request->id;
        $exists = \App\Models\Invoice::where('payment_id', $paymentId)->exists();

        if (!$exists) {
            \App\Models\Invoice::create([
                'invoice_number' => 'INV-' . time() . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'dealer_id' => $user->id,
                'credits' => $request->credits,
                'amount' => $request->amount ?: 0,
                'status' => 'paid',
                'payment_id' => $paymentId,
            ]);
        }

        return back()->with('success', 'Credit request approved and credits added to account.');
    }

    public function rejectCreditRequest(\App\Models\CreditRequest $request)
    {
        if ($request->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $request->status = 'rejected';
        $request->save();

        return back()->with('success', 'Credit request has been rejected.');
    }

    public function plans()
    {
        $plans = \App\Models\CreditPlan::orderBy('created_at', 'desc')->get();
        return view('admin.plans', compact('plans'));
    }

    public function storePlan(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'badge_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        \App\Models\CreditPlan::create($data);

        return redirect()->back()->with('success', 'Credit plan created successfully.');
    }

    public function updatePlan(\Illuminate\Http\Request $request, \App\Models\CreditPlan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'badge_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $plan->update($data);

        return redirect()->back()->with('success', 'Credit plan updated successfully.');
    }

    public function destroyPlan(\App\Models\CreditPlan $plan)
    {
        $plan->delete();
        return redirect()->back()->with('success', 'Credit plan deleted successfully.');
    }
}
