<?php

namespace App\Http\Controllers;

use App\Models\HotTub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PageController extends Controller
{
    private function tierFilterOptions(): array
    {
        return [
            'entry-level' => 'Entry Level',
            'luxury' => 'Luxury',
            'mid-range' => 'Mid-range',
        ];
    }

    private function normalizeTierFilter(?string $tier): ?string
    {
        if (!$tier) {
            return null;
        }

        $normalized = strtolower(trim(str_replace('_', '-', $tier)));
        $normalized = preg_replace('/\s+/', '-', $normalized);

        if (in_array($normalized, ['luxury'], true)) {
            return 'luxury';
        }

        if (in_array($normalized, ['entry-level', 'entry', 'entrylevel', 'budget'], true)) {
            return 'entry-level';
        }

        if (in_array($normalized, ['mid-range', 'midrange', 'mid'], true)) {
            return 'mid-range';
        }

        return null;
    }

    private function applyTierFilter($query, Request $request): void
    {
        $tier = $this->normalizeTierFilter($request->query('tier'));
        if (!$tier) {
            return;
        }

        $allowed = match ($tier) {
            'luxury' => ['luxury'],
            'entry-level' => ['entry-level', 'entry', 'entrylevel', 'budget'],
            default => ['mid-range', 'mid', 'midrange'],
        };

        $placeholders = implode(',', array_fill(0, count($allowed), '?'));
        $query->whereRaw(
            'LOWER(REPLACE(REPLACE(tier, " ", "-"), "_", "-")) IN (' . $placeholders . ')',
            $allowed
        );
    }

    private function activeBrandNames(?string $context = null): array
    {
        if (!Schema::hasTable('brands')) {
            return [];
        }

        try {
            $q = $this->activeBrandsQuery();
            if ($context) {
                $q->where(function ($sq) use ($context) {
                    // New JSON multi-type support.
                    if (Schema::hasColumn('brands', 'types')) {
                        $sq->whereJsonContains('types', $context);
                        if (in_array($context, ['hot_tub', 'swim_spa'], true)) {
                            $sq->orWhereJsonContains('types', 'both');
                        }
                    }

                    // Legacy single-type fallback compatibility.
                    if (Schema::hasColumn('brands', 'type')) {
                        $sq->orWhere('type', $context);
                        if (in_array($context, ['hot_tub', 'swim_spa'], true)) {
                            $sq->orWhere('type', 'both');
                        }
                    }
                });
            }

            return $q
                ->pluck('name')
                ->filter()
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            report($e);

            $q = $this->activeBrandsQuery();
            if ($context && Schema::hasColumn('brands', 'type')) {
                $q->where(function ($sq) use ($context) {
                    $sq->where('type', $context);
                    if (in_array($context, ['hot_tub', 'swim_spa'], true)) {
                        $sq->orWhere('type', 'both');
                    }
                });
            }

            return $q
                ->pluck('name')
                ->filter()
                ->values()
                ->toArray();
        }
    }

    private function resolveBrandFilterName(?string $param): ?string
    {
        if (!$param || !Schema::hasTable('brands')) {
            return $param;
        }

        $rowQuery = \App\Models\Brand::where('slug', $param)
            ->orWhere('name', $param)
            ->orderBy('name');

        if (Schema::hasColumn('brands', 'is_active')) {
            $rowQuery->where('is_active', true);
        }

        $row = $rowQuery->first();

        return $row ? $row->name : $param;
    }

    private function activeBrandsQuery()
    {
        $q = \App\Models\Brand::query()->orderBy('name');
        if (Schema::hasColumn('brands', 'is_active')) {
            $q->where('is_active', true);
        }

        return $q;
    }

    public function hotTubs(Request $request)
    {
        $items = collect();
        $brands = collect();
        $modelsByBrand = [];
        $seatOptions = [];
        if (Schema::hasTable('hot_tubs')) {
            $q = HotTub::where('status', 'active')->where('product_type', 'hot_tub');
            $activeBrandNames = $this->activeBrandNames('hot_tub');
            if (!empty($activeBrandNames)) {
                $q->whereIn('brand', $activeBrandNames);
            }
            $brandParam = $request->query('brand');
            if ($brandParam) {
                $brandName = $brandParam;
                if (Schema::hasTable('brands')) {
                    $rowQuery = \App\Models\Brand::where('slug', $brandParam)
                        ->orWhere('name', $brandParam);
                    if (Schema::hasColumn('brands', 'is_active')) {
                        $rowQuery->where('is_active', true);
                    }
                    $row = $rowQuery->first();
                    if ($row) $brandName = $row->name;
                }
                $q->whereRaw('LOWER(brand) = ?', [mb_strtolower($brandName)]);
            }
            if ($request->filled('model')) {
                $q->where('model', $request->query('model'));
            }
            $this->applyTierFilter($q, $request);
            if ($request->filled('min_seats')) {
                $q->where('seats', '>=', (int) $request->query('min_seats'));
            }
            $perPage = (int) $request->query('per_page', 9);
            $perPage = $perPage > 0 && $perPage <= 60 ? $perPage : 12;
            $items = $q->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
            if (Schema::hasTable('brands')) {
                $brands = $this->activeBrandsQuery()->get();
            }
            $allQuery = HotTub::where('status', 'active')->where('product_type', 'hot_tub');
            if (!empty($activeBrandNames)) {
                $allQuery->whereIn('brand', $activeBrandNames);
            }
            $all = $allQuery->get(['brand', 'model']);
            foreach ($all as $row) {
                $modelsByBrand[$row->brand][] = $row->model;
            }
            foreach ($modelsByBrand as $b => $arr) {
                $modelsByBrand[$b] = array_values(array_unique($arr));
            }
            $seatOptions = HotTub::where('status', 'active')
                ->where('product_type', 'hot_tub')
                ->when(!empty($activeBrandNames), function ($sq) use ($activeBrandNames) {
                    $sq->whereIn('brand', $activeBrandNames);
                })
                ->whereNotNull('seats')
                ->select('seats')
                ->distinct()
                ->orderBy('seats')
                ->pluck('seats')
                ->map(fn ($seat) => (int) $seat)
                ->filter(fn ($seat) => $seat > 0)
                ->values()
                ->toArray();
            if ($request->boolean('fragment')) {
                $html = view('components.hot-tub-cards-fragment', ['items' => $items])->render();
                return response()->json([
                    'html' => $html,
                    'hasMore' => $items->hasMorePages(),
                    'nextPage' => $items->currentPage() + 1,
                ]);
            }
        }
        return view('pages.hot_tubs', [
            'items' => $items,
            'brands' => $brands,
            'modelsByBrand' => $modelsByBrand,
            'seatOptions' => $seatOptions,
            'tierFilters' => $this->tierFilterOptions(),
        ]);
    }

    public function hotTubDetail(string $slug)
    {
        abort_unless(Schema::hasTable('hot_tubs'), 404);
        $item = HotTub::where('slug', $slug)->where('status', 'active')->firstOrFail();
        return view('pages.hot_tub_detail', compact('item'));
    }

    public function swimSpaDetail(string $slug)
    {
        abort_unless(Schema::hasTable('hot_tubs'), 404);
        $item = HotTub::where('slug', $slug)->where('status', 'active')->where('product_type','swim_spa')->firstOrFail();
        return view('pages.swim_spa_detail', compact('item'));
    }

    public function swimSpas()
    {
        return $this->swimSpasList(request());
    }

    public function swimSpasList(Request $request)
    {
        $items = collect();
        $brands = collect();
        $modelsByBrand = [];
        $seatOptions = [];
        $swimSpaCatalogTotal = 0;
        if (Schema::hasTable('hot_tubs')) {
            $q = HotTub::where('status', 'active')->where('product_type', 'swim_spa');
            $activeBrandNames = $this->activeBrandNames('swim_spa');
            if (!empty($activeBrandNames)) {
                $q->whereIn('brand', $activeBrandNames);
            }
            $brandSlug = $request->query('brand');
            $brandName = null;
            if ($brandSlug) {
                $brandName = $this->resolveBrandFilterName($brandSlug);
                $q->whereRaw('LOWER(brand) = ?', [mb_strtolower((string) $brandName)]);
            }
            if ($request->filled('model')) {
                $q->where('model', $request->query('model'));
            }
            $this->applyTierFilter($q, $request);
            if ($request->filled('min_seats')) {
                $q->where('seats', '>=', (int) $request->query('min_seats'));
            }
            $perPage = (int) $request->query('per_page', 9);
            $perPage = $perPage > 0 && $perPage <= 60 ? $perPage : 9;
            $items = $q->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
            if (Schema::hasTable('brands')) {
                $brands = $this->activeBrandsQuery()->get();
            }
            $allQuery = HotTub::where('status', 'active')->where('product_type', 'swim_spa');
            if (!empty($activeBrandNames)) {
                $allQuery->whereIn('brand', $activeBrandNames);
            }
            $all = $allQuery->get(['brand', 'model']);
            $swimSpaCatalogTotal = $all->count();
            foreach ($all as $row) {
                $modelsByBrand[$row->brand][] = $row->model;
            }
            foreach ($modelsByBrand as $b => $arr) {
                $modelsByBrand[$b] = array_values(array_unique($arr));
            }
            $seatOptions = HotTub::where('status', 'active')
                ->where('product_type', 'swim_spa')
                ->when(!empty($activeBrandNames), function ($sq) use ($activeBrandNames) {
                    $sq->whereIn('brand', $activeBrandNames);
                })
                ->whereNotNull('seats')
                ->select('seats')
                ->distinct()
                ->orderBy('seats')
                ->pluck('seats')
                ->map(fn ($seat) => (int) $seat)
                ->filter(fn ($seat) => $seat > 0)
                ->values()
                ->toArray();
            if ($request->boolean('fragment')) {
                $html = view('components.swim-spa-cards-fragment', ['items' => $items])->render();
                return response()->json([
                    'html' => $html,
                    'hasMore' => $items->hasMorePages(),
                    'nextPage' => $items->currentPage() + 1,
                ]);
            }
        }
        return view('pages.swim_spas2', [
            'items' => $items,
            'brands' => $brands,
            'modelsByBrand' => $modelsByBrand,
            'seatOptions' => $seatOptions,
            'swimSpaCatalogTotal' => $swimSpaCatalogTotal,
            'tierFilters' => $this->tierFilterOptions(),
        ]);
    }

    public function services()
    {
        $items = [];
        if (Schema::hasTable('services')) {
            $items = \App\Models\Service::where('status','active')->orderBy('created_at','desc')->get();
        }
        return view('pages.services', compact('items'));
    }

    public function serviceDetail(string $slug)
    {
        abort_unless(Schema::hasTable('services'), 404);
        $item = \App\Models\Service::where('slug',$slug)->where('status','active')->firstOrFail();
        return view('pages.service_detail', compact('item'));
    }

    public function outdoorProducts(Request $request)
    {
        $items = collect();
        $brands = collect();
        $modelsByBrand = [];
        if (Schema::hasTable('outdoor_products')) {
            $q = \App\Models\OutdoorProduct::where('status', 'active');
            $activeBrandNames = $this->activeBrandNames();
            if (!empty($activeBrandNames)) {
                $q->whereIn('brand', $activeBrandNames);
            }
            
            if ($request->filled('brand')) {
                $resolved = $this->resolveBrandFilterName($request->query('brand'));
                $q->whereRaw('LOWER(brand) = ?', [mb_strtolower((string) $resolved)]);
            }
            if ($request->filled('model')) {
                $q->where('model', $request->query('model'));
            }
            $this->applyTierFilter($q, $request);
            
            $perPage = (int) $request->query('per_page', 9);
            $items = $q->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
            
            if (Schema::hasTable('brands')) {
                $brands = $this->activeBrandsQuery()->get();
            }
            
            $all = \App\Models\OutdoorProduct::where('status', 'active')
                ->when(!empty($activeBrandNames), function ($sq) use ($activeBrandNames) {
                    $sq->whereIn('brand', $activeBrandNames);
                })
                ->get(['brand', 'model']);
            foreach ($all as $row) {
                $modelsByBrand[$row->brand][] = $row->model;
            }
            foreach ($modelsByBrand as $b => $arr) {
                $modelsByBrand[$b] = array_values(array_unique($arr));
            }

            if ($request->boolean('fragment')) {
                $html = view('components.outdoor-product-cards-fragment', ['items' => $items])->render();
                return response()->json([
                    'html' => $html,
                    'hasMore' => $items->hasMorePages(),
                    'nextPage' => $items->currentPage() + 1,
                ]);
            }
        }
        return view('pages.outdoor_products', [
            'items' => $items,
            'brands' => $brands,
            'modelsByBrand' => $modelsByBrand,
            'tierFilters' => $this->tierFilterOptions(),
        ]);
    }

    public function outdoorProductDetail(string $slug)
    {
        abort_unless(Schema::hasTable('outdoor_products'), 404);
        $item = \App\Models\OutdoorProduct::where('slug', $slug)->where('status', 'active')->firstOrFail();
        return view('pages.outdoor_product_detail', compact('item'));
    }

    public function parts()
    {
        $items = [];
        $brandsById = [];
        if (Schema::hasTable('parts')) {
            $items = \App\Models\Part::where('status','active')->orderBy('created_at','desc')->get();
        }
        if (Schema::hasTable('brands')) {
            $brandsById = $this->activeBrandsQuery()->pluck('name', 'id')->toArray();
        }
        return view('pages.parts', [
            'items' => $items,
            'brandsById' => $brandsById,
        ]);
    }

    public function brands(Request $request)
    {
        $brands = [];
        $counts = ['hot_tub'=>[], 'swim_spa'=>[]];
        $origins = [];
        $types = [];
        if (Schema::hasTable('brands')) {
            $baseQuery = $this->activeBrandsQuery();
            // Must clear inherited orderBy('name') — MySQL rejects DISTINCT + ORDER BY non-selected columns.
            $origins = (clone $baseQuery)
                ->reorder()
                ->whereNotNull('country_of_origin')
                ->where('country_of_origin', '!=', '')
                ->select('country_of_origin')
                ->distinct()
                ->orderBy('country_of_origin')
                ->pluck('country_of_origin')
                ->values()
                ->toArray();
            $types = (clone $baseQuery)
                ->reorder()
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->select('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->values()
                ->toArray();

            $brands = $baseQuery
                ->when($request->filled('origin'), function ($q) use ($request) {
                    $q->where('country_of_origin', $request->query('origin'));
                })
                ->when($request->filled('type'), function ($q) use ($request) {
                    $q->where('type', $request->query('type'));
                })
                ->get();
        }
        if (Schema::hasTable('hot_tubs')) {
            $activeBrandNames = $this->activeBrandNames();
            $hot = \App\Models\HotTub::select('brand','product_type')
                ->where('status','active')
                ->when(!empty($activeBrandNames), function ($q) use ($activeBrandNames) {
                    $q->whereIn('brand', $activeBrandNames);
                })
                ->get();
            foreach ($hot as $h) {
                $pt = $h->product_type === 'swim_spa' ? 'swim_spa' : 'hot_tub';
                $counts[$pt][$h->brand] = ($counts[$pt][$h->brand] ?? 0) + 1;
            }
        }
        return view('pages.brands', [
            'brands' => $brands,
            'counts' => $counts,
            'origins' => $origins,
            'types' => $types,
        ]);
    }

    /**
     * Single brand page (slug from admin-managed brands).
     */
    public function brandDetail(string $slug)
    {
        abort_unless(Schema::hasTable('brands'), 404);

        $q = \App\Models\Brand::query()->where('slug', $slug);
        if (Schema::hasColumn('brands', 'is_active')) {
            $q->where('is_active', true);
        }
        $brand = $q->firstOrFail();

        $counts = ['hot_tub' => 0, 'swim_spa' => 0];
        if (Schema::hasTable('hot_tubs')) {
            $nameLower = mb_strtolower((string) $brand->name);
            $counts['hot_tub'] = HotTub::query()
                ->where('status', 'active')
                ->where('product_type', 'hot_tub')
                ->whereRaw('LOWER(brand) = ?', [$nameLower])
                ->count();
            $counts['swim_spa'] = HotTub::query()
                ->where('status', 'active')
                ->where('product_type', 'swim_spa')
                ->whereRaw('LOWER(brand) = ?', [$nameLower])
                ->count();
        }

        return view('pages.brand_detail', compact('brand', 'counts'));
    }

    public function careGuide()
    {
        return view('pages.care_guide');
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function findDealer()
    {
        return view('pages.find_dealer');
    }

    public function login()
    {
        return view('pages.login');
    }

    public function register()
    {
        return view('pages.register');
    }
}
