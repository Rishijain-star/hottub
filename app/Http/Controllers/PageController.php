<?php

namespace App\Http\Controllers;

use App\Models\HotTub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PageController extends Controller
{
    public function hotTubs(Request $request)
    {
        $items = collect();
        $brands = collect();
        $modelsByBrand = [];
        if (Schema::hasTable('hot_tubs')) {
            $q = HotTub::where('status', 'active')->where('product_type', 'hot_tub');
            $brandParam = $request->query('brand');
            if ($brandParam) {
                $brandName = $brandParam;
                if (Schema::hasTable('brands')) {
                    $row = \App\Models\Brand::where('slug', $brandParam)
                        ->orWhere('name', $brandParam)
                        ->first();
                    if ($row) $brandName = $row->name;
                }
                $q->whereRaw('LOWER(brand) = ?', [mb_strtolower($brandName)]);
            }
            if ($request->filled('model')) {
                $q->where('model', $request->query('model'));
            }
            if ($request->filled('tier')) {
                $q->whereRaw('LOWER(REPLACE(tier, \" \", \"-\")) = ?', [strtolower(str_replace(' ', '-', $request->query('tier')))]);
            }
            if ($request->filled('min_seats')) {
                $q->where('seats', '>=', (int) $request->query('min_seats'));
            }
            $perPage = (int) $request->query('per_page', 9);
            $perPage = $perPage > 0 && $perPage <= 60 ? $perPage : 12;
            $items = $q->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
            if (Schema::hasTable('brands')) {
                $brands = \App\Models\Brand::orderBy('name')->get();
            }
            $all = HotTub::where('status', 'active')->where('product_type', 'hot_tub')->get(['brand', 'model']);
            foreach ($all as $row) {
                $modelsByBrand[$row->brand][] = $row->model;
            }
            foreach ($modelsByBrand as $b => $arr) {
                $modelsByBrand[$b] = array_values(array_unique($arr));
            }
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
        if (Schema::hasTable('hot_tubs')) {
            $q = HotTub::where('status', 'active')->where('product_type', 'swim_spa');
            $brandSlug = $request->query('brand');
            $brandName = null;
            if ($brandSlug) {
                if (Schema::hasTable('brands')) {
                    $brandName = \App\Models\Brand::where('slug', $brandSlug)->value('name') ?: $brandSlug;
                } else {
                    $brandName = $brandSlug;
                }
                $q->where('brand', $brandName);
            }
            if ($request->filled('model')) {
                $q->where('model', $request->query('model'));
            }
            if ($request->filled('min_seats')) {
                $q->where('seats', '>=', (int) $request->query('min_seats'));
            }
            $perPage = (int) $request->query('per_page', 9);
            $perPage = $perPage > 0 && $perPage <= 60 ? $perPage : 9;
            $items = $q->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
            if (Schema::hasTable('brands')) {
                $brands = \App\Models\Brand::orderBy('name')->get();
            }
            $all = HotTub::where('status', 'active')->where('product_type', 'swim_spa')->get(['brand', 'model']);
            foreach ($all as $row) {
                $modelsByBrand[$row->brand][] = $row->model;
            }
            foreach ($modelsByBrand as $b => $arr) {
                $modelsByBrand[$b] = array_values(array_unique($arr));
            }
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
            
            if ($request->filled('brand')) {
                $q->where('brand', $request->query('brand'));
            }
            if ($request->filled('model')) {
                $q->where('model', $request->query('model'));
            }
            
            $perPage = (int) $request->query('per_page', 9);
            $items = $q->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
            
            if (Schema::hasTable('brands')) {
                $brands = \App\Models\Brand::orderBy('name')->get();
            }
            
            $all = \App\Models\OutdoorProduct::where('status', 'active')->get(['brand', 'model']);
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
            $brandsById = \App\Models\Brand::pluck('name','id')->toArray();
        }
        return view('pages.parts', [
            'items' => $items,
            'brandsById' => $brandsById,
        ]);
    }

    public function brands()
    {
        $brands = [];
        $counts = ['hot_tub'=>[], 'swim_spa'=>[]];
        if (Schema::hasTable('brands')) {
            $brands = \App\Models\Brand::orderBy('name')->get();
        }
        if (Schema::hasTable('hot_tubs')) {
            $hot = \App\Models\HotTub::select('brand','product_type')
                ->where('status','active')->get();
            foreach ($hot as $h) {
                $pt = $h->product_type === 'swim_spa' ? 'swim_spa' : 'hot_tub';
                $counts[$pt][$h->brand] = ($counts[$pt][$h->brand] ?? 0) + 1;
            }
        }
        return view('pages.brands', [
            'brands' => $brands,
            'counts' => $counts,
        ]);
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
