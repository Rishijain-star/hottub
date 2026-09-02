@extends('layouts.app')
@section('title', $brand->name . ' – ' . __('pages.brand_detail.page_title_suffix'))
@section('content')

@php
    $hotLink = route('hot-tubs', ['brand' => $brand->slug]);
    $swimLink = route('swim-spas', ['brand' => $brand->slug]);
    $typeLabel = $brand->type ? ucfirst(str_replace('_', ' ', $brand->type)) : __('pages.brand_detail.brand');
@endphp

<section class="svc-hero" style="border-bottom:1px solid var(--gray-200);">
    <div class="container" style="text-align:center;">
        <span class="svc-hero__badge">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            {{ __('pages.brand_detail.badge') }}
        </span>
        <h1 class="svc-hero__title">{{ $brand->name }}</h1>
        <p class="svc-hero__desc">{{ $typeLabel }}@if($brand->country_of_origin) · {{ $brand->country_of_origin }}@endif</p>
    </div>
</section>

<section class="section section--white" style="padding-top:2rem;padding-bottom:3rem;">
    <div class="container" style="max-width:900px;">
        <div style="display:flex;flex-wrap:wrap;gap:2rem;align-items:flex-start;">
            <div style="flex:0 0 200px;max-width:100%;text-align:center;">
                @if($brand->logo_path)
                    <div style="background:#f8fafb;border:1px solid var(--gray-200);border-radius:16px;padding:1.5rem;display:flex;align-items:center;justify-content:center;min-height:140px;position:relative;">
                        <img src="{{ \App\Support\PublicMedia::url($brand->logo_path) }}" alt="{{ $brand->name }}" style="max-width:100%;max-height:100px;object-fit:contain;" onerror="this.style.display='none';var f=document.getElementById('brand-logo-fallback');if(f)f.style.display='flex';">
                        <div id="brand-logo-fallback" style="display:none;width:80px;height:80px;border-radius:14px;background:var(--teal);color:#fff;font-size:2rem;font-weight:800;align-items:center;justify-content:center;">{{ strtoupper(mb_substr($brand->name, 0, 1)) }}</div>
                    </div>
                @else
                    <div style="width:120px;height:120px;margin:0 auto;border-radius:16px;background:var(--teal);color:#fff;font-size:3rem;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ strtoupper(mb_substr($brand->name, 0, 1)) }}</div>
                @endif
            </div>
            <div style="flex:1;min-width:240px;">
                @if($brand->website)
                    <p style="margin:0 0 .75rem;"><a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="btn btn--outline btn--sm">{{ __('pages.brand_detail.official_website') }}</a></p>
                @endif
                @if($brand->description)
                    <div style="color:var(--gray-700);line-height:1.65;">{!! nl2br(e($brand->description)) !!}</div>
                @else
                    <p style="color:var(--gray-500);">{{ __('pages.brand_detail.explore_catalogue', ['brand' => $brand->name]) }}</p>
                @endif
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.25rem;">
                    <span style="font-size:.85rem;background:#f1f5f9;color:var(--gray-700);padding:.35rem .75rem;border-radius:999px;font-weight:600">{{ trans_choice('pages.brand_detail.hot_tubs_count', $counts['hot_tub'] ?? 0, ['count' => $counts['hot_tub'] ?? 0]) }}</span>
                    <span style="font-size:.85rem;background:#f1f5f9;color:var(--gray-700);padding:.35rem .75rem;border-radius:999px;font-weight:600">{{ trans_choice('pages.brand_detail.swim_spas_count', $counts['swim_spa'] ?? 0, ['count' => $counts['swim_spa'] ?? 0]) }}</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.5rem;">
                    <a href="{{ $hotLink }}" class="btn btn--primary btn--pill">{{ __('pages.brand_detail.view_hot_tubs') }}</a>
                    <a href="{{ $swimLink }}" class="btn btn--outline btn--pill">{{ __('pages.brand_detail.view_swim_spas') }}</a>
                    <a href="{{ route('brands') }}" class="btn btn--ghost btn--pill">{{ __('pages.brand_detail.all_brands') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
