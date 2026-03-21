<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredHotTubs = \App\Models\HotTub::where('status', 'active')
            ->whereHas('featuredContents', function($q) {
                $q->where('show_on_homepage', true)
                  ->where('status', 'active');
            })
            ->with(['featuredContents' => function($q) {
                $q->where('show_on_homepage', true)->where('status', 'active');
            }])
            ->take(10)
            ->get();

        if ($featuredHotTubs->isEmpty()) {
            // Fallback to any active hot tubs if none are specifically featured on homepage
            $featuredHotTubs = \App\Models\HotTub::where('status', 'active')->take(10)->get();
        }

        $potm = \App\Models\FeaturedContent::where('content_type', 'product_of_month')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();

        $dotw = \App\Models\FeaturedContent::where('content_type', 'delivery_of_week')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('pages.home', compact('featuredHotTubs', 'potm', 'dotw'));
    }
}
