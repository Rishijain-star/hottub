@extends('layouts.app')
@section('title', 'Hot Tub Parts – Genuine Replacement Parts & Accessories')
@section('content')

{{-- ══ HERO HEADER ══════════════════════════════════════════════════════════ --}}
<section class="svc-hero">
    <div class="container" style="text-align:center;">
        <span class="svc-hero__badge">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Genuine Parts
        </span>
        <h1 class="svc-hero__title">Hot Tub Parts</h1>
        <p class="svc-hero__desc">Quality replacement parts for all major hot tub brands. Fast UK delivery with expert support to help you find exactly what you need.</p>
    </div>
</section>

{{-- ══ CATEGORY TABS ════════════════════════════════════════════════════════ --}}
<section class="parts-cats-section">
    <div class="container" style="text-align:center;">
        <h2 class="parts-cats__heading">Shop by Category</h2>
        <div class="parts-cats" id="catTabs">
            <button class="parts-cat-btn parts-cat-btn--active" data-cat="all">
                All Parts
            </button>
            <button class="parts-cat-btn" data-cat="Filters">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
            </button>
            <button class="parts-cat-btn" data-cat="Pumps">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                Pumps
            </button>
            <button class="parts-cat-btn" data-cat="Heaters">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 5-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/></svg>
                Heaters
            </button>
            <button class="parts-cat-btn" data-cat="Controls">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                Controls
            </button>
            <button class="parts-cat-btn" data-cat="Jets">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                Jets
            </button>
            <button class="parts-cat-btn" data-cat="Covers">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                Covers
            </button>
            <button class="parts-cat-btn" data-cat="Chemicals">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Chemicals
            </button>
            <button class="parts-cat-btn" data-cat="Other">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Other
            </button>
        </div>
    </div>
</section>

{{-- ══ PARTS GRID ═══════════════════════════════════════════════════════════ --}}
<section class="section section--white" style="padding-top:1.5rem;">
    <div class="container">
        <div class="parts-grid" id="partsGrid">
@if(isset($items) && count($items))
@foreach($items as $p)
@php
    $img = (is_array($p->images) && count($p->images)) ? $p->images[0] : null;
    $cat = $p->category ?: 'Other';
    $compatible = [];
    if (is_array($p->compatible_brand_ids)) {
        foreach ($p->compatible_brand_ids as $bid) {
            if (isset($brandsById[$bid])) $compatible[] = $brandsById[$bid];
        }
    }
@endphp
            <div class="parts-card" data-cat="{{ $cat }}">
                <div class="parts-card__img" @if($img) style="background-image:url('{{ $img }}');background-size:cover;background-position:center" @endif>
                    @if(!$img)
                    <svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    @endif
                </div>
                <div class="parts-card__body">
                    <span class="parts-cat-badge">{{ $cat }}</span>
                    <div class="parts-card__name">{{ $p->name }}</div>
                    @if($p->part_number)
                    <div class="parts-card__partno">Part #: {{ $p->part_number }}</div>
                    @endif
                    @if(!is_null($p->price))
                    <div class="parts-card__price">£{{ number_format($p->price, 2) }}</div>
                    @endif
                    @if(count($compatible))
                    <div class="text-sm text-muted" style="margin-top:.4rem">Compatible with:</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin:.35rem 0 .65rem">
                        @foreach(array_slice($compatible,0,4) as $i=>$brand)
                        <span class="badge" style="border-radius:999px;border:1px solid var(--gray-200);padding:.22rem .5rem;background:#f8fafb">{{ $brand }}</span>
                        @endforeach
                        @if(count($compatible)>4)
                        <span class="badge" style="border-radius:999px;border:1px solid var(--gray-200);padding:.22rem .5rem;background:#f8fafb">+{{ count($compatible)-4 }} more</span>
                        @endif
                    </div>
                    @endif
                    <button class="parts-enquire-btn" onclick="window.__openEnquiryModal({ title: 'Enquire: {{ addslashes($p->name) }}', subtitle: 'Please provide quantity and hot tub model.', type: 'part', product_id: '{{ $p->id }}' })">Enquire Now</button>
                </div>
            </div>
@endforeach
@endif
        </div>
        <div class="ht-no-results" id="partsNoResults" style="display:none;">
            <div class="ht-no-results__icon">🔍</div>
            <h3>No parts found in this category</h3>
            <p>Try selecting a different category or view all parts.</p>
            <button class="btn btn--outline btn--pill" onclick="filterParts('all')">View All Parts</button>
        </div>
    </div>
</section>

{{-- ══ BOTTOM 2-COL ═════════════════════════════════════════════════════════ --}}
<section class="section section--gray" style="padding-top:0;">
    <div class="container">
        <div class="parts-bottom-grid">

            {{-- Genuine OEM --}}
            <div class="parts-oem-box">
                <div class="parts-oem__icon">
                    <svg width="28" height="28" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h2 class="parts-oem__title">Genuine OEM Parts</h2>
                <p class="parts-oem__desc">We stock genuine manufacturer parts for all major brands including Jacuzzi, Hot Spring, Sundance, and more. Every part comes with a warranty and installation support.</p>
                <ul class="parts-oem__list">
                    <li>
                        <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        12-month warranty on all parts
                    </li>
                    <li>
                        <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Fast UK delivery (1–3 working days)
                    </li>
                    <li>
                        <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Expert installation guidance included
                    </li>
                </ul>
            </div>

            {{-- Part Finder --}}
            <div class="parts-finder-box">
                <div class="parts-finder__icon">
                    <svg width="28" height="28" fill="none" stroke="var(--teal)" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <h2 class="parts-finder__title">Need Help Finding a Part?</h2>
                <p class="parts-finder__desc">Not sure which part you need? Our technical team can help identify the correct replacement part for your hot tub. Just provide your model information and describe the issue.</p>
                <div class="form-group">
                    <input class="form-input" type="text" id="finderModel" placeholder="Hot tub brand &amp; model">
                </div>
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <textarea class="form-input" rows="4" id="finderDesc" placeholder="Describe what you need or the problem you're experiencing..." style="resize:vertical;"></textarea>
                </div>
                <button class="svc-request-btn" onclick="submitPartFinder()">
                    Get Expert Help
                </button>
            </div>

        </div>
    </div>
</section>

{{-- ══ ENQUIRE MODAL ════════════════════════════════════════════════════════ --}}
{{-- Per-page enquiry modal removed; using global enquiry modal --}}

{{-- ══ JAVASCRIPT ══════════════════════════════════════════════════════════ --}}
<script>
/* ── FILTERING (client-side by category) ─────────────────────────────────── */

const catColors = {
    purple:"linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%)",
    red:   "linear-gradient(135deg,#ef4444 0%,#dc2626 100%)",
    orange:"linear-gradient(135deg,#f97316 0%,#ea580c 100%)",
    blue:  "linear-gradient(135deg,#3b82f6 0%,#2563eb 100%)",
    indigo:"linear-gradient(135deg,#4f46e5 0%,#4338ca 100%)",
    teal:  "linear-gradient(135deg,#0d9488 0%,#0f766e 100%)",
    slate: "linear-gradient(135deg,#475569 0%,#334155 100%)",
    green: "linear-gradient(135deg,#22c55e 0%,#16a34a 100%)",
};

const catBadgeColors = {
    Pumps:     { bg:"#ede9fe", color:"#6d28d9" },
    Heaters:   { bg:"#fee2e2", color:"#991b1b" },
    Other:     { bg:"#fff7ed", color:"#c2410c" },
    Filters:   { bg:"#eff6ff", color:"#1d4ed8" },
    Controls:  { bg:"#eef2ff", color:"#4338ca" },
    Jets:      { bg:"#ccfbf1", color:"#0f766e" },
    Covers:    { bg:"#f1f5f9", color:"#334155" },
    Chemicals: { bg:"#dcfce7", color:"#166534" },
};

/* ── ICON SVGs ────────────────────────────────────────────────────────────── */
function getIcon(type) {
    const icons = {
        pump:    `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>`,
        heater:  `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 1 7 7c0 5-7 13-7 13S5 14 5 9a7 7 0 0 1 7-7z"/></svg>`,
        box:     `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`,
        filter:  `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>`,
        control: `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>`,
        jet:     `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>`,
        cover:   `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>`,
        chem:    `<svg width="56" height="56" fill="none" stroke="rgba(255,255,255,.88)" stroke-width="1.4" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
    };
    return icons[type] || icons.box;
}

/* ── RENDER ───────────────────────────────────────────────────────────────── */
function applyCategoryFilter(cat){
    const cards = document.querySelectorAll('.parts-card');
    let visible = 0;
    cards.forEach(c=>{
        const ok = (cat==='all') || (c.dataset.cat===cat);
        c.style.display = ok ? '' : 'none';
        if(ok) visible++;
    });
    document.getElementById('partsNoResults').style.display = visible ? 'none' : 'block';
}

/* ── FILTER ───────────────────────────────────────────────────────────────── */
function filterParts(cat){
    document.querySelectorAll('.parts-cat-btn').forEach(btn=>{
        btn.classList.toggle('parts-cat-btn--active', btn.dataset.cat===cat);
    });
    applyCategoryFilter(cat);
}

document.getElementById('catTabs').addEventListener('click', e => {
    const btn = e.target.closest('.parts-cat-btn');
    if (btn) filterParts(btn.dataset.cat);
});

/* ── PART FINDER (demo) ───────────────────────────────────────────────────── */
function submitPartFinder() {
    const model = document.getElementById('finderModel').value.trim();
    const desc  = document.getElementById('finderDesc').value.trim();
    if (!model) { alert('Please enter your hot tub brand & model.'); return; }
    if (!desc)  { alert('Please describe what you need.'); return; }
    document.getElementById('finderModel').value = '';
    document.getElementById('finderDesc').value  = '';
    document.getElementById('partSuccessOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/* ── INIT ─────────────────────────────────────────────────────────────────── */
applyCategoryFilter('all');
</script>

@endsection
