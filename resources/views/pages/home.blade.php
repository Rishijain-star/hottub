@extends('layouts.app')
@section('title', __('home.title'))
@section('meta_description', __('home.meta_description'))

@section('styles')
    @php
        $homeCssPath = public_path('css/home.css');
        $homeCssVersion = file_exists($homeCssPath) ? filemtime($homeCssPath) : null;
    @endphp
    <link rel="stylesheet" href="{{ asset('css/home.css') }}{{ $homeCssVersion ? ('?v=' . $homeCssVersion) : '' }}">
@endsection

@section('content')

{{-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ --}}
@php
    $heroSlides = collect($heroImages ?? [])
        ->filter(fn ($u) => is_string($u) && $u !== '')
        ->values();
    if ($heroSlides->isEmpty() && !empty($heroBgUrl)) {
        $heroSlides = collect([$heroBgUrl]);
    }
@endphp
<section class="hero" id="homeHeroSection">
    <div class="hero__bg hero__bg--slider" id="heroBgSlider" @if($heroSlides->isEmpty() && !empty($heroBgUrl)) style="background-image:url({{ json_encode($heroBgUrl) }})" @endif>
        @foreach($heroSlides as $index => $slideUrl)
            <div
                class="hero__bg-slide {{ $index === 0 ? 'is-active' : '' }}"
                data-bg="{{ e($slideUrl) }}"
                @if($index === 0) data-priority="high" @endif
            ></div>
        @endforeach
    </div>
    <div class="hero__overlay hero__overlay--clear"></div>
    <div class="hero__inner">
        <div class="hero__body">
            <span class="hero__badge">
                <span class="hero__badge-dot"></span>
                {{ __('home.hero_badge') }}
            </span>
            <h1 class="hero__title">
                <span class="hero__title-line">{{ __('home.hero_title_1') }}</span>
                <span class="hero__title-line">{{ __('home.hero_title_2') }}</span>
                <span class="hero__title-line">{{ __('home.hero_title_3') }}</span>
            </h1>
            <div class="hero__actions">
                <a href="{{ route('hot-tubs') }}" class="btn btn--primary btn--pill btn--lg">
                    {{ __('home.hero_browse_hot_tubs') }}
                </a>
                <a href="{{ route('swim-spas') }}" class="btn btn--ghost btn--pill btn--lg">
                    {{ __('home.hero_explore_swim_spas') }}
                </a>
            </div>
        </div>
        @if($heroSlides->count() > 1)
            <button class="hero__nav-btn hero__nav-btn--prev" id="heroPrevBtn" type="button" aria-label="{{ __('home.hero_prev') }}">
                <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="hero__nav-btn hero__nav-btn--next" id="heroNextBtn" type="button" aria-label="{{ __('home.hero_next') }}">
                <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        @endif
    </div>
</section>

{{-- ═══════════════════════════════════════════
     TRUST BAR
════════════════════════════════════════════ --}}
<div class="trust-bar trust-bar--home">
    <div class="trust-bar__inner">
        <p class="trust-bar__text">{{ __('home.trust_text') }}</p>
        <div class="trust-bar__pills">
            <span class="trust-pill">
                <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"/>
                </svg>
                {{ __('home.trust_expert_reviews') }}
            </span>
            <span class="trust-pill">
                <svg viewBox="0 0 24 24" fill="none" width="13" height="13">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0Z" stroke="currentColor" stroke-width="2"/>
                </svg>
                {{ __('home.trust_verified_dealers') }}
            </span>
            <span class="trust-pill">
                <svg viewBox="0 0 24 24" fill="none" width="13" height="13">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                {{ __('home.trust_free_guides') }}
            </span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     PRODUCT OF THE MONTH
════════════════════════════════════════════ --}}
@if($potm)
<section class="potm home-animate home-defer">
    <div class="potm__grid">
        <div class="potm__text">
            <p class="potm__label">{{ __('home.potm_label') }}</p>
            <h2 class="potm__title">{{ $potm->title }}</h2>
            <p class="potm__desc">
                {{ $potm->description ?: ($potm->hotTub ? $potm->hotTub->description : __('home.potm_fallback_desc')) }}
            </p>
            @if($potm->hotTub)
            <a href="{{ route('hot-tubs.detail', $potm->hotTub->slug) }}" class="btn btn--outline btn--pill btn--sm">
                {{ __('home.view_product_details') }}
            </a>
            @endif
        </div>
        <div class="potm__img">
            @php
                $potmImg = null;
                if ($potm && $potm->image_url) {
                    $potmImg = \App\Support\PublicMedia::url($potm->image_url);
                }
                if (!$potmImg && $potm && $potm->hotTub) {
                    $rawImgs = $potm->hotTub->images;
                    if ($rawImgs instanceof \Illuminate\Support\Collection) {
                        $rawImgs = $rawImgs->all();
                    }
                    $imgs = is_array($rawImgs) ? $rawImgs : (is_string($rawImgs) ? (json_decode($rawImgs, true) ?: []) : []);
                    $imgs = array_values(array_filter(array_map(function ($v) {
                        if (is_string($v)) return $v;
                        if (is_array($v)) return $v['path'] ?? $v['url'] ?? $v['file'] ?? ($v[0] ?? null);
                        return null;
                    }, $imgs), fn ($v) => is_string($v) && $v !== ''));
                    if (count($imgs)) {
                        $potmImg = \App\Support\PublicMedia::url($imgs[0]);
                    }
                }
            @endphp
            <img src="{{ $potmImg ?: 'https://images.unsplash.com/photo-1584464479516-0c10b2d2eb1c?w=800' }}" alt="{{ $potm->title }}" loading="lazy">
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════
     WHY CHOOSE HOT TUB BUYER
════════════════════════════════════════════ --}}
<section class="why home-animate home-defer">
    <div class="container">
        <h2 class="section-title text-center">{{ __('home.why_title') }}</h2>
        <p class="section-subtitle text-center">{{ __('home.why_subtitle') }}</p>

        <div class="why__grid">
            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <h4>{{ __('home.why_expert_title') }}</h4>
                <p>{{ __('home.why_expert_desc') }}</p>
            </div>

            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
                <h4>{{ __('home.why_tier_title') }}</h4>
                <p>{{ __('home.why_tier_desc') }}</p>
            </div>

            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
                <h4>{{ __('home.why_quotes_title') }}</h4>
                <p>{{ __('home.why_quotes_desc') }}</p>
            </div>

            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
                <h4>{{ __('home.why_trusted_title') }}</h4>
                <p>{{ __('home.why_trusted_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     FEATURED HOT TUBS
════════════════════════════════════════════ --}}
<section class="featured home-animate home-defer">
    <div class="featured__heading text-center">
        <h2 class="featured__title">{{ __('home.featured_title') }}</h2>
        <p class="featured__sub">{{ __('home.featured_sub') }}</p>
    </div>

    <div class="featured__slider-container">
        <button class="featured__nav featured__nav--prev" id="featuredPrev">
            <svg viewBox="0 0 24 24" fill="none" width="20" height="20"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        
        <div class="featured__slider" id="featuredSlider">
            <div class="featured__track">
                @foreach($featuredHotTubs as $it)
                    @php
                        $rawImgs = $it->images;
                        if ($rawImgs instanceof \Illuminate\Support\Collection) {
                            $rawImgs = $rawImgs->all();
                        }
                        $imgs = is_array($rawImgs) ? $rawImgs : (is_string($rawImgs) ? (json_decode($rawImgs, true) ?: []) : []);
                        $imgs = array_values(array_filter(array_map(function ($v) {
                            if (is_string($v)) return $v;
                            if (is_array($v)) return $v['path'] ?? $v['url'] ?? $v['file'] ?? ($v[0] ?? null);
                            return null;
                        }, $imgs), fn ($v) => is_string($v) && $v !== ''));
                        $img = count($imgs)
                            ? (\App\Support\PublicMedia::url($imgs[0]) ?: 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=80&auto=format&fit=crop')
                            : 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=80&auto=format&fit=crop';

                        $featuredInfo = $it->featuredContents->first();
                        if ($featuredInfo && $featuredInfo->image_url) {
                            $img = \App\Support\PublicMedia::url($featuredInfo->image_url) ?: $img;
                        }
                        $badgeText = $featuredInfo ? $featuredInfo->title : __('home.top_rated');
                    @endphp
                    <div class="featured__slide">
                        @include('components.hot-tub-card', ['it' => $it])
                    </div>
                @endforeach
            </div>
        </div>

        <button class="featured__nav featured__nav--next" id="featuredNext">
            <svg viewBox="0 0 24 24" fill="none" width="20" height="20"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>

    <div class="featured__dots" id="featuredDots"></div>
</section>

{{-- ═══════════════════════════════════════════
     BRANDS
════════════════════════════════════════════ --}}
@php
    $brandSlides = collect($premiumBrands ?? [])->map(function ($b) {
        $name = (string) ($b->name ?? '');
        $mark = '';
        foreach (preg_split('/\s+/', trim($name)) as $word) {
            if ($word !== '') {
                $mark .= mb_substr($word, 0, 1);
            }
            if (mb_strlen($mark) >= 2) break;
        }
        $tag = $b->country_of_origin ? __('home.made_in', ['country' => $b->country_of_origin]) : __('home.premium_brand');
        return [
            'name' => $name,
            'tag' => $tag,
            'mark' => mb_strtoupper($mark ?: mb_substr($name, 0, 1)),
            'logo' => $b->logo_path ? \App\Support\PublicMedia::url($b->logo_path) : null,
            'slug' => $b->slug ?? null,
        ];
    });
    $brandLoop = $brandSlides->count() > 1 ? $brandSlides->concat($brandSlides) : $brandSlides;
@endphp
@if($brandSlides->isNotEmpty())
<section class="brands home-animate home-defer">
    <div class="container">
        <h2 class="section-title text-center">{{ __('home.brands_title') }}</h2>
        <p class="section-subtitle text-center">{{ __('home.brands_sub') }}</p>

        <div class="brands__slider" aria-label="{{ __('home.brands_aria') }}">
            <div class="brands__track">
                @foreach($brandLoop as $brand)
                    <a href="{{ !empty($brand['slug']) ? route('brands.detail', $brand['slug']) : route('brands') }}" class="brand-tile brand-tile--logo">
                        <span class="brand-tile__logo">
                            @if(!empty($brand['logo']))
                                <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" style="width:100%;height:100%;object-fit:contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;font-weight:800;font-size:1.1rem;">{{ $brand['mark'] }}</span>
                            @else
                                {{ $brand['mark'] }}
                            @endif
                        </span>
                        <span class="brand-tile__meta">
                            <span class="brand-tile__name">{{ $brand['name'] }}</span>
                            <span class="brand-tile__tag">{{ $brand['tag'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('brands') }}" class="btn btn--outline btn--pill">
                {{ __('home.explore_brands') }}
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════
     EXPERT GUIDES & RESOURCES
════════════════════════════════════════════ --}}
<section class="guides home-animate home-defer">
    <div class="guides__bg"></div>
    <div class="guides__overlay"></div>
    <div class="guides__inner">
        <div class="guides__heading">
            <h2>{{ __('home.guides_title') }}</h2>
            <p>{{ __('home.guides_sub') }}</p>
        </div>
        <div class="guides__cards">
            <a href="{{ route('care-guide') }}" class="guide-card">
                <div class="guide-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                        <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="guide-card__text">
                    <strong>{{ __('home.guide_care_title') }}</strong>
                    <span>{{ __('home.guide_care_desc') }}</span>
                </div>
                <span class="guide-card__arrow">→</span>
            </a>

            <a href="{{ route('faq') }}" class="guide-card">
                <div class="guide-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="12" cy="17" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="guide-card__text">
                    <strong>{{ __('home.guide_faq_title') }}</strong>
                    <span>{{ __('home.guide_faq_desc') }}</span>
                </div>
                <span class="guide-card__arrow">→</span>
            </a>

            <a href="{{ route('brands') }}" class="guide-card">
                <div class="guide-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                        <path d="M12 2L2 7l10 5 10-5-10-5ZM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="guide-card__text">
                    <strong>{{ __('home.guide_brands_title') }}</strong>
                    <span>{{ __('home.guide_brands_desc') }}</span>
                </div>
                <span class="guide-card__arrow">→</span>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     SUCCESS STORIES / STATS
════════════════════════════════════════════ --}}
<section class="stats home-animate home-defer">
    <div class="container">
        <h2 class="stats__title">{{ __('home.stats_title') }}</h2>
        <div class="stats__grid">
            <div class="stat-item">
                <span class="stat-item__number">50+</span>
                <div class="stat-item__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="22" height="22">
                        <circle cx="12" cy="12" r="3" stroke="white" stroke-width="1.8"/>
                        <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="stat-item__label">{{ __('home.stat_dealers') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-item__number">10,000+</span>
                <div class="stat-item__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="22" height="22">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="white" stroke-width="1.8"/>
                        <circle cx="9" cy="7" r="4" stroke="white" stroke-width="1.8"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="white" stroke-width="1.8"/>
                    </svg>
                </div>
                <span class="stat-item__label">{{ __('home.stat_customers') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-item__number">4.8</span>
                <div class="stat-item__icon">
                    <svg viewBox="0 0 24 24" fill="white" width="22" height="22">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"/>
                    </svg>
                </div>
                <span class="stat-item__label">{{ __('home.stat_rating') }}</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA
════════════════════════════════════════════ --}}
<section class="cta-home home-animate home-defer">
    <div class="cta-home__layout">
        <div class="cta-home__text">
            <h2>{{ __('home.cta_title') }}</h2>
            <p>{{ __('home.cta_desc') }}</p>
            <a href="{{ route('hot-tubs') }}" class="btn btn--outline btn--pill">
                {{ __('home.cta_button') }}
            </a>
        </div>
        <div class="cta-home__img">
            {{--
                REAL IMAGE: https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400
                Save as: public/images/cta-tub.png
            --}}
            <img
                src="{{ !empty($ctaImageUrl) ? e($ctaImageUrl) : asset('images/hot-tub-cta-fallback.svg') }}"
                alt="{{ __('home.cta_alt') }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/hot-tub-cta-fallback.svg') }}';"
                style="max-width:100%;height:auto;object-fit:contain;"
            >
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     DELIVERY OF THE WEEK (page bottom)
════════════════════════════════════════════ --}}
@if($dotw)
<section class="potm home-animate home-defer" style="margin-top:2rem;">
    <div class="potm__grid">
        <div class="potm__text">
            <p class="potm__label">{{ __('home.dotw_label') }}</p>
            <h2 class="potm__title">{{ $dotw->title }}</h2>
            <p class="potm__desc">{{ $dotw->description ?: ($dotw->hotTub?->description ?? __('home.dotw_fallback_desc')) }}</p>
            @if($dotw->hotTub)
            <a href="{{ route('hot-tubs.detail', $dotw->hotTub->slug) }}" class="btn btn--outline btn--pill btn--sm">{{ __('home.view_product') }}</a>
            @endif
        </div>
        <div class="potm__img">
            @php
                $dotwImg = $dotw->image_url ? \App\Support\PublicMedia::url($dotw->image_url) : null;
                if (!$dotwImg && $dotw->hotTub) {
                    $rawImgs = $dotw->hotTub->images;
                    if ($rawImgs instanceof \Illuminate\Support\Collection) $rawImgs = $rawImgs->all();
                    $imgs = is_array($rawImgs) ? $rawImgs : (is_string($rawImgs) ? (json_decode($rawImgs, true) ?: []) : []);
                    $imgs = array_values(array_filter(array_map(function ($v) {
                        if (is_string($v)) return $v;
                        if (is_array($v)) return $v['path'] ?? $v['url'] ?? null;
                        return null;
                    }, $imgs), fn ($v) => is_string($v) && $v !== ''));
                    if (count($imgs)) $dotwImg = \App\Support\PublicMedia::url($imgs[0]);
                }
            @endphp
            <img src="{{ $dotwImg ?: 'https://images.unsplash.com/photo-1584464479516-0c10b2d2eb1c?w=800' }}" alt="{{ $dotw->title }}" loading="lazy">
        </div>
    </div>
</section>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    (function initBrandsMouseScroll() {
        const slider = document.querySelector('.brands__slider');
        if (!slider) return;

        slider.addEventListener('wheel', function (event) {
            // Convert vertical wheel into smooth horizontal scrolling for premium brands strip.
            if (Math.abs(event.deltaY) > Math.abs(event.deltaX)) {
                event.preventDefault();
                slider.scrollLeft += event.deltaY;
            }
        }, { passive: false });
    })();

    (function initHeroCarousel() {
        const heroSlides = Array.from(document.querySelectorAll('#heroBgSlider .hero__bg-slide'));
        const heroPrevBtn = document.getElementById('heroPrevBtn');
        const heroNextBtn = document.getElementById('heroNextBtn');
        const HERO_AUTOPLAY_MS = 10000;
        let heroTimer = null;
        let heroIndex = 0;
        let isNavigatingAway = false;
        const activeHeroPreloads = new Set();

        function optimizeHeroImageUrl(rawUrl) {
            if (!rawUrl) return rawUrl;
            try {
                const parsed = new URL(rawUrl, window.location.origin);
                const host = parsed.hostname.toLowerCase();

                // Keep local uploads untouched (avoid breaking storage URLs).
                if (parsed.origin === window.location.origin) {
                    return parsed.href;
                }

                // Safe compression/resize for Unsplash assets.
                if (host.includes('unsplash.com')) {
                    if (!parsed.searchParams.has('auto')) parsed.searchParams.set('auto', 'format');
                    if (!parsed.searchParams.has('fit')) parsed.searchParams.set('fit', 'crop');
                    if (!parsed.searchParams.has('q')) parsed.searchParams.set('q', '74');
                    if (!parsed.searchParams.has('w')) parsed.searchParams.set('w', '1920');
                    if (!parsed.searchParams.has('dpr')) parsed.searchParams.set('dpr', String(Math.min(2, Math.max(1, window.devicePixelRatio || 1))));
                    return parsed.href;
                }

                return parsed.href;
            } catch (e) {
                return rawUrl;
            }
        }

        function loadHeroSlideBackground(slide) {
            if (!slide) return Promise.resolve(false);
            if (isNavigatingAway) return Promise.resolve(false);
            if (slide.dataset.bgLoaded === '1') return Promise.resolve(true);
            if (slide.dataset.bgLoading === '1') return Promise.resolve(false);

            const rawBgUrl = slide.dataset.bg || '';
            const bgUrl = optimizeHeroImageUrl(rawBgUrl);
            if (!bgUrl) return Promise.resolve(false);

            slide.dataset.bgLoading = '1';
            const preloader = new Image();
            preloader.decoding = 'async';
            activeHeroPreloads.add(preloader);

            return new Promise(function (resolve) {
                preloader.onload = function () {
                    activeHeroPreloads.delete(preloader);
                    if (isNavigatingAway) {
                        slide.dataset.bgLoading = '0';
                        resolve(false);
                        return;
                    }
                    slide.style.backgroundImage = 'url("' + bgUrl.replace(/"/g, '\\"') + '")';
                    slide.dataset.bgLoaded = '1';
                    slide.dataset.bgLoading = '0';
                    resolve(true);
                };
                preloader.onerror = function () {
                    activeHeroPreloads.delete(preloader);
                    slide.dataset.bgLoading = '0';
                    resolve(false);
                };
                preloader.src = bgUrl;
            });
        }

        function preloadHeroAround(index) {
            if (!heroSlides.length) return;
            loadHeroSlideBackground(heroSlides[index]);
            if (heroSlides.length > 1) {
                loadHeroSlideBackground(heroSlides[(index + 1) % heroSlides.length]);
            }
        }

        function showHeroSlide(targetIndex) {
            if (!heroSlides.length) return;
            heroSlides[heroIndex].classList.remove('is-active');
            heroIndex = (targetIndex + heroSlides.length) % heroSlides.length;
            heroSlides[heroIndex].classList.add('is-active');
            preloadHeroAround(heroIndex);
        }

        function clearHeroTimer() {
            if (heroTimer) {
                clearInterval(heroTimer);
                heroTimer = null;
            }
        }

        function pauseHeroForNavigation() {
            if (isNavigatingAway) return;
            isNavigatingAway = true;
            clearHeroTimer();
            activeHeroPreloads.forEach(function (img) {
                img.onload = null;
                img.onerror = null;
                img.src = '';
            });
            activeHeroPreloads.clear();
        }

        function startHeroAutoPlay() {
            clearHeroTimer();
            if (heroSlides.length <= 1) return;
            if (document.visibilityState !== 'visible') return;
            heroTimer = setInterval(function () {
                showHeroSlide(heroIndex + 1);
            }, HERO_AUTOPLAY_MS);
        }

        function resetHeroAutoPlay() {
            startHeroAutoPlay();
        }

        if (heroSlides.length > 1) {
            document.addEventListener('click', function (event) {
                const link = event.target.closest('a[href]');
                if (!link) return;
                if (link.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                try {
                    const nextUrl = new URL(link.href, window.location.origin);
                    if (nextUrl.origin !== window.location.origin) return;
                    if (nextUrl.pathname === window.location.pathname && nextUrl.search === window.location.search && nextUrl.hash) return;
                    pauseHeroForNavigation();
                } catch (e) {}
            }, { capture: true });

            window.addEventListener('pagehide', pauseHeroForNavigation);

            if (heroNextBtn) {
                heroNextBtn.addEventListener('click', function () {
                    showHeroSlide(heroIndex + 1);
                    resetHeroAutoPlay();
                });
            }
            if (heroPrevBtn) {
                heroPrevBtn.addEventListener('click', function () {
                    showHeroSlide(heroIndex - 1);
                    resetHeroAutoPlay();
                });
            }
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    startHeroAutoPlay();
                } else {
                    clearHeroTimer();
                }
            });
            preloadHeroAround(0);
            setTimeout(startHeroAutoPlay, 0);
        }
    })();

    (function initFeaturedSlider() {
        const slider = document.getElementById('featuredSlider');
        const track = slider ? slider.querySelector('.featured__track') : null;
        if (!slider || !track) return;

        const slides = Array.from(track.children);
        const nextBtn = document.getElementById('featuredNext');
        const prevBtn = document.getElementById('featuredPrev');
        const dotsContainer = document.getElementById('featuredDots');

        if (slides.length === 0) return;
        if (!nextBtn || !prevBtn || !dotsContainer) return;

        let index = 0;
        const gap = 24;

        function updateSlider() {
            const containerWidth = slider.offsetWidth;
            const trackWidth = track.scrollWidth;
            const slideWidth = slides[0].offsetWidth;

            const itemsInView = Math.floor((containerWidth + gap) / (slideWidth + gap));
            const maxIndex = Math.max(0, slides.length - itemsInView);

            if (index > maxIndex) index = maxIndex;

            if (trackWidth <= containerWidth) {
                track.style.transform = 'translateX(0)';
                track.style.justifyContent = 'center';
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                dotsContainer.style.display = 'none';
                return;
            }

            track.style.justifyContent = 'flex-start';
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
            dotsContainer.style.display = 'flex';

            const offset = index * (slideWidth + gap);
            track.style.transform = 'translateX(-' + offset + 'px)';

            updateControls(maxIndex);
        }

        function updateControls(maxIndex) {
            prevBtn.disabled = index === 0;
            nextBtn.disabled = index >= maxIndex;

            dotsContainer.innerHTML = '';
            const numDots = maxIndex + 1;
            if (numDots > 1) {
                for (let i = 0; i < numDots; i++) {
                    const dot = document.createElement('div');
                    dot.classList.add('featured__dot');
                    if (i === index) dot.classList.add('active');
                    dot.onclick = function () {
                        index = i;
                        updateSlider();
                    };
                    dotsContainer.appendChild(dot);
                }
            }
        }

        nextBtn.onclick = function () {
            index++;
            updateSlider();
        };

        prevBtn.onclick = function () {
            index--;
            updateSlider();
        };

        let startX, moveX, isDragging = false;
        slider.ontouchstart = function (e) {
            startX = e.touches[0].clientX;
            isDragging = true;
        };
        slider.ontouchmove = function (e) {
            if (!isDragging) return;
            moveX = e.touches[0].clientX;
        };
        slider.ontouchend = function () {
            if (!isDragging || moveX === undefined) return;
            const diff = startX - moveX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) nextBtn.click();
                else prevBtn.click();
            }
            isDragging = false;
            moveX = undefined;
        };

        window.addEventListener('resize', updateSlider);

        setTimeout(updateSlider, 100);
    })();

    (function initHomeScrollReveal() {
        var blocks = document.querySelectorAll('.home-animate');
        if (!blocks.length) return;

        function markInView(el) {
            el.classList.add('home-in-view');
        }

        function revealNow() {
            var vh = window.innerHeight || document.documentElement.clientHeight;
            blocks.forEach(function (el) {
                if (el.classList.contains('home-in-view')) return;
                var r = el.getBoundingClientRect();
                if (r.top < vh * 0.92 && r.bottom > 0) {
                    markInView(el);
                }
            });
        }

        revealNow();

        if (!window.IntersectionObserver) {
            blocks.forEach(markInView);
            return;
        }

        var io = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        markInView(entry.target);
                        io.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.08, rootMargin: '0px 0px -24px 0px' }
        );

        blocks.forEach(function (el) {
            if (!el.classList.contains('home-in-view')) {
                io.observe(el);
            }
        });
    })();
});
</script>
@endsection
