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
        <div class="ht-filters" id="brandFilters">
            <div class="ht-filter-group">
                <label class="ht-filter-label">Tier</label>
                <select class="ht-filter-select" id="filterTier">
                    <option value="">All Tiers</option>
                    <option value="luxury">Luxury</option>
                    <option value="premium">Premium</option>
                    <option value="mid">Mid-Range</option>
                    <option value="entry">Entry Level</option>
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">Origin</label>
                <select class="ht-filter-select" id="filterOrigin">
                    <option value="">All Countries</option>
                    <option value="USA">USA</option>
                    <option value="Canada">Canada</option>
                    <option value="UK">UK</option>
                    <option value="Italy">Italy</option>
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">Best For</label>
                <select class="ht-filter-select" id="filterBest">
                    <option value="">Any Use</option>
                    <option value="therapy">Hydrotherapy</option>
                    <option value="family">Families</option>
                    <option value="fitness">Fitness</option>
                    <option value="cold">Cold Climates</option>
                    <option value="value">Best Value</option>
                </select>
            </div>
            <div class="ht-filter-results" id="brandCount">Showing 15 brands</div>
        </div>
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
            <div class="brand-card">
                <a href="{{ $brandLink }}" class="brand-card__header" style="background:#0d9488;display:flex;align-items:center;justify-content:center;text-decoration:none;color:inherit;overflow:hidden">
                    @if($b->logo_path)
                        <img src="{{ asset('storage/'.$b->logo_path) }}" alt="{{ $b->name }}" style="width:100%;height:100%;object-fit:contain;background:white;padding:10px">
                    @else
                        <div class="brand-card__initials">{{ strtoupper(substr($b->name,0,1)) }}</div>
                    @endif
                    @if($b->featured)
                    <span class="brand-card__tier-badge" style="background:#fff8e6;color:#a06200;">FEATURED</span>
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
                    <p class="brand-card__short" style="max-height:3.6em;overflow:hidden">{{ $desc }}</p>
                    @endif
                    <div class="brand-card__meta" style="margin-top:.4rem">
                        <span class="brand-card__meta-item">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $hotCount }} hot tubs
                        </span>
                        <span class="brand-card__meta-item">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            {{ $swimCount }} swim spas
                        </span>
                    </div>
                    <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                        <a href="{{ $brandLink }}" class="btn btn--sm" style="flex:1;">View {{ $b->name }} Hot Tubs →</a>
                        <button class="parts-enquire-btn" style="flex:1;padding:.5rem;font-size:.85rem;border-radius:var(--r-sm);" onclick="window.__openEnquiryModal({ title: 'Get a Quote — {{ addslashes($b->name) }}', subtitle: 'Request pricing from authorised {{ addslashes($b->name) }} dealers.' })">Get Quote</button>
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
            <button class="btn btn--outline btn--pill" onclick="resetBrandFilters()">Clear Filters</button>
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

{{-- ══ BRAND DETAIL MODAL ═══════════════════════════════════════════════════ --}}
<div class="ht-modal-overlay" id="brandModalOverlay" style="display:none;" onclick="closeBrandModal(event)">
    <div class="ht-detail-modal" style="max-width:680px;" id="brandModalContent">
        <button class="ht-modal__close" onclick="closeBrandModal()">&times;</button>
        <div id="brandModalBody"></div>
    </div>
</div>

{{-- ══ QUOTE MODAL ══════════════════════════════════════════════════════════ --}}
<div class="ht-modal-overlay" id="brandQuoteOverlay" style="display:none;" onclick="closeBrandQuote(event)">
    <div class="ht-quote-modal" style="max-width:540px;">
        <button class="ht-modal__close" onclick="closeBrandQuote()">&times;</button>
        <h2 class="ht-quote-modal__title" id="brandQuoteTitle">Get a Quote</h2>
        <p class="ht-quote-modal__sub" id="brandQuoteSub">Request pricing from authorised dealers near you.</p>
        <div class="form-group">
            <label class="form-label">Name *</label>
            <input class="form-input" type="text" id="bqName" placeholder="Jane Smith">
        </div>
        <div class="form-group">
            <label class="form-label">Email *</label>
            <input class="form-input" type="email" id="bqEmail" placeholder="jane@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Phone *</label>
            <input class="form-input" type="tel" id="bqPhone" placeholder="+44 7700 000000">
        </div>
        <div class="form-group">
            <label class="form-label">Postcode *</label>
            <input class="form-input" type="text" id="bqPostcode" placeholder="SW1A 1AA">
        </div>
        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-input" rows="3" id="bqNotes" placeholder="Model you're interested in, budget, etc." style="resize:vertical;"></textarea>
        </div>
        <div class="ht-quote__terms">
            <input type="checkbox" id="bqTerms" style="margin-top:2px;accent-color:var(--teal);flex-shrink:0;width:16px;height:16px;cursor:pointer;">
            <label for="bqTerms" style="font-size:.85rem;color:var(--gray-700);">I agree to the <a href="#" style="color:var(--teal);font-weight:600;text-decoration:underline;">Terms &amp; Conditions</a> and consent to Hot Tub Buyer processing my personal data. *</label>
        </div>
        <button class="svc-request-btn" style="width:100%;padding:.85rem;font-size:1rem;justify-content:center;" onclick="submitBrandQuote()">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5A16 16 0 0 0 16 16.59l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 24 17z"/></svg>
            Send Quote Request
        </button>
        <p style="margin-top: 1rem; font-size: 0.82rem; color: #6b7280; text-align: center; line-height: 1.4;">
            Buying a hot tub is exciting. Our platform connects you with trusted dealers who will support you from purchase to installation and long-term ownership.
        </p>
    </div>
</div>

{{-- ══ SUCCESS MODAL ════════════════════════════════════════════════════════ --}}
<div class="ht-modal-overlay" id="brandSuccessOverlay" style="display:none;">
    <div class="ht-success-modal">
        <button class="ht-modal__close" onclick="closeBrandSuccess()" style="position:absolute;top:1.25rem;right:1.25rem;">&times;</button>
        <div class="ht-success__icon">✅</div>
        <h2>Quote Request Sent!</h2>
        <p>We've forwarded your request to authorised dealers. Expect a response within 2 business hours.</p>
        <button class="svc-request-btn" style="width:100%;padding:.85rem;font-size:1rem;justify-content:center;" onclick="closeBrandSuccess()">Done</button>
    </div>
</div>

{{-- ══ JAVASCRIPT ══════════════════════════════════════════════════════════ --}}
<script>
/* Static dataset removed: server now renders brand cards */
    {
        id: 1, name: "Jacuzzi", slug: "jacuzzi",
        tier: "luxury", origin: "USA", best: ["therapy","family"],
        founded: "1956", priceRange: "£8,000 – £25,000",
        tagline: "The Original. The Icon.",
        accentColor: "#0a6c7a",
        tags: ["Luxury", "USA", "Hydrotherapy"],
        short: "The brand that invented the whirlpool bath. Jacuzzi remains the benchmark for quality, innovation, and hydrotherapy performance.",
        desc: "Jacuzzi® invented the whirlpool bath in 1956 and has spent nearly seven decades perfecting the art of hydrotherapy. Their PowerPro® jets, CLEARRAY® UV-C sanitation, and SmartTub® app integration set the standard across the industry. Every Jacuzzi hot tub is engineered in California and undergoes rigorous quality testing before leaving the factory.",
        strengths: ["Industry-leading PowerPro® jets", "CLEARRAY® UV-C water care", "SmartTub® WiFi connectivity", "Widest product range available", "Strong UK dealer network", "Excellent resale value"],
        warranty: "5-year shell, 2-year equipment",
        models: ["J-175", "J-235", "J-345", "J-495", "J-LXL"],
        rating: 4.8, reviews: 1240,
    },
    {
        id: 2, name: "Hot Spring Spas", slug: "hot-spring",
        tier: "luxury", origin: "USA", best: ["therapy","family","cold"],
        founded: "1977", priceRange: "£7,500 – £22,000",
        tagline: "World's Best-Selling Hot Tub Brand",
        accentColor: "#c25900",
        tags: ["Luxury", "USA", "Energy Efficient"],
        short: "Consistently ranked the world's best-selling hot tub brand, Hot Spring Spas are renowned for their exceptional energy efficiency and Moto-Massage® DX jets.",
        desc: "Hot Spring Spas has held the title of world's best-selling hot tub brand for decades. Built in Vista, California, their hot tubs are engineered to use up to 45% less energy than comparable models, making them one of the most cost-effective premium choices. The iconic Moto-Massage® DX jets deliver a sweeping full-back massage found nowhere else.",
        strengths: ["Moto-Massage® DX sweeping jets", "Industry-leading energy efficiency", "No-fault® 50-year shell warranty", "ACE® salt water system available", "Largest global dealer network", "FreshWater® Salt System"],
        warranty: "No-Fault® 50-year shell, 5-year equipment",
        models: ["Jetsetter", "Highlife Envoy", "Highlife Aria", "Limelight Beam", "Limelight Flair"],
        rating: 4.9, reviews: 2180,
    },
    {
        id: 3, name: "Hydropool", slug: "hydropool",
        tier: "premium", origin: "Canada", best: ["therapy","family","fitness"],
        founded: "1970", priceRange: "£5,500 – £18,000",
        tagline: "Self-Cleaning. Simply Better.",
        accentColor: "#1565c0",
        tags: ["Premium", "Canada", "Self-Cleaning"],
        short: "Hydropool pioneered the self-cleaning hot tub, offering 100% water filtration every 15 minutes. Outstanding build quality from a Canadian manufacturer with 50+ years of experience.",
        desc: "Founded in Canada in 1970, Hydropool has over 50 years of experience crafting premium hot tubs and swim spas. Their patented self-cleaning technology filters 100% of the water every 15 minutes, dramatically reducing chemical usage and maintenance time. Hydropool's FiberCor insulation system and EcoSmart energy management make them one of the most efficient brands available.",
        strengths: ["Patented 100% self-cleaning system", "FiberCor full-foam insulation", "EcoSmart energy management", "Outstanding swim spa range", "50+ years manufacturing experience", "Excellent cold-weather performance"],
        warranty: "Lifetime shell, 7-year plumbing, 5-year equipment",
        models: ["Serenity 4500", "Serenity 6000", "Serenity 8000", "AquaTrainer 14", "AquaSport 16"],
        rating: 4.7, reviews: 834,
    },
    {
        id: 4, name: "Arctic Spas", slug: "arctic-spas",
        tier: "premium", origin: "Canada", best: ["cold","family","therapy"],
        founded: "1994", priceRange: "£6,000 – £20,000",
        tagline: "Built for Extremes.",
        accentColor: "#1a237e",
        tags: ["Premium", "Canada", "Cold Climate"],
        short: "Arctic Spas are engineered for the harshest climates on earth, with full-foam insulation rated to -40°C. The undisputed choice for cold-climate performance.",
        desc: "Arctic Spas are manufactured in Thorsby, Alberta, Canada — one of the coldest regions on the planet. This heritage means every hot tub is engineered to perform flawlessly in extreme cold, with their Arctic Shield™ full-foam insulation system rated to -40°C. Despite their extreme durability, Arctic Spas are remarkably stylish and feature advanced technology including WiFi connectivity and ozone sanitation.",
        strengths: ["Arctic Shield™ full-foam insulation", "Rated to -40°C performance", "WiFi app connectivity", "20-year structural warranty", "Built-in freeze protection", "Excellent running costs in cold weather"],
        warranty: "20-year shell structure, 5-year equipment",
        models: ["Cub", "Wolf", "Bear", "Yukon", "Tundra"],
        rating: 4.6, reviews: 612,
    },
    {
        id: 5, name: "Sundance Spas", slug: "sundance",
        tier: "premium", origin: "USA", best: ["therapy","family"],
        founded: "1979", priceRange: "£5,000 – £16,000",
        tagline: "Innovation in Every Jet.",
        accentColor: "#e65100",
        tags: ["Premium", "USA", "Innovation"],
        short: "Sundance Spas has been innovating in hydrotherapy since 1979. Their MicroSilk® oxygen therapy and ClearRay® UV water treatment are genuinely industry-leading technologies.",
        desc: "Sundance Spas has been a pioneer in hot tub technology since 1979. They were among the first to introduce interchangeable jet faces, allowing bathers to customise their massage experience. Today, their MicroSilk® technology infuses billions of ultra-fine bubbles into the water, delivering a unique oxygen-rich therapy that softens skin and promotes relaxation. ClearRay® UV water treatment reduces chemical usage by up to 50%.",
        strengths: ["MicroSilk® oxygen therapy", "ClearRay® UV water treatment", "Fluidix® jet technology", "iQPump01® variable speed pump", "Customisable jet configurations", "Strong UK warranty support"],
        warranty: "5-year shell, 3-year equipment",
        models: ["Cameo 880", "Optima 880", "Maxxus 880", "Altamar 880", "Chelsee 680"],
        rating: 4.6, reviews: 729,
    },
    {
        id: 6, name: "Master Spas", slug: "master-spas",
        tier: "premium", origin: "USA", best: ["fitness","family","therapy"],
        founded: "1995", priceRange: "£4,500 – £20,000",
        tagline: "World's Largest Swim Spa Manufacturer.",
        accentColor: "#00695c",
        tags: ["Premium", "USA", "Swim Spas"],
        short: "Master Spas is the world's largest manufacturer of swim spas, producing outstanding fitness-focused products. Their H2X Trainer series is the benchmark for aquatic exercise.",
        desc: "Founded in Fort Wayne, Indiana in 1995, Master Spas has grown into one of the world's largest hot tub and swim spa manufacturers. Their H2X Trainer swim spa series is the world's best-selling swim spa range, combining powerful adjustable swim currents with full hydrotherapy seating. Master Spas also offers the Michael Phelps Signature series, designed in collaboration with the most decorated Olympian of all time.",
        strengths: ["World's largest swim spa manufacturer", "Michael Phelps Signature series", "H2X Trainer — best-selling swim spa", "EcoPure™ water management", "Variable-speed swim current", "Excellent value at each price point"],
        warranty: "Limited lifetime shell, 5-year plumbing, 2-year equipment",
        models: ["H2X Trainer 12", "H2X Trainer 15D", "MP Momentum Deep", "Legend Series", "Twilight Series"],
        rating: 4.5, reviews: 918,
    },
    {
        id: 7, name: "Caldera Spas", slug: "caldera",
        tier: "premium", origin: "USA", best: ["therapy","family"],
        founded: "1976", priceRange: "£5,500 – £15,000",
        tagline: "Pure Therapeutic Performance.",
        accentColor: "#4a148c",
        tags: ["Premium", "USA", "Therapeutic"],
        short: "Caldera Spas has focused purely on hydrotherapy performance since 1976. Their Utopia and Paradise series are beloved for their targeted, therapeutic jet systems.",
        desc: "Part of the Watkins Wellness family (which also includes Hot Spring Spas), Caldera Spas has built its reputation on pure hydrotherapy performance since 1976. The Utopia and Paradise series feature highly targeted jet systems designed to address specific muscle groups, while the Niagara Cascading Waterfall adds a soothing audio-visual element. FreshWater® Ag+ silver ion technology keeps water crystal clear with minimal chemical intervention.",
        strengths: ["Highly targeted therapy jets", "FreshWater® Ag+ silver ion system", "Niagara cascading waterfall", "Part of Watkins Wellness group", "Energy Star qualified models", "Strong UK dealer network"],
        warranty: "Lifetime shell, 5-year equipment",
        models: ["Utopia Capitola", "Utopia Geneva", "Paradise Kauai", "Paradise Makena", "Vacanza Martinique"],
        rating: 4.6, reviews: 541,
    },
    {
        id: 8, name: "Endless Pools", slug: "endless-pools",
        tier: "luxury", origin: "USA", best: ["fitness","therapy"],
        founded: "1988", priceRange: "£20,000 – £60,000",
        tagline: "The Original Swim-in-Place Pool.",
        accentColor: "#006064",
        tags: ["Luxury", "USA", "Swim & Fitness"],
        short: "Endless Pools invented the concept of swimming in place with their patented Fastlane® current system. The definitive choice for serious swimmers who want a compact, year-round solution.",
        desc: "Endless Pools pioneered the swim-in-place concept in 1988 and remains the undisputed leader. Their patented Fastlane® propeller-driven current system delivers a smooth, turbulence-free swim experience that no jet-based system can match. Available as standalone units or fully integrated into swim spas, Endless Pools are the first choice for serious swimmers, triathletes, and aquatic therapists worldwide.",
        strengths: ["Patented Fastlane® propeller current", "Turbulence-free swimming experience", "Digital current speed control", "Optional underwater treadmill", "Full colour touchscreen control", "Compact year-round indoor/outdoor use"],
        warranty: "10-year shell, 5-year equipment",
        models: ["E-Series E500", "E-Series E700", "R-Series R200", "Fitness Systems", "Performance Series"],
        rating: 4.8, reviews: 387,
    },
    {
        id: 9, name: "Passion Spas", slug: "passion-spas",
        tier: "mid", origin: "Italy", best: ["family","value"],
        founded: "2010", priceRange: "£3,500 – £10,000",
        tagline: "European Design. Outstanding Value.",
        accentColor: "#bf360c",
        tags: ["Mid-Range", "Italy", "Value"],
        short: "Passion Spas from Italy deliver exceptional European design and build quality at a very competitive price point, making premium hot tub ownership accessible to more families.",
        desc: "Passion Spas are manufactured in Italy and combine European design sensibilities with solid build quality at accessible price points. Their range spans compact 3-person tubs through to large 8-person family models, all featuring stainless steel jets, ozone sanitation, and LED lighting as standard. Passion Spas have gained a strong UK following thanks to their attractive aesthetics and straightforward maintenance.",
        strengths: ["European design and aesthetics", "Stainless steel jets as standard", "Ozone sanitation included", "Good range from 3–8 persons", "Competitive pricing", "Clean, modern cabinet options"],
        warranty: "5-year shell, 2-year equipment",
        models: ["Charm", "Create", "Explore", "Fame", "Flash Plus"],
        rating: 4.3, reviews: 312,
    },
    {
        id: 10, name: "Marquis Spas", slug: "marquis",
        tier: "premium", origin: "USA", best: ["therapy","family"],
        founded: "1980", priceRange: "£5,000 – £14,000",
        tagline: "Engineered for Life.",
        accentColor: "#1b5e20",
        tags: ["Premium", "USA", "Durable"],
        short: "Marquis Spas builds exceptionally durable hot tubs in Oregon, USA. Their Vector21™ jet system delivers precision therapeutic massage, and they back it with industry-leading warranties.",
        desc: "Family-owned and operated in Independence, Oregon since 1980, Marquis Spas is renowned for building some of the most durable hot tubs in the industry. Their exclusive Vector21™ jet system features 21 points of targeted adjustment, delivering a personalised therapeutic massage unlike any other. Every Marquis hot tub is pressure-tested and fully operational before leaving the factory.",
        strengths: ["Vector21™ 21-point adjustable jets", "Factory pressure-tested before shipping", "Family-owned Oregon manufacturing", "PERC™ recycled insulation system", "MyMarcuis™ smartphone app", "Best-in-class durability"],
        warranty: "Lifetime shell, 5-year equipment, 3-year labour",
        models: ["Epic", "Reward", "Euphoria", "Momentum", "Quest"],
        rating: 4.5, reviews: 428,
    },
    {
        id: 11, name: "Fantasy Spas", slug: "fantasy",
        tier: "entry", origin: "USA", best: ["value","family"],
        founded: "2005", priceRange: "£2,000 – £5,000",
        tagline: "Great Value Hot Tubs.",
        accentColor: "#6a1b9a",
        tags: ["Entry Level", "USA", "Budget-Friendly"],
        short: "Fantasy Spas make hot tub ownership accessible for first-time buyers without sacrificing the essentials. A solid choice for families looking for their first hot tub on a budget.",
        desc: "Fantasy Spas are manufactured in the USA and targeted at the entry-level and mid-range market. They offer solid construction, reliable components, and the essential hot tub experience at prices that put ownership within reach of more families. While not as feature-rich as premium brands, Fantasy Spas offer good reliability and are backed by a US-based manufacturer warranty.",
        strengths: ["Accessible price points", "US-manufactured reliability", "Good warranty for price tier", "Available in compact sizes", "Suitable for first-time buyers", "Simple operation and maintenance"],
        warranty: "5-year shell, 2-year equipment",
        models: ["Emerald", "Dream", "Dream DL", "Illusion", "Wish"],
        rating: 4.1, reviews: 203,
    },
    {
        id: 12, name: "Freeflow Spas", slug: "freeflow",
        tier: "entry", origin: "USA", best: ["value","family"],
        founded: "1997", priceRange: "£1,800 – £4,500",
        tagline: "Simple. Reliable. Affordable.",
        accentColor: "#0277bd",
        tags: ["Entry Level", "USA", "Plug & Play"],
        short: "Freeflow Spas are factory-assembled, plug-and-play hot tubs that require no electrician. Ideal for renters, first-time buyers, or those who want a simple, hassle-free hot tub experience.",
        desc: "Freeflow Spas specialise in factory-assembled plug-and-play hot tubs that operate on a standard 13-amp household socket — no electrician required. This makes them ideal for renters, temporary installations, or buyers who want minimum setup hassle. While performance is more modest than hardwired models, Freeflow Spas are robustly built in the USA with genuine fibreglass shells and reliable pump systems.",
        strengths: ["True plug-and-play operation", "No electrician required", "USA-manufactured fibreglass shell", "Simple relocation possible", "Low barrier to entry", "Suitable for rental properties"],
        warranty: "5-year shell, 1-year equipment",
        models: ["Curve", "Tempo", "Rhythm", "Groove", "Alto"],
        rating: 4.0, reviews: 178,
    },
    {
        id: 13, name: "Beachcomber", slug: "beachcomber",
        tier: "premium", origin: "Canada", best: ["cold","family","therapy"],
        founded: "1978", priceRange: "£4,500 – £14,000",
        tagline: "Canadian Craftsmanship. Built to Last.",
        accentColor: "#004d40",
        tags: ["Premium", "Canada", "Cold Climate"],
        short: "Beachcomber has been hand-crafting hot tubs in British Columbia since 1978. Renowned for outstanding cold-climate performance and a genuine commitment to environmental sustainability.",
        desc: "Beachcomber Hot Tubs has been handcrafting premium hot tubs in Surrey, British Columbia since 1978. As one of Canada's oldest hot tub manufacturers, they've perfected cold-climate performance with their Full Foam System and high-density cabinet insulation. Beachcomber is also an industry leader in environmental responsibility, using eco-friendly manufacturing processes and offering the H2X salt water system for reduced chemical use.",
        strengths: ["Hand-crafted in British Columbia", "Full foam insulation system", "H2X salt water system", "Environmentally responsible manufacturing", "45+ years Canadian heritage", "Excellent cold-weather efficiency"],
        warranty: "Lifetime shell, 5-year equipment",
        models: ["550 Beachcomber", "650 Series", "750 Series", "850 Series", "950 Series"],
        rating: 4.5, reviews: 367,
    },
    {
        id: 14, name: "Artesian Hot Tubs", slug: "artesian",
        tier: "mid", origin: "USA", best: ["family","therapy","value"],
        founded: "1993", priceRange: "£3,200 – £10,000",
        tagline: "Quality Without Compromise.",
        accentColor: "#37474f",
        tags: ["Mid-Range", "USA", "Value"],
        short: "Artesian Hot Tubs punch well above their weight for the price, offering stainless steel jets, full-foam insulation, and ozone sanitation on models that compete with much more expensive brands.",
        desc: "Artesian Spas has been manufacturing hot tubs in Ontario, California since 1993. They've built their reputation by offering features typically reserved for luxury brands — stainless steel jet bodies, full-foam insulation, and ozone sanitation — at genuinely mid-range prices. The Island Series and Garden Spas collections offer particularly strong value, and their Platinum Series competes directly with premium brands at a fraction of the cost.",
        strengths: ["Stainless steel jets as standard", "Full-foam insulation", "Ozone sanitation included", "Excellent value proposition", "Wide range of sizes (3–8 person)", "Good UK parts availability"],
        warranty: "Lifetime shell, 5-year plumbing, 2-year equipment",
        models: ["Platinum Elite", "Island Series", "South Seas Series", "Garden Spas", "Greenbrier Spas"],
        rating: 4.3, reviews: 289,
    },
    {
        id: 15, name: "Nordic Hot Tubs", slug: "nordic",
        tier: "mid", origin: "USA", best: ["value","family"],
        founded: "1995", priceRange: "£2,500 – £7,000",
        tagline: "Great Performance. Outstanding Durability.",
        accentColor: "#283593",
        tags: ["Mid-Range", "USA", "Durable"],
        short: "Nordic Hot Tubs are built with a focus on durability and low operating costs. Their patented energy-efficient pump system and full-foam insulation deliver impressive running costs for the price.",
        desc: "Nordic Hot Tubs are manufactured in Minnesota, USA and are specifically engineered for cold-climate performance and energy efficiency. Their patented EnergyPro™ pump system uses a single multi-speed pump to dramatically reduce electricity consumption compared to multi-pump systems. Nordic hot tubs are known for their reliability, straightforward maintenance, and competitive running costs — ideal for buyers who want long-term value.",
        strengths: ["EnergyPro™ single pump system", "Full-foam insulation", "Cold-climate Minnesota engineering", "Low energy consumption", "Simple maintenance design", "Strong value for money"],
        warranty: "5-year shell, 3-year plumbing, 2-year equipment",
        models: ["Encore", "Crown", "Grande", "Retreat LS", "Legend LS"],
        rating: 4.2, reviews: 241,
    },
];

const tierLabels  = { luxury:'Luxury', premium:'Premium', mid:'Mid-Range', entry:'Entry Level' };
const tierClasses = { luxury:'luxury', premium:'premium', mid:'mid', entry:'budget' };
const tierColors  = {
    luxury:  { bg:'#fff8e6', color:'#a06200' },
    premium: { bg:'#e8f0fe', color:'#2d62d3' },
    mid:     { bg:'#e8f5e9', color:'#2e7d32' },
    entry:   { bg:'#f3e5f5', color:'#7b1fa2' },
};

/* ── RENDER ───────────────────────────────────────────────────────────────── */
function updateBrandCount(){
    const grid = document.getElementById('brandGrid');
    const visible = Array.from(grid.children).filter(el=>el.style.display!=='none').length;
    document.getElementById('brandCount').textContent = `Showing ${visible} brand${visible!==1?'s':''}`;
}

/* ── FILTERS ─────────────────────────────────────────────────────────────── */
function applyBrandFilters() {
    updateBrandCount(); // server rendered; keep count in sync
}
function resetBrandFilters() {
    ['filterTier','filterOrigin','filterBest'].forEach(id => document.getElementById(id).value = '');
    updateBrandCount();
}
['filterTier','filterOrigin','filterBest'].forEach(id =>
    document.getElementById(id).addEventListener('change', applyBrandFilters));

/* ── BRAND DETAIL MODAL ───────────────────────────────────────────────────── */
function openBrandModal(id) {
    const b = brands.find(x => x.id === id);
    const stars = '★'.repeat(Math.round(b.rating)) + '☆'.repeat(5 - Math.round(b.rating));
    const tc = tierColors[b.tier];
    document.getElementById('brandModalBody').innerHTML = `
        <div style="border-radius:var(--r-lg);overflow:hidden;height:120px;background:${b.accentColor};display:flex;align-items:center;padding:0 2rem;gap:1.5rem;margin-bottom:1.5rem;">
            <div style="width:64px;height:64px;border-radius:var(--r-md);background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;">${b.name.charAt(0)}</div>
            <div>
                <div style="font-size:1.6rem;font-weight:800;color:#fff;">${b.name}</div>
                <div style="font-size:.88rem;color:rgba(255,255,255,.8);">${b.tagline}</div>
            </div>
            <span style="margin-left:auto;background:${tc.bg};color:${tc.color};font-size:.72rem;font-weight:800;padding:.25rem .75rem;border-radius:9999px;text-transform:uppercase;">${tierLabels[b.tier]}</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
            <div style="background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--r-md);padding:1rem;">
                <div style="font-size:.72rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem;">Origin</div>
                <div style="font-size:1rem;font-weight:700;color:var(--gray-900);">${b.origin}</div>
            </div>
            <div style="background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--r-md);padding:1rem;">
                <div style="font-size:.72rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem;">Founded</div>
                <div style="font-size:1rem;font-weight:700;color:var(--gray-900);">${b.founded}</div>
            </div>
            <div style="background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--r-md);padding:1rem;">
                <div style="font-size:.72rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem;">Price Range</div>
                <div style="font-size:1rem;font-weight:700;color:var(--gray-900);">${b.priceRange}</div>
            </div>
            <div style="background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--r-md);padding:1rem;">
                <div style="font-size:.72rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem;">Warranty</div>
                <div style="font-size:.88rem;font-weight:700;color:var(--gray-900);">${b.warranty}</div>
            </div>
        </div>
        <div style="margin-bottom:1.5rem;">
            <div class="ht-card__rating" style="margin-bottom:1rem;">
                <span class="ht-stars ht-stars--lg">${stars}</span>
                <span class="ht-rating-num">${b.rating} / 5.0</span>
                <span class="ht-rating-count">${b.reviews.toLocaleString()} verified reviews</span>
            </div>
        </div>
        <p style="font-size:.93rem;color:var(--gray-500);line-height:1.75;margin-bottom:1.5rem;">${b.desc}</p>
        <div style="margin-bottom:1.5rem;">
            <div style="font-size:.82rem;font-weight:700;color:var(--gray-700);text-transform:uppercase;letter-spacing:1px;margin-bottom:.85rem;">Key Strengths</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .75rem;">
                ${b.strengths.map(s => `
                    <div style="display:flex;align-items:flex-start;gap:.5rem;font-size:.86rem;color:var(--gray-600);">
                        <span style="color:var(--teal);font-weight:700;flex-shrink:0;">✓</span>${s}
                    </div>`).join('')}
            </div>
        </div>
        <div style="margin-bottom:1.75rem;">
            <div style="font-size:.82rem;font-weight:700;color:var(--gray-700);text-transform:uppercase;letter-spacing:1px;margin-bottom:.75rem;">Popular Models</div>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                ${b.models.map(m => `<span class="tub-card__tag">${m}</span>`).join('')}
            </div>
        </div>
        <button class="parts-enquire-btn" style="width:100%;padding:.9rem;font-size:1rem;border-radius:var(--r-pill);" onclick="closeBrandModal();openBrandQuote(${b.id})">
            🔒 Get a Free Quote for ${b.name}
        </button>
    `;
    document.getElementById('brandModalOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeBrandModal(e) {
    if (!e || e.target === document.getElementById('brandModalOverlay') || e.type === 'click') {
        document.getElementById('brandModalOverlay').style.display = 'none';
        document.body.style.overflow = '';
    }
}

/* ── QUOTE MODAL ──────────────────────────────────────────────────────────── */
let activeBrandId = null;
function openBrandQuote(id) {
    activeBrandId = id;
    const b = brands.find(x => x.id === id);
    document.getElementById('brandQuoteTitle').textContent = `Get a Quote — ${b.name}`;
    document.getElementById('brandQuoteSub').textContent   = `Request pricing from authorised ${b.name} dealers near you.`;
    document.getElementById('brandQuoteOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeBrandQuote(e) {
    if (!e || e.target === document.getElementById('brandQuoteOverlay') || e.type === 'click') {
        document.getElementById('brandQuoteOverlay').style.display = 'none';
        document.body.style.overflow = '';
    }
}
function submitBrandQuote() {
    const name = document.getElementById('bqName').value.trim();
    const email = document.getElementById('bqEmail').value.trim();
    const phone = document.getElementById('bqPhone').value.trim();
    const postcode = document.getElementById('bqPostcode').value.trim();
    const terms = document.getElementById('bqTerms').checked;
    if (!name)     { alert('Please enter your name.');           return; }
    if (!email || !email.includes('@')) { alert('Please enter a valid email.'); return; }
    if (!phone)    { alert('Please enter your phone number.');   return; }
    if (!postcode) { alert('Please enter your postcode.');       return; }
    if (!terms)    { alert('Please agree to the Terms & Conditions.'); return; }
    document.getElementById('brandQuoteOverlay').style.display = 'none';
    document.getElementById('brandSuccessOverlay').style.display = 'flex';
}
function closeBrandSuccess() {
    document.getElementById('brandSuccessOverlay').style.display = 'none';
    document.body.style.overflow = '';
    ['bqName','bqEmail','bqPhone','bqPostcode','bqNotes'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('bqTerms').checked = false;
}

/* ── INIT ─────────────────────────────────────────────────────────────────── */
renderBrands(brands);
</script>

@endsection
