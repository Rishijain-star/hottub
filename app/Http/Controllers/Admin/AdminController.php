<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\PublicMedia;
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
        $manufacturersPending = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'manufacturer')->where('status', 'pending')->count() : 0;
        $pendingPartnerRegistrations = $dealersPending + $manufacturersPending;
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
            $dealerPurchaseQuery = DB::table('lead_purchases')
                ->join('leads', 'leads.id', '=', 'lead_purchases.lead_id')
                ->where('lead_purchases.buyer_role', 'dealer');
            $manufacturerPurchaseQuery = DB::table('lead_purchases')
                ->join('leads', 'leads.id', '=', 'lead_purchases.lead_id')
                ->where('lead_purchases.buyer_role', 'manufacturer');
            $leadsBase = DB::table('leads');
            $convertedLeadsBase = DB::table('leads')->where('status', 'converted');

            if ($range) {
                [$from, $to] = $range;
                $dealerPurchaseQuery->whereBetween('lead_purchases.created_at', [$from, $to]);
                $manufacturerPurchaseQuery->whereBetween('lead_purchases.created_at', [$from, $to]);
                $leadsBase->whereBetween('created_at', [$from, $to]);
                $convertedLeadsBase->whereBetween('created_at', [$from, $to]);
            }

            $dealerPurchasedCount = (int) $dealerPurchaseQuery->count();
            $manufacturerPurchasedCount = (int) $manufacturerPurchaseQuery->count();

            // Total leads in overview must be unique leads, not purchase frequency.
            $leadsTotal = (int) (clone $leadsBase)->count();

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
                ->map(fn($wins, $brand) => ['brand' => $brand, 'wins' => $wins])
                ->values();

            $dealerRankings = $this->buildUserRanking('dealer', $range);
            $manufacturerRankings = $this->buildUserRanking('manufacturer', $range);
        }

        return compact(
            'dealersTotal',
            'dealersApproved',
            'dealersPending',
            'manufacturersPending',
            'pendingPartnerRegistrations',
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
                $join
                    ->on('users.id', '=', 'leads.assigned_dealer_id')
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
            ->join('leads', 'leads.id', '=', 'lead_purchases.lead_id')
            ->where('buyer_role', $role);
        if ($range) {
            [$from, $to] = $range;
            $purchaseQuery->whereBetween('lead_purchases.created_at', [$from, $to]);
        }
        $purchasesByUser = $purchaseQuery
            ->select('lead_purchases.dealer_id', DB::raw('COUNT(*) as purchases'))
            ->groupBy('lead_purchases.dealer_id')
            ->pluck('purchases', 'lead_purchases.dealer_id');

        return $winsQuery
            ->get()
            ->map(function ($row) use ($purchasesByUser) {
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
            ->sortByDesc(fn($row) => [$row['wins'], $row['conversion_rate']])
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
        $q = \App\Models\Message::where('receiver_id', 1)
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->select('messages.*', 'users.name as sender_name', 'users.email as sender_email', 'users.role as sender_role', 'users.status as sender_status', 'users.company_name')
            ->orderBy('messages.created_at', 'desc');

        if ($hasSupportStatusColumn) {
            $q->whereNotNull('messages.support_status');
        }

        $requests = $q->paginate(7);

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

    public function invoice(string $invoice)
    {
        $inv = \App\Models\Invoice::where('invoice_number', $invoice)
            ->with('user')
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

        $customerName = $inv->user?->company_name ?: ($inv->user?->name ?? 'N/A');

        $data = [
            'invoice' => $inv->invoice_number,
            'date' => optional($inv->created_at)->format('d/m/Y'),
            'time' => optional($inv->created_at)->format('H:i:s'),
            'customer' => $customerName,
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

        return view('admin.invoice', $data);
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

    public function settings()
    {
        $homepageHeroImages = json_decode(SiteSetting::get('homepage_hero_images', '[]') ?? '[]', true);
        if (!is_array($homepageHeroImages)) {
            $homepageHeroImages = [];
        }

        $homepageHeroImages = collect($homepageHeroImages)
            ->map(function ($item, $index) {
                $path = is_array($item) ? ($item['path'] ?? null) : null;
                if (!$path) {
                    return null;
                }

                return [
                    'path' => $path,
                    'sort' => (int) (is_array($item) ? ($item['sort'] ?? ($index + 1)) : ($index + 1)),
                    'url' => PublicMedia::url($path),
                ];
            })
            ->filter()
            ->sortBy('sort')
            ->values()
            ->all();

        $homepageCtaImage = SiteSetting::get('homepage_cta_image');
        $homepageCtaImage = $homepageCtaImage ? [
            'path' => PublicMedia::normalizeStoredPath($homepageCtaImage) ?? $homepageCtaImage,
            'url' => PublicMedia::url($homepageCtaImage),
        ] : null;

        $businessDetails = [
            'vat_number' => SiteSetting::get('company_vat_number', '842368419'),
            'company_number' => SiteSetting::get('company_number', '049947'),
            'fca_number' => SiteSetting::get('company_fca_number'),
            'company_name' => SiteSetting::get('company_name', 'Hot Tub Buyer Ltd'),
            'company_email' => SiteSetting::get('company_email', 'support@hottubbuyer.com'),
            'company_address' => SiteSetting::get('company_address'),
        ];

        $socialLinks = [
            'facebook' => SiteSetting::get('social_facebook_url'),
            'twitter' => SiteSetting::get('social_twitter_url'),
            'instagram' => SiteSetting::get('social_instagram_url'),
            'tiktok' => SiteSetting::get('social_tiktok_url'),
        ];

        return view('admin.settings', compact('homepageHeroImages', 'homepageCtaImage', 'businessDetails', 'socialLinks'));
    }

    public function updateSettings(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'hero_images' => 'nullable|array',
            'hero_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
            'cta_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
            'existing_hero_paths' => 'nullable|array',
            'existing_hero_paths.*' => 'nullable|string|max:1000',
            'existing_hero_sorts' => 'nullable|array',
            'existing_hero_sorts.*' => 'nullable|integer|min:1',
            'remove_hero_paths' => 'nullable|array',
            'remove_hero_paths.*' => 'nullable|string|max:1000',
            'existing_cta_path' => 'nullable|string|max:1000',
            'remove_cta_image' => 'nullable|boolean',
        ]);

        $removePaths = collect($request->input('remove_hero_paths', []))
            ->filter(fn($v) => is_string($v) && $v !== '')
            ->values()
            ->all();

        $existingPaths = collect($request->input('existing_hero_paths', []))
            ->filter(fn($v) => is_string($v) && $v !== '' && !in_array($v, $removePaths, true))
            ->values();
        $existingSorts = collect($request->input('existing_hero_sorts', []))->values();

        $heroImages = [];
        foreach ($existingPaths as $idx => $path) {
            $sort = (int) ($existingSorts->get($idx) ?: ($idx + 1));
            $canonical = PublicMedia::normalizeStoredPath($path) ?? $path;
            $heroImages[] = ['path' => $canonical, 'sort' => max(1, $sort)];
        }

        if ($request->hasFile('hero_images')) {
            foreach ((array) $request->file('hero_images') as $file) {
                if (!$file) {
                    continue;
                }
                $storedPath = $file->store('hero-images', 'public');
                $canonical = PublicMedia::normalizeStoredPath($storedPath) ?? $storedPath;
                $heroImages[] = [
                    'path' => $canonical,
                    'sort' => count($heroImages) + 1,
                ];
            }
        }

        usort($heroImages, fn($a, $b) => ($a['sort'] <=> $b['sort']));
        $heroImages = array_values(array_map(
            fn($item, $i) => ['path' => $item['path'], 'sort' => $i + 1],
            $heroImages,
            array_keys($heroImages)
        ));

        SiteSetting::set('homepage_hero_images', json_encode($heroImages));

        $ctaPath = $request->boolean('remove_cta_image')
            ? null
            : (PublicMedia::normalizeStoredPath($request->input('existing_cta_path')) ?? $request->input('existing_cta_path'));

        if ($request->hasFile('cta_image')) {
            $storedPath = $request->file('cta_image')->store('cta-images', 'public');
            $ctaPath = PublicMedia::normalizeStoredPath($storedPath) ?? $storedPath;
        }

        SiteSetting::set('homepage_cta_image', $ctaPath);

        // ── Business details & social links ────────────────────────────
        $businessValidated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:1000',
            'company_vat_number' => 'nullable|string|max:100',
            'company_number' => 'nullable|string|max:100',
            'company_fca_number' => 'nullable|string|max:100',
            'social_facebook_url' => 'nullable|url|max:500',
            'social_twitter_url' => 'nullable|url|max:500',
            'social_instagram_url' => 'nullable|url|max:500',
            'social_tiktok_url' => 'nullable|url|max:500',
        ]);

        foreach ($businessValidated as $key => $value) {
            // Only persist fields the admin actually submitted so partial updates are safe.
            if ($request->has($key)) {
                SiteSetting::set($key, $value);
            }
        }

        return back()->with('success', 'Settings saved.');
    }
}
