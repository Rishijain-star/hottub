@extends('layouts.app')
@section('title', 'Premium Hot Tub Brands – Expert Reviews & Buyer Guides')
@section('content')

{{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
<section class="svc-hero" style="border-bottom:1px solid var(--gray-200);">
    <div class="container" style="text-align:center;">
        <span class="svc-hero__badge">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Premium Hot Tub Brands
        </span>
        <h1 class="svc-hero__title">Hot Tub Brands</h1>
        <p class="svc-hero__desc">Compare the world's leading hot tub manufacturers. From luxury to value, find the brand that's right for you.</p>
    </div>
</section>

{{-- ══ FILTER BAR ═══════════════════════════════════════════════════════════ --}}
<div class="ht-filters-bar">
    <div class="container">
        <form class="ht-filters" id="brandFilters" method="GET" action="{{ route('brands') }}">
            <div class="ht-filter-group">
                <label class="ht-filter-label">Brand Type</label>
                <select class="ht-filter-select" id="filterType" name="type">
                    <option value="">All Types</option>
                    @foreach(($types ?? []) as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">Origin</label>
                <select class="ht-filter-select" id="filterOrigin" name="origin">
                    <option value="">All Countries</option>
                    @foreach(($origins ?? []) as $origin)
                        <option value="{{ $origin }}" {{ request('origin') === $origin ? 'selected' : '' }}>
                            {{ $origin }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end;gap:.5rem">
                <button type="submit" class="btn btn--primary btn--sm">Apply</button>
                <a href="{{ route('brands') }}" class="btn btn--ghost btn--sm">Clear</a>
            </div>
            <div class="ht-filter-results" id="brandCount">Showing {{ count($brands ?? []) }} brand{{ count($brands ?? [])===1 ? '' : 's' }}</div>
        </form>
    </div>
</div>

{{-- ══ BRAND GRID ════════════════════════════════════════════════════════════ --}}
<section class="section section--gray" style="padding-top:2rem;">
    <div class="container">
        <div class="brand-cards-grid" id="brandGrid">
@if(isset($brands) && count($brands))
@foreach($brands as $b)
@php
    $hotCount  = $counts['hot_tub'][$b->name]  ?? 0;
    $swimCount = $counts['swim_spa'][$b->name] ?? 0;
    $desc = $b->description ?: '';
    $type = $b->type ?: 'Brand';
    $brandLink = route('hot-tubs', ['brand' => $b->slug]);
@endphp
            <div class="brand-card {{ $b->featured ? 'brand-card--featured' : '' }}">
                @if($b->featured)
                <div class="brand-card__featured-badge">
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" style="margin-right:4px"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    FEATURED
                </div>
                @endif
                
                <a href="{{ $brandLink }}" class="brand-card__header" style="background:#f8fafb;display:flex;align-items:center;justify-content:center;text-decoration:none;color:inherit;overflow:hidden;border-bottom:1px solid #f1f5f9;height:120px;">
                    @if($b->logo_path)
                        <img src="{{ \App\Support\PublicMedia::url($b->logo_path) }}" alt="{{ $b->name }}" style="max-width:160px; max-height:80px; width:auto; height:auto; object-fit:contain; transition:transform 0.3s ease;">
                    @else
                        <div class="brand-card__initials" style="background:var(--teal);">{{ strtoupper(substr($b->name,0,1)) }}</div>
                    @endif
                </a>
                <div class="brand-card__body">
                    <div style="display:flex;justify-content:space-between;align-items:start">
                        <div class="brand-card__name">{{ $b->name }}</div>
                        @if($b->country_of_origin)
                            <span style="font-size:11px;background:#f3f4f6;color:#6b7280;padding:2px 6px;border-radius:4px;font-weight:600">{{ strtoupper($b->country_of_origin) }}</span>
                        @endif
                    </div>
                    <div class="brand-card__tagline">{{ ucfirst($type) }}</div>
                    @if($desc)
                    @include('components.card-description', ['text' => $desc, 'lines' => 3, 'class' => 'brand-card__short'])
                    @endif
                    <div class="brand-card__meta">
                        <span class="brand-card__meta-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $hotCount }} hot tubs
                        </span>
                        <span class="brand-card__meta-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            {{ $swimCount }} swim spas
                        </span>
                    </div>
                    <div style="display:flex;gap:.75rem;margin-top:auto;">
                        <a href="{{ $brandLink }}" class="btn btn--outline btn--sm" style="flex:1.2; border-radius:12px; font-weight:700;">Explore Range</a>
                        <button class="btn btn--primary btn--sm" style="flex:1; border-radius:12px; font-weight:700;" onclick="window.__openEnquiryModal({ title: 'Get a Quote — {{ addslashes($b->name) }}', subtitle: 'Request pricing from authorised {{ addslashes($b->name) }} dealers.' })">Get Quote</button>
                    </div>
                </div>
            </div>
@endforeach
@else
            <div class="text-muted" style="padding:2rem">No brands available yet.</div>
@endif
        </div>
        <div class="ht-no-results" id="brandNoResults" style="display:none;">
            <div class="ht-no-results__icon">🔍</div>
            <h3>No brands match your filters</h3>
            <p>Try adjusting your filters to see more results.</p>
            <a class="btn btn--outline btn--pill" href="{{ route('brands') }}">Clear Filters</a>
        </div>
    </div>
</section>

{{-- ══ COMPARE CTA ══════════════════════════════════════════════════════════ --}}
<section class="section section--white" style="padding-top:0;padding-bottom:3rem;">
    <div class="container" style="max-width:760px;text-align:center;">
        <div class="faq-cta">
            <h3 class="faq-cta__title">Not sure which brand is right for you?</h3>
            <p class="faq-cta__desc">Answer a few questions and our expert tool will recommend the perfect brand and model for your needs and budget.</p>
            <a href="/hot-tubs" class="btn btn--ghost btn--pill btn--lg">Browse All Hot Tubs</a>
        </div>
    </div>
</section>

{{-- ══ JAVASCRIPT ══════════════════════════════════════════════════════════ --}}
<script>
document.getElementById('filterType')?.addEventListener('change', () => {
    document.getElementById('brandFilters')?.submit();
});
document.getElementById('filterOrigin')?.addEventListener('change', () => {
    document.getElementById('brandFilters')?.submit();
});
</script>

@endsection
