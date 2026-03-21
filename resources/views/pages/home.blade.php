@extends('layouts.app')
@section('title', 'Hot Tub Buyer - Expert Reviews & Guides')
@section('meta_description', 'Find your perfect hot tub. Expert reviews, verified dealers, price comparisons and comprehensive guides.')

@section('content')

{{-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ --}}
<section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__overlay"></div>
    <div class="hero__inner">
        <div class="hero__body">
            <span class="hero__badge">
                <span class="hero__badge-dot"></span>
                Expert Reviews &amp; Verified Dealers
            </span>
            <h1 class="hero__title">
                Find Your Perfect<br>
                Hot Tub Or Wellness<br>
                Products
            </h1>
            <div class="hero__actions">
                <a href="{{ route('hot-tubs') }}" class="btn btn--primary btn--pill btn--lg">
                    Browse Hot Tubs
                </a>
                <a href="{{ route('swim-spas') }}" class="btn btn--ghost btn--pill btn--lg">
                    Explore Swim Spas
                </a>
            </div>
        </div>
        <button class="hero__scroll-btn" aria-label="Scroll down" onclick="window.scrollBy({top:500,behavior:'smooth'})">
            <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     TRUST BAR
════════════════════════════════════════════ --}}
<div class="trust-bar">
    <div class="trust-bar__inner">
        <p class="trust-bar__text">
            Expert Reviews, Comprehensive Guides, And Competitive Quotes From Trusted UK Dealers.
            Compare All Major Brands And Models In One Place.
        </p>
        <div class="trust-bar__pills">
            <span class="trust-pill">
                <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"/>
                </svg>
                Expert Reviews
            </span>
            <span class="trust-pill">
                <svg viewBox="0 0 24 24" fill="none" width="13" height="13">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0Z" stroke="currentColor" stroke-width="2"/>
                </svg>
                Verified Dealer Network
            </span>
            <span class="trust-pill">
                <svg viewBox="0 0 24 24" fill="none" width="13" height="13">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Free Expert Guides
            </span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     PRODUCT OF THE MONTH
════════════════════════════════════════════ --}}
@if($potm)
<section class="potm">
    <div class="potm__grid">
        <div class="potm__text">
            <p class="potm__label">Product Of The Month</p>
            <h2 class="potm__title">{{ $potm->title }}</h2>
            <p class="potm__desc">
                {{ $potm->hotTub ? $potm->hotTub->description : 'Expertly reviewed for performance, luxury, and lasting quality.' }}
            </p>
            @if($potm->hotTub)
            <a href="{{ route('hot-tubs.detail', $potm->hotTub->slug) }}" class="btn btn--outline btn--pill btn--sm">
                View Product Details
            </a>
            @endif
        </div>
        <div class="potm__img">
            @php
                $potmImg = $potm->image_url;
                if (!$potmImg && $potm->hotTub && count($potm->hotTub->images ?? [])) {
                    $potmImg = asset('storage/' . $potm->hotTub->images[0]);
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
<section class="why">
    <div class="container">
        <h2 class="section-title text-center">Why Choose Hot Tub Buyer?</h2>
        <p class="section-subtitle text-center">Your Complete Resource For Hot Tub Research And Purchasing</p>

        <div class="why__grid">
            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <h4>Expert Reviews</h4>
                <p>In-depth evaluations with detailed scoring across 8 key metrics including jets, seats, features, energy efficiency and build quality.</p>
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
                <h4>Tiered System</h4>
                <p>Clear categorisation by budget, size and purpose. Comparing entry-level, mid-range and premium models fairly and accurately.</p>
            </div>

            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
                <h4>Get Quotes</h4>
                <p>Receive competitive quotes from certified local dealers. Compare prices, features and installation packages hassle-free.</p>
            </div>

            <div class="why-card">
                <div class="why-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </div>
                <h4>Trusted Network</h4>
                <p>Only approved dealers within our verified customer network. We add only quality dealers that you can trust completely.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     FEATURED HOT TUBS
════════════════════════════════════════════ --}}
<section class="featured">
    <div class="featured__heading text-center">
        <h2 class="featured__title">Featured Hot Tubs</h2>
        <p class="featured__sub">Top-Rated Models Across All Price Ranges</p>
    </div>

    <div class="featured__slider-container">
        <button class="featured__nav featured__nav--prev" id="featuredPrev">
            <svg viewBox="0 0 24 24" fill="none" width="20" height="20"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        
        <div class="featured__slider" id="featuredSlider">
            <div class="featured__track">
                @foreach($featuredHotTubs as $it)
                    @php
                        $imgs = is_array($it->images) ? $it->images : [];
                        $img = count($imgs) ? asset('storage/' . $imgs[0]) : 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=80&auto=format&fit=crop';
                        
                        $featuredInfo = $it->featuredContents->first();
                        if ($featuredInfo && $featuredInfo->image_url) {
                            $img = $featuredInfo->image_url;
                        }
                        $badgeText = $featuredInfo ? $featuredInfo->title : 'Top Rated';
                    @endphp
                    <div class="featured__slide">
                        <div class="tub-card">
                            <div class="tub-card__img">
                                <img src="{{ $img }}" alt="{{ $it->brand }} {{ $it->model }}" loading="lazy">
                                <span class="tub-card__badge {{ $loop->index % 2 == 0 ? 'tub-card__badge--dark' : 'tub-card__badge--teal' }}">{{ $badgeText }}</span>
                            </div>
                            <div class="tub-card__body">
                                <p class="tub-card__name">{{ $it->model }}</p>
                                <p class="tub-card__brand">{{ $it->brand }}</p>
                                <div class="tub-card__tags">
                                    <span class="tub-card__tag">{{ $it->seats ?? '—' }} Person</span>
                                    <span class="tub-card__tag">{{ $it->jets ?? '—' }} Jets</span>
                                </div>
                                <div class="tub-card__footer">
                                    <a href="{{ route('hot-tubs.detail', $it->slug) }}" class="btn btn--primary btn--sm">View Details</a>
                                    <span class="tub-card__rating">⭐ {{ number_format($it->overall ?? 0, 1) }}</span>
                                </div>
                            </div>
                        </div>
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
<section class="brands">
    <div class="container">
        <h2 class="section-title text-center">Premium Brands We Feature</h2>
        <p class="section-subtitle text-center">Industry Leaders In Hot Tub Innovation And Quality</p>

        <div class="brands__grid">
            <a href="{{ route('brands') }}" class="brand-tile">
                <span class="brand-tile__name">Jacuzzi</span>
                <span class="brand-tile__tag">Iconic Technology</span>
            </a>
            <a href="{{ route('brands') }}" class="brand-tile">
                <span class="brand-tile__name">Hot Spring</span>
                <span class="brand-tile__tag">Premium Brand</span>
            </a>
            <a href="{{ route('brands') }}" class="brand-tile">
                <span class="brand-tile__name">Sundance</span>
                <span class="brand-tile__tag">Made in USA</span>
            </a>
            <a href="{{ route('brands') }}" class="brand-tile">
                <span class="brand-tile__name">Marquis</span>
                <span class="brand-tile__tag">Luxury Choice</span>
            </a>
            <a href="{{ route('brands') }}" class="brand-tile">
                <span class="brand-tile__name">Caldera</span>
                <span class="brand-tile__tag">Alto Designs</span>
            </a>
            <a href="{{ route('brands') }}" class="brand-tile">
                <span class="brand-tile__name">Bullfrog</span>
                <span class="brand-tile__tag">JetPak System</span>
            </a>
        </div>

        <div class="text-center">
            <a href="{{ route('brands') }}" class="btn btn--outline btn--pill">
                Explore All Brands &amp; Technologies
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     EXPERT GUIDES & RESOURCES
════════════════════════════════════════════ --}}
<section class="guides">
    <div class="guides__bg"></div>
    <div class="guides__overlay"></div>
    <div class="guides__inner">
        <div class="guides__heading">
            <h2>Expert Guides &amp; Resources</h2>
            <p>Everything You Need To Know About Hot Tub Ownership</p>
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
                    <strong>Complete Care Guide</strong>
                    <span>Daily, Weekly &amp; Monthly Maintenance Schedules. Learn The 5-Step Hot Tub Care Protocol Step By Step.</span>
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
                    <strong>FAQ</strong>
                    <span>Answers To Your Most Common Hot Tub Questions. Installation, Running Costs And Health Benefits.</span>
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
                    <strong>Brand Technologies</strong>
                    <span>Discover Proprietary Technologies From Each Approved Brand. Filtration, Heating, Jet And Sanitation Systems.</span>
                </div>
                <span class="guide-card__arrow">→</span>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     SUCCESS STORIES / STATS
════════════════════════════════════════════ --}}
<section class="stats">
    <div class="container">
        <h2 class="stats__title">Our Success Stories</h2>
        <div class="stats__grid">
            <div class="stat-item">
                <span class="stat-item__number">50+</span>
                <div class="stat-item__icon">
                    <svg viewBox="0 0 24 24" fill="none" width="22" height="22">
                        <circle cx="12" cy="12" r="3" stroke="white" stroke-width="1.8"/>
                        <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="stat-item__label">Verified Dealers</span>
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
                <span class="stat-item__label">Happy Customers</span>
            </div>
            <div class="stat-item">
                <span class="stat-item__number">4.8</span>
                <div class="stat-item__icon">
                    <svg viewBox="0 0 24 24" fill="white" width="22" height="22">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"/>
                    </svg>
                </div>
                <span class="stat-item__label">Average Rating</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA
════════════════════════════════════════════ --}}
<section class="cta-home">
    <div class="cta-home__layout">
        <div class="cta-home__text">
            <h2>Ready To Find Your Perfect Hot Tub?</h2>
            <p>Get Free Expert Guides From Local Approved Dealers Today. Compare Prices, Features, And Installation Packages.</p>
            <a href="{{ route('hot-tubs') }}" class="btn btn--outline btn--pill">
                Start Your Search Now
            </a>
        </div>
        <div class="cta-home__img">
            {{--
                REAL IMAGE: https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400
                Save as: public/images/cta-tub.png
            --}}
            <img
                src="/images/2254d30420731f5659a4b5d60e9edddc84f75915.png"
                alt="Hot Tub"
                loading="lazy"
            >
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('featuredSlider');
    const track = slider.querySelector('.featured__track');
    const slides = Array.from(track.children);
    const nextBtn = document.getElementById('featuredNext');
    const prevBtn = document.getElementById('featuredPrev');
    const dotsContainer = document.getElementById('featuredDots');
    
    if (slides.length === 0) return;

    let index = 0;
    const gap = 24; // 1.5rem in pixels

    function updateSlider() {
        const containerWidth = slider.offsetWidth;
        const trackWidth = track.scrollWidth;
        const slideWidth = slides[0].offsetWidth;
        
        // Number of items that can fully fit in the container
        const itemsInView = Math.floor((containerWidth + gap) / (slideWidth + gap));
        const maxIndex = Math.max(0, slides.length - itemsInView);

        // Adjust index if it exceeds maxIndex
        if (index > maxIndex) index = maxIndex;

        // If track is smaller than container, center it and hide controls
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
        track.style.transform = `translateX(-${offset}px)`;

        updateControls(maxIndex);
    }

    function updateControls(maxIndex) {
        prevBtn.disabled = index === 0;
        nextBtn.disabled = index >= maxIndex;
        
        // Update dots
        dotsContainer.innerHTML = '';
        const numDots = maxIndex + 1;
        if (numDots > 1) {
            for (let i = 0; i < numDots; i++) {
                const dot = document.createElement('div');
                dot.classList.add('featured__dot');
                if (i === index) dot.classList.add('active');
                dot.onclick = () => {
                    index = i;
                    updateSlider();
                };
                dotsContainer.appendChild(dot);
            }
        }
    }

    nextBtn.onclick = () => {
        index++;
        updateSlider();
    };

    prevBtn.onclick = () => {
        index--;
        updateSlider();
    };

    // Touch support
    let startX, moveX, isDragging = false;
    slider.ontouchstart = (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
    };
    slider.ontouchmove = (e) => {
        if (!isDragging) return;
        moveX = e.touches[0].clientX;
    };
    slider.ontouchend = () => {
        if (!isDragging || moveX === undefined) return;
        const diff = startX - moveX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextBtn.click();
            else prevBtn.click();
        }
        isDragging = false;
        moveX = undefined;
    };

    window.onresize = updateSlider;
    
    // Initial call
    setTimeout(updateSlider, 100);
});
</script>
@endsection