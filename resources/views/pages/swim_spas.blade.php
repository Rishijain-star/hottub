@extends('layouts.app')
@section('title', __('pages.swim_spas.page_title'))
@section('content')

{{-- ══ PAGE HEADER ══════════════════════════════════════════════════════════ --}}
<div class="ht-page-header">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <span class="section-label">{{ __('pages.browse_compare') }}</span>
                <h1 class="ht-page-title">{{ __('pages.swim_spas.title') }}</h1>
            </div>
            <span class="badge badge--teal badge--dot">{{ __('pages.live_pricing') }}</span>
        </div>
    </div>
</div>

{{-- ══ FILTERS BAR ══════════════════════════════════════════════════════════ --}}
<div class="ht-filters-bar" id="filtersBar">
    <div class="container">
        <div class="ht-filters" id="filtersForm">

            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.tier') }}</label>
                <select class="ht-filter-select" id="filterTier">
                    <option value="">{{ __('pages.filters.all_tiers') }}</option>
                    <option value="luxury">Luxury</option>
                    <option value="premium">Premium</option>
                    <option value="mid">Mid-Range</option>
                    <option value="budget">Entry Level</option>
                </select>
            </div>

            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.swim_spas.filters.length') }}</label>
                <select class="ht-filter-select" id="filterLength">
                    <option value="">{{ __('pages.swim_spas.filters.any_length') }}</option>
                    <option value="12">12 ft</option>
                    <option value="14">14 ft</option>
                    <option value="16">16 ft</option>
                    <option value="18">18 ft+</option>
                </select>
            </div>

            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.swim_spas.filters.swim_current') }}</label>
                <select class="ht-filter-select" id="filterCurrent">
                    <option value="">{{ __('pages.swim_spas.filters.any_type') }}</option>
                    <option value="jet">Jet System</option>
                    <option value="propeller">Propeller</option>
                    <option value="paddlewheel">Paddle Wheel</option>
                </select>
            </div>

            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.brand') }}</label>
                <select class="ht-filter-select" id="filterBrand">
                    <option value="">{{ __('pages.all_brands') }}</option>
                    <option value="Master Spas">Master Spas</option>
                    <option value="Endless Pools">Endless Pools</option>
                    <option value="Hydropool">Hydropool</option>
                    <option value="Jacuzzi">Jacuzzi</option>
                    <option value="Arctic Spas">Arctic Spas</option>
                    <option value="Swim Life">Swim Life</option>
                </select>
            </div>

            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.swim_spas.filters.max_price') }}</label>
                <select class="ht-filter-select" id="filterPrice">
                    <option value="">{{ __('pages.swim_spas.filters.any_price') }}</option>
                    <option value="20000">Under $20,000</option>
                    <option value="30000">Under $30,000</option>
                    <option value="40000">Under $40,000</option>
                    <option value="50000">Under $50,000</option>
                </select>
            </div>

            <div class="ht-filter-results" id="resultCount"></div>
        </div>
    </div>
</div>

{{-- ══ PRODUCTS GRID ════════════════════════════════════════════════════════ --}}
<section class="ht-products-section">
    <div class="container">
        <div class="ht-products-grid" id="productsGrid">
            {{-- Cards injected by JS --}}
        </div>
        <div class="ht-no-results" id="noResults" style="display:none;">
            <div class="ht-no-results__icon">🔍</div>
            <h3>{{ __('pages.swim_spas.no_found_title') }}</h3>
            <p>{{ __('pages.swim_spas.no_found_desc') }}</p>
            <button class="btn btn--outline btn--pill" onclick="resetFilters()">{{ __('pages.no_results.clear') }}</button>
        </div>
    </div>
</section>

{{-- ══ DETAIL MODAL ════════════════════════════════════════════════════════ --}}
<div class="ht-modal-overlay" id="detailOverlay" style="display:none;" onclick="closeDetailModal(event)">
    <div class="ht-detail-modal" id="detailModal">
        <button class="ht-modal__close" onclick="closeDetail()">&times;</button>
        <div id="detailContent"></div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const swimSpaI18n = @json([
    'one' => __('pages.swim_spas.results_one'),
    'other' => __('pages.swim_spas.results_other'),
]);
/* ── DATA ─────────────────────────────────────────────────────────────────── */
const swimSpas = [
    {
        id: 1,
        name: "H2X Trainer 15D",
        brand: "Master Spas",
        tier: "premium",
        length: 15,
        currentType: "jet",
        price: 28995,
        seats: 6,
        jets: 34,
        hp: 3.0,
        img: "https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=80&auto=format&fit=crop",
        rating: 4.7,
        reviews: 183,
        tags: ["Dual Zone", "ATV Therapy", "LED Package"],
        desc: "The H2X Trainer 15D is a dual-zone swim spa that delivers a powerful, adjustable current alongside a full hydrotherapy lounge — giving you a complete fitness and relaxation experience in one elegant unit.",
        features: ["Variable speed swim current", "Dual-zone design (swim + relax)", "34 stainless steel jets", "Energy-efficient insulation", "In.Touch 2 controls", "Ozone + UV-C sanitation", "LED underwater lighting", "Waterfall feature"],
    },
    {
        id: 2,
        name: "Fastlane Pool Pro",
        brand: "Endless Pools",
        tier: "luxury",
        length: 14,
        currentType: "propeller",
        price: 35500,
        seats: 4,
        jets: 20,
        hp: 2.5,
        img: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80&auto=format&fit=crop",
        rating: 4.9,
        reviews: 247,
        tags: ["Propeller Current", "Resistance Training", "SmartTouch"],
        desc: "Engineered for serious swimmers, the Fastlane Pool Pro delivers a smooth, turbulence-free current using Endless Pools' patented propeller system — no more flip turns, no lane sharing.",
        features: ["Patented Fastlane current", "Turbulence-free swim", "Digital current control", "Integrated SmartTouch app", "Stainless steel frame", "Underwater mirror", "Treadmill optional", "Compact 14 ft footprint"],
    },
    {
        id: 3,
        name: "AquaTrainer 14 SE",
        brand: "Hydropool",
        tier: "mid",
        length: 14,
        currentType: "paddlewheel",
        price: 19995,
        seats: 5,
        jets: 26,
        hp: 2.0,
        img: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&q=80&auto=format&fit=crop",
        rating: 4.5,
        reviews: 112,
        tags: ["Self-Cleaning", "Entry-Friendly", "Paddle Current"],
        desc: "Hydropool's AquaTrainer 14 SE is perfect for first-time swim spa buyers wanting professional-grade self-cleaning technology at a more accessible price point.",
        features: ["Self-cleaning filtration (100%)", "Paddle wheel current system", "5-person hydrotherapy seats", "EcoSmart energy package", "FiberCor insulation", "Plug-and-play ready", "Optional cover lifter", "10-year shell warranty"],
    },
    {
        id: 4,
        name: "J-16 PowerPro",
        brand: "Jacuzzi",
        tier: "luxury",
        length: 16,
        currentType: "jet",
        price: 42000,
        seats: 8,
        jets: 56,
        hp: 4.5,
        img: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&q=80&auto=format&fit=crop",
        rating: 4.8,
        reviews: 329,
        tags: ["56 PowerPro Jets", "Swim & Lounge", "ProAir Blower"],
        desc: "The J-16 is Jacuzzi's flagship swim spa — a showstopper combining powerful swim performance with a fully equipped 8-person hydrotherapy zone featuring 56 PowerPro jets.",
        features: ["56 PowerPro® jets", "Independent swim zone control", "Quiet Flo™ pump technology", "CLEARRAY® UV-C sanitation", "Fiber Optic starfield lighting", "Bluetooth audio system", "Auto-cover included", "SmartTub® WiFi app"],
    },
    {
        id: 5,
        name: "Arctic Swim X20",
        brand: "Arctic Spas",
        tier: "premium",
        length: 20,
        currentType: "jet",
        price: 39800,
        seats: 7,
        jets: 44,
        hp: 4.0,
        img: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&q=80&auto=format&fit=crop",
        rating: 4.6,
        reviews: 88,
        tags: ["Cold Climate Rated", "Full-Foam", "20 ft"],
        desc: "Built for extreme climates, the Arctic Swim X20 features Arctic Spas' legendary full-foam insulation, keeping operating costs low even in sub-zero winters while delivering a premium swim experience.",
        features: ["Arctic Shield™ full-foam insulation", "Cold-climate rated to -40°F", "Variable-speed swim current", "9-layer acrylic shell", "AquaFresh™ ozone system", "Moto-Massage DX jets", "Wireless connectivity", "20-year structural warranty"],
    },
    {
        id: 6,
        name: "SwimLife 12 Fitness",
        brand: "Swim Life",
        tier: "budget",
        length: 12,
        currentType: "jet",
        price: 14995,
        seats: 4,
        jets: 18,
        hp: 1.5,
        img: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80&auto=format&fit=crop",
        rating: 4.2,
        reviews: 67,
        tags: ["Compact", "Beginner-Friendly", "Budget"],
        desc: "The SwimLife 12 Fitness makes swim spa ownership accessible without compromising on the essentials. A compact 12 ft footprint fits most backyards, with an adjustable jet current for all ability levels.",
        features: ["12 ft compact design", "Adjustable jet current", "4-person seating area", "EconoPower pump", "Ozone sanitation included", "Digital display panel", "Integrated steps", "2-year full warranty"],
    },
    {
        id: 7,
        name: "E500 Executive",
        brand: "Endless Pools",
        tier: "premium",
        length: 18,
        currentType: "propeller",
        price: 31500,
        seats: 6,
        jets: 30,
        hp: 3.5,
        img: "https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=600&q=80&auto=format&fit=crop",
        rating: 4.7,
        reviews: 154,
        tags: ["Propeller Flow", "Executive Suite", "18 ft"],
        desc: "The E500 Executive offers the best of both worlds: Endless Pools' renowned turbulence-free propeller system in a spacious 18 ft dual-zone layout with premium lounge seating.",
        features: ["Fastlane® propeller current", "18 ft dual-zone design", "Executive teak accents", "Full-color touchscreen", "Saltwater compatible", "Programmable workout intervals", "Audio-visual package", "5-year component warranty"],
    },
    {
        id: 8,
        name: "AquaSport 16 Duo",
        brand: "Hydropool",
        tier: "premium",
        length: 16,
        currentType: "paddlewheel",
        price: 26500,
        seats: 6,
        jets: 40,
        hp: 3.0,
        img: "https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=80&auto=format&fit=crop",
        rating: 4.6,
        reviews: 101,
        tags: ["Duo Zone", "40 Jets", "Self-Cleaning"],
        desc: "The AquaSport 16 Duo pairs Hydropool's signature self-cleaning technology with a dual-zone layout — one for serious lap swimming and one for hydrotherapy massage with 40 precision jets.",
        features: ["Self-cleaning filtration system", "Paddle wheel + jet current", "40 hydrotherapy jets", "Ozone sanitation", "LED chromotherapy lighting", "In-floor cleaning heads", "Energy Star qualified", "Lifetime shell guarantee"],
    },
    {
        id: 9,
        name: "SwimSpa SS18 Trainer",
        brand: "Arctic Spas",
        tier: "mid",
        length: 18,
        currentType: "jet",
        price: 22800,
        seats: 5,
        jets: 30,
        hp: 2.5,
        img: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80&auto=format&fit=crop",
        rating: 4.4,
        reviews: 73,
        tags: ["Trainer Series", "Arctic Insulation", "18 ft"],
        desc: "The SS18 Trainer is Arctic Spas' mid-range swim spa, blending their world-renowned insulation with a solid 18 ft swim zone and comfortable 5-person hot tub lounge.",
        features: ["Arctic Shield™ insulation", "30 hydrotherapy jets", "Adjustable swim current", "Multi-stage filtration", "LED lighting package", "Digital controls", "All-weather cabinet", "Family-friendly design"],
    },
    {
        id: 10,
        name: "SwimLife Pro 16",
        brand: "Swim Life",
        tier: "mid",
        length: 16,
        currentType: "jet",
        price: 18500,
        seats: 5,
        jets: 24,
        hp: 2.5,
        img: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&q=80&auto=format&fit=crop",
        rating: 4.3,
        reviews: 55,
        tags: ["Mid-Range Value", "16 ft", "Spa Zone"],
        desc: "The SwimLife Pro 16 steps up from the entry line with a larger swim zone, more jet power, and an upgraded hydrotherapy area — ideal for families wanting both fitness and relaxation.",
        features: ["Spacious 16 ft design", "Variable speed current", "5-person spa zone", "24 hydrotherapy jets", "Energy-efficient heater", "Composite cabinet", "Full-colour LED", "3-year parts warranty"],
    },
    {
        id: 11,
        name: "J-13 Dual Swim",
        brand: "Jacuzzi",
        tier: "premium",
        length: 13,
        currentType: "jet",
        price: 29500,
        seats: 5,
        jets: 38,
        hp: 3.0,
        img: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&q=80&auto=format&fit=crop",
        rating: 4.7,
        reviews: 196,
        tags: ["Jacuzzi Jets", "Dual-Zone", "CLEARRAY UV-C"],
        desc: "In a more compact 13 ft footprint, the J-13 Dual Swim still packs in 38 PowerPro jets, Jacuzzi's CLEARRAY sanitation and a smooth, adjustable swim current perfect for everyday training.",
        features: ["38 PowerPro® jets", "CLEARRAY® UV-C sanitation", "Compact 13 ft design", "Adjustable swim current", "Quiet Flo™ pump", "SmartTub® WiFi", "Fiber optic lighting", "ClearRay app control"],
    },
    {
        id: 12,
        name: "Master H2X Stride 14",
        brand: "Master Spas",
        tier: "mid",
        length: 14,
        currentType: "jet",
        price: 21500,
        seats: 4,
        jets: 28,
        hp: 2.5,
        img: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&q=80&auto=format&fit=crop",
        rating: 4.5,
        reviews: 134,
        tags: ["Stride Treadmill", "28 Jets", "Compact Dual"],
        desc: "The H2X Stride 14 is built for aquatic exercise enthusiasts — featuring an optional underwater treadmill, 28 therapy jets, and Master Spas' EcoPure water management system.",
        features: ["Optional aqua treadmill", "28 stainless jets", "EcoPure™ water care", "14 ft dual-zone", "Variable swim current", "Handrail included", "Energy-efficient pump", "In.Touch 2 controls"],
    },
];

const tierLabels  = { luxury:'Luxury', premium:'Premium', mid:'Mid-Range', budget:'Entry Level' };
const tierClasses = { luxury:'luxury', premium:'premium', mid:'mid', budget:'budget' };

/* ── RENDER ───────────────────────────────────────────────────────────────── */
function renderCards(list) {
    const grid = document.getElementById('productsGrid');
    const none = document.getElementById('noResults');
    const tpl = (list.length === 1 ? swimSpaI18n.one : swimSpaI18n.other).replace(':count', String(list.length));
    document.getElementById('resultCount').textContent = tpl;

    if (!list.length) {
        grid.innerHTML = '';
        none.style.display = 'block';
        return;
    }
    none.style.display = 'none';
    grid.innerHTML = list.map(s => `
        <div class="ht-card" data-id="${s.id}">
            <div class="ht-card__img" onclick="openDetail(${s.id})">
                <img src="${s.img}" alt="${s.name}" loading="lazy">
            </div>
            <div class="ht-card__body">
                <div class="ht-card__top">
                    <span class="ht-card__brand">${s.brand}</span>
                    <span class="ht-card__tier ht-card__tier--${tierClasses[s.tier]}">${tierLabels[s.tier]}</span>
                </div>
                <div class="ht-card__name">${s.name}</div>
                <div class="ht-card__specs">
                    <span class="ht-card__spec">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                        ${s.length} ft
                    </span>
                    <span class="ht-card__spec">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        ${s.jets} jets
                    </span>
                    <span class="ht-card__spec">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        ${s.hp} HP
                    </span>
                </div>
                <div class="ht-card__tags">
                    ${s.tags.slice(0,3).map(t=>`<span class="tub-card__tag">${t}</span>`).join('')}
                </div>
                <div class="ht-card__rating">
                    <span class="ht-stars">${'★'.repeat(Math.round(s.rating))}${'☆'.repeat(5-Math.round(s.rating))}</span>
                    <span class="ht-rating-num">${s.rating}</span>
                    <span class="ht-rating-count">(${s.reviews} reviews)</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
                    <span style="font-size:1.15rem;font-weight:800;color:var(--gray-900);">$${s.price.toLocaleString()}</span>
                    <span style="font-size:.78rem;color:var(--gray-400);">est. retail</span>
                </div>
                <div style="display:flex;gap:.5rem;">
                    <button class="btn btn--outline btn--sm" style="flex:1;" onclick="openDetail(${s.id})">View Details</button>
                    <button class="ht-quote-btn" style="flex:1;" onclick="window.__openEnquiryModal({ title: 'Quote: ' + s.brand + ' ' + s.name, type: 'swim_spa', product_id: s.id })">Get Quote</button>
                </div>
            </div>
        </div>
    `).join('');
}

/* ── FILTERS ─────────────────────────────────────────────────────────────── */
function applyFilters() {
    const tier    = document.getElementById('filterTier').value;
    const length  = document.getElementById('filterLength').value;
    const current = document.getElementById('filterCurrent').value;
    const brand   = document.getElementById('filterBrand').value;
    const price   = document.getElementById('filterPrice').value;

    const filtered = swimSpas.filter(s => {
        if (tier    && s.tier        !== tier)                     return false;
        if (length  && s.length      < parseInt(length))           return false;
        if (current && s.currentType !== current)                  return false;
        if (brand   && s.brand       !== brand)                    return false;
        if (price   && s.price       > parseInt(price))            return false;
        return true;
    });
    renderCards(filtered);
}

function resetFilters() {
    ['filterTier','filterLength','filterCurrent','filterBrand','filterPrice']
        .forEach(id => document.getElementById(id).value = '');
    renderCards(swimSpas);
}

['filterTier','filterLength','filterCurrent','filterBrand','filterPrice']
    .forEach(id => document.getElementById(id).addEventListener('change', applyFilters));

/* ── DETAIL MODAL ─────────────────────────────────────────────────────────── */
function openDetail(id) {
    const s = swimSpas.find(x => x.id === id);
    if (!s) return;
    document.getElementById('detailContent').innerHTML = `
        <div style="margin-bottom:1.5rem;">
            <span class="ht-card__tier ht-card__tier--${tierClasses[s.tier]}" style="font-size:.8rem;">${tierLabels[s.tier]}</span>
        </div>
        <div class="ht-detail__layout">
            <div>
                <div class="ht-detail__img-wrap">
                    <img class="ht-detail__img" src="${s.img}" alt="${s.name}">
                </div>
                <div style="margin-top:1.25rem;">
                    <div class="ht-detail__specs-grid">
                        <div class="ht-detail__spec-item">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></svg>
                            <div><span class="ht-detail__spec-label">Length</span><span class="ht-detail__spec-val">${s.length} ft</span></div>
                        </div>
                        <div class="ht-detail__spec-item">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            <div><span class="ht-detail__spec-label">Jets</span><span class="ht-detail__spec-val">${s.jets} jets</span></div>
                        </div>
                        <div class="ht-detail__spec-item">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            <div><span class="ht-detail__spec-label">Horsepower</span><span class="ht-detail__spec-val">${s.hp} HP</span></div>
                        </div>
                        <div class="ht-detail__spec-item">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <div><span class="ht-detail__spec-label">Seating</span><span class="ht-detail__spec-val">${s.seats} people</span></div>
                        </div>
                        <div class="ht-detail__spec-item" style="grid-column:span 2;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4l16 16M4 20L20 4"/></svg>
                            <div><span class="ht-detail__spec-label">Current System</span><span class="ht-detail__spec-val" style="text-transform:capitalize;">${s.currentType}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="ht-detail__brand">${s.brand}</div>
                <h2 class="ht-detail__name">${s.name}</h2>
                <div class="ht-detail__rating">
                    <span class="ht-stars ht-stars--lg">${'★'.repeat(Math.round(s.rating))}${'☆'.repeat(5-Math.round(s.rating))}</span>
                    <span class="ht-detail__score">${s.rating} / 5.0</span>
                    <span class="ht-detail__reviews">${s.reviews} verified reviews</span>
                </div>
                <div class="ht-detail__badges">
                    ${s.tags.map(t=>`<span class="ht-detail__badge ht-detail__badge--teal">${t}</span>`).join('')}
                </div>
                <p style="color:var(--gray-500);font-size:.95rem;line-height:1.75;margin-bottom:1.5rem;">${s.desc}</p>
                <div style="margin-bottom:1.75rem;">
                    <h4 style="font-size:.85rem;font-weight:700;color:var(--gray-700);text-transform:uppercase;letter-spacing:1px;margin-bottom:.85rem;">What's Included</h4>
                    <ul style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .75rem;">
                        ${s.features.map(f=>`
                            <li style="display:flex;align-items:flex-start;gap:.5rem;font-size:.85rem;color:var(--gray-500);">
                                <span style="color:var(--teal);font-weight:700;flex-shrink:0;">✓</span>${f}
                            </li>`).join('')}
                    </ul>
                </div>
                <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem;padding:1.25rem;background:var(--teal-xxlt);border-radius:var(--r-md);border:1px solid var(--gray-200);">
                    <div>
                        <div style="font-size:.78rem;color:var(--gray-400);margin-bottom:.2rem;">Estimated Retail Price</div>
                        <div style="font-size:2rem;font-weight:800;color:var(--gray-900);">$${s.price.toLocaleString()}</div>
                        <div style="font-size:.78rem;color:var(--gray-500);">Get a quote for your best dealer price</div>
                    </div>
                    <div style="margin-left:auto;text-align:right;">
                        <div style="font-size:.78rem;color:var(--gray-400);">Savings potential</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#157a50;">Up to 15% off</div>
                    </div>
                </div>
                <button class="ht-get-quote-btn" onclick="window.__openEnquiryModal({ title: 'Quote: ' + s.brand + ' ' + s.name, type: 'swim_spa', product_id: s.id })">
                    🔒 Get My Free Quote — No Obligation
                </button>
            </div>
        </div>
    `;
    document.getElementById('detailOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDetail() {
    document.getElementById('detailOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

function closeDetailModal(e) {
    if (e.target === document.getElementById('detailOverlay')) closeDetail();
}

/* ── INIT ─────────────────────────────────────────────────────────────────── */
renderCards(swimSpas);
</script>
@endsection