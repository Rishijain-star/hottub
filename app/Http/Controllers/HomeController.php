<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\SiteSetting;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
class HomeController extends Controller
{
    public function index()
    {
        $heroBgUrl = PublicMedia::url(SiteSetting::get('homepage_hero_bg'));
        $ctaImageUrl = PublicMedia::url(SiteSetting::get('homepage_cta_image'));
        $heroImagesRaw = json_decode(SiteSetting::get('homepage_hero_images', '[]') ?? '[]', true);
        if (!is_array($heroImagesRaw)) {
            $heroImagesRaw = [];
        }
        $heroImages = collect($heroImagesRaw)
            ->map(function ($item, $index) {
                $path = is_array($item) ? ($item['path'] ?? null) : null;
                if (!$path || !is_string($path)) {
                    return null;
                }
                $url = PublicMedia::url($path);
                if (!$url) {
                    return null;
                }

                return [
                    'url' => $url,
                    'sort' => (int) (is_array($item) ? ($item['sort'] ?? ($index + 1)) : ($index + 1)),
                ];
            })
            ->filter()
            ->sortBy('sort')
            ->pluck('url')
            ->values()
            ->all();

        $featuredHotTubs = \App\Models\HotTub::where('status', 'active')
            ->where(function ($q) {
                $q->where('featured_on_homepage', true)
                    ->orWhereHas('featuredContents', function ($fq) {
                        $fq->where('show_on_homepage', true)
                            ->where('status', 'active');
                    });
            })
            ->with(['featuredContents' => function ($q) {
                $q->where('show_on_homepage', true)->where('status', 'active');
            }])
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        if ($featuredHotTubs->isEmpty()) {
            $featuredHotTubs = \App\Models\HotTub::where('status', 'active')->take(10)->get();
        }

        $potm = \App\Models\FeaturedContent::where('content_type', 'product_of_month')
            ->where('status', 'active')
            ->with('hotTub')
            ->orderBy('created_at', 'desc')
            ->first();

        $dotw = \App\Models\FeaturedContent::where('content_type', 'delivery_of_week')
            ->where('status', 'active')
            ->with('hotTub')
            ->orderBy('created_at', 'desc')
            ->first();

        $premiumBrands = collect();
        if (Schema::hasTable('brands')) {
            $brandQuery = Brand::query()->where('featured', true);
            if (Schema::hasColumn('brands', 'is_active')) {
                $brandQuery->where('is_active', true);
            }
            $premiumBrands = $brandQuery->orderBy('name')->get();
        }

        return view('pages.home', compact('featuredHotTubs', 'potm', 'dotw', 'heroBgUrl', 'ctaImageUrl', 'heroImages', 'premiumBrands'));
    }
}
