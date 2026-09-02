@extends('layouts.app')
@section('title', __('pages.hot_tubs.page_title'))
@section('meta_description', __('pages.hot_tubs.meta'))

@section('content')

{{-- Page Header --}}
<div class="ht-page-header">
    <div class="container">
        <h1 class="ht-page-title">{{ __('pages.hot_tubs.title') }}</h1>
    </div>
</div>

{{-- Filters Bar --}}
<div class="ht-filters-bar">
    <div class="container">
        <div class="ht-filters-panel">
        <form class="ht-filters" method="GET" action="{{ route('hot-tubs') }}" id="filterForm">
            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.tier') }}</label>
                <select class="ht-filter-select" name="tier" id="filter-tier">
                    @php $tierSel = request('tier'); @endphp
                    <option value="">{{ __('pages.filters.all_tiers') }}</option>
                    @foreach(($tierFilters ?? ['entry-level' => 'Entry Level', 'luxury' => 'Luxury', 'mid-range' => 'Mid-range']) as $val => $label)
                        <option value="{{ $val }}" {{ $tierSel==$val ? 'selected' : '' }}>{{ __('pages.tiers.'.$val) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.min_seats') }}</label>
                <select class="ht-filter-select" name="min_seats" id="filter-seats">
                    @php $seatSel = request('min_seats'); @endphp
                    <option value="">{{ __('pages.any') }}</option>
                    @foreach(($seatOptions ?? []) as $s)
                        <option value="{{ $s }}" {{ (string)$seatSel===(string)$s ? 'selected' : '' }}>{{ $s }}+</option>
                    @endforeach
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.brand') }}</label>
                <select class="ht-filter-select" name="brand" id="filter-brand">
                    <option value="">{{ __('pages.all_brands') }}</option>
                    @if(isset($brands) && count($brands))
                        @foreach($brands as $b)
                            <option value="{{ $b->slug }}" {{ request('brand')===$b->slug ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.model') }}</label>
                <select class="ht-filter-select" name="model" id="filter-model">
                    <option value="">{{ __('pages.all_models') }}</option>
                </select>
            </div>
            <div class="ht-filter-results">
                @php $total = method_exists($items,'total') ? $items->total() : (isset($items) ? count($items) : 0); @endphp
                <span id="results-count">{{ trans_choice('pages.hot_tubs.results', $total, ['count' => $total]) }}</span>
            </div>
        </form>
        </div>
    </div>
</div>

{{-- Products Grid --}}
<div class="ht-products-section">
    <div class="container">
        <div class="ht-products-grid" id="products-grid">
            @if(isset($items) && count($items))
            @foreach($items as $it)
                @include('components.hot-tub-card', ['it' => $it])
            @endforeach
            @else
            @endif

        </div>

        {{-- No results message --}}
        <div class="ht-no-results" id="no-results" style="{{ (isset($items) && count($items)) ? 'display:none;' : '' }}">
            <div class="ht-no-results__icon">🔍</div>
            <h3>{{ __('pages.hot_tubs.no_match_title') }}</h3>
            <p>{{ __('pages.no_results.desc') }}</p>
            <button class="btn btn--outline btn--pill" onclick="resetFilters()">{{ __('pages.no_results.reset') }}</button>
        </div>
        @if(method_exists($items,'hasMorePages') && $items->hasMorePages())
        <div class="mt-4" style="padding:1rem;text-align:center">
            <button id="loadMore" class="btn btn--outline" data-next-page="{{ $items->currentPage()+1 }}">{{ __('pages.load_more') }}</button>
        </div>
        @endif
    </div>
</div>



{{-- ── QUOTE MODAL ── --}}
<div class="ht-modal-overlay" id="quote-modal" style="display:none;" onclick="closeQuoteModal(event)">
    <div class="ht-quote-modal">
        <button class="ht-modal__close" onclick="closeQuote()">✕</button>
        <h2 class="ht-quote-modal__title">Request a Free Quote</h2>
        <p class="ht-quote-modal__sub" id="quote-product-name">Hotspring Highlife Aria</p>

        <div class="form-group">
            <label class="form-label">Select timeframe</label>
            <select class="form-input">
                <option>Select timeframe</option>
                <option>As soon as possible</option>
                <option>Within 1 month</option>
                <option>1–3 months</option>
                <option>3–6 months</option>
                <option>Just researching</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Message (Optional)</label>
            <textarea class="form-input" rows="4" placeholder="Any specific requirements or questions?"></textarea>
        </div>

        <div class="ht-quote__email-notice">
            <div class="ht-quote__email-icon">✉</div>
            <div>
                <p class="ht-quote__email-title">
                    <span class="ht-quote__email-dot"></span>
                    Email Verification Required
                </p>
                <p class="ht-quote__email-text">After clicking "Get Quote", you'll verify your email address via code.
                    This prevents spam and ensures dealers can contact you with genuine quotes.</p>
            </div>
        </div>

        <div class="ht-quote__terms">
            <input type="checkbox" id="terms-check" checked>
            <label for="terms-check">I agree to the <a href="#" class="text-teal">Terms &amp; Conditions</a> and consent
                to Hot Tub Buyer processing my personal data to connect me with up to 3 local approved dealers.
                *</label>
        </div>

        <button class="ht-get-quote-btn" onclick="submitQuote()">
            Get Quote (Verify Email Next) →
        </button>
        <p style="margin-top: 1rem; font-size: 0.85rem; color: #6b7280; text-align: center; line-height: 1.4;">
            Buying a hot tub is exciting. Our platform connects you with trusted dealers who will support you from purchase to installation and long-term ownership.
        </p>
    </div>
</div>

{{-- ── SUCCESS MODAL ── --}}
<div class="ht-modal-overlay" id="success-modal" style="display:none;" onclick="closeSuccessModal(event)">
    <div class="ht-success-modal">
        <button class="ht-modal__close"
            onclick="document.getElementById('success-modal').style.display='none'">✕</button>
        <div class="ht-success__icon">🎉</div>
        <h2>Coming Soon!</h2>
        <p>Email verification and dealer matching will be available soon. We're working hard to connect you with the
            best local dealers.</p>
        <button class="ht-get-quote-btn" onclick="document.getElementById('success-modal').style.display='none'">Got
            it!</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Auto-submit on filter change and populate model list based on brand
const form = document.getElementById('filterForm');
const selBrand = document.getElementById('filter-brand');
const selModel = document.getElementById('filter-model');
const modelsByBrand = @json($modelsByBrand ?? []);

function populateModels(brandSlug, keepSelected=true){
    // Map slug to brand name if possible
    const slugMap = {};
    @if(isset($brands))
        @foreach($brands as $b)
            slugMap['{{ $b->slug }}'] = '{{ addslashes($b->name) }}';
        @endforeach
    @endif
    const brandName = slugMap[brandSlug] || '';
    const list = brandName && modelsByBrand[brandName] ? modelsByBrand[brandName] : [];
    const current = keepSelected ? '{{ request('model','') }}' : '';
    selModel.innerHTML = '<option value=\"\">{{ __('pages.all_models') }}</option>';
    list.forEach(m=>{
        const opt = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        if(current && current===m) opt.selected = true;
        selModel.appendChild(opt);
    });
}

selBrand.addEventListener('change', function(){
    populateModels(this.value, false);
    form.submit();
});
selModel.addEventListener('change', function(){ form.submit(); });
const ft = document.getElementById('filter-tier'); if(ft){ ft.addEventListener('change', ()=>form.submit()); }
const fs = document.getElementById('filter-seats'); if(fs){ fs.addEventListener('change', ()=>form.submit()); }

// initialize models on load
populateModels(selBrand.value, true);

// Legacy modal handlers removed from listing (detail page now on its own)
function openDetail(imgEl) {
    const card = imgEl.closest('.ht-card');
    const name = card.querySelector('.ht-card__name').textContent;
    const brand = card.querySelector('.ht-card__brand').textContent;
    const img = imgEl.querySelector('img');
    const specs = card.querySelectorAll('.ht-card__spec');

    document.getElementById('detail-name').textContent = name;
    document.getElementById('detail-brand').textContent = brand;
    document.getElementById('detail-img').src = img.src;
    document.getElementById('detail-img').alt = img.alt;
    document.getElementById('detail-quote-btn').onclick = () => openQuote(brand + ' ' + name);

    // parse seats & jets from spec text
    specs.forEach(s => {
        const t = s.textContent.trim();
        if (t.includes('seat')) document.getElementById('detail-seats').textContent = t.replace('seats', '')
            .trim();
        if (t.includes('jet')) document.getElementById('detail-jets').textContent = t.replace('jets', '')
    .trim();
    });

    document.getElementById('detail-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDetailModal(e) {
    if (e.target === document.getElementById('detail-modal')) closeDetailFull();
}

function closeDetailFull() {
    document.getElementById('detail-modal').style.display = 'none';
    document.body.style.overflow = '';
}

// ── QUOTE MODAL ───────────────────────────────────────────────────────────────
function openQuote(productName) {
    document.getElementById('detail-modal').style.display = 'none';
    document.getElementById('quote-product-name').textContent = productName;
    document.getElementById('quote-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeQuote() {
    document.getElementById('quote-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function closeQuoteModal(e) {
    if (e.target === document.getElementById('quote-modal')) closeQuote();
}

function submitQuote() {
    const checked = document.getElementById('terms-check').checked;
    if (!checked) {
        alert('Please agree to the Terms & Conditions.');
        return;
    }
    closeQuote();
    document.getElementById('success-modal').style.display = 'flex';
}

function closeSuccessModal(e) {
    if (e.target === document.getElementById('success-modal'))
        document.getElementById('success-modal').style.display = 'none';
}

// Load More with skeleton + AJAX
(function(){
    const btn = document.getElementById('loadMore');
    if(!btn) return;
    const grid = document.getElementById('products-grid');
    function skeletonHtml(){
        return `<div class="ht-card">
            <div class="ht-card__img" style="background:#e5e7eb"></div>
            <div class="ht-card__body">
                <div class="ht-card__top">
                    <span style="width:80px;height:12px;background:#eef2f7;border-radius:6px;display:inline-block"></span>
                    <span style="width:70px;height:18px;background:#fff3cd;border-radius:999px;display:inline-block"></span>
                </div>
                <div style="width:60%;height:16px;background:#eef2f7;border-radius:6px;margin-bottom:8px"></div>
                <div class="ht-card__specs">
                    <span style="width:80px;height:12px;background:#f3f4f6;border-radius:6px;display:inline-block"></span>
                    <span style="width:80px;height:12px;background:#f3f4f6;border-radius:6px;display:inline-block"></span>
                </div>
                <div style="height:14px;background:#f3f4f6;border-radius:6px;width:40%;margin-bottom:12px"></div>
                <div class="ht-quote-btn" style="pointer-events:none;opacity:.6;text-align:center">Loading…</div>
            </div>
        </div>`;
    }
    btn.addEventListener('click', async function(){
        const next = parseInt(btn.dataset.nextPage || '2', 10);
        const tempWrap = document.createElement('div');
        let skeletons = '';
        for(let i=0;i<6;i++) skeletons += skeletonHtml();
        tempWrap.innerHTML = skeletons;
        while(tempWrap.firstChild){ grid.appendChild(tempWrap.firstChild); }
        const params = new URLSearchParams(new FormData(document.getElementById('filterForm')));
        params.set('page', String(next));
        params.set('fragment', '1');
        const url = '{{ route('hot-tubs') }}' + '?' + params.toString();
        try{
            const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
            const data = await res.json();
            const added = Array.from(grid.querySelectorAll('.ht-card')).slice(-6);
            added.forEach(el=>el.remove());
            const holder = document.createElement('div');
            holder.innerHTML = data.html;
            Array.from(holder.children).forEach(ch=>grid.appendChild(ch));
            if(data.hasMore){
                btn.dataset.nextPage = String(data.nextPage);
            }else{
                btn.remove();
            }
        }catch(e){
            const added = Array.from(grid.querySelectorAll('.ht-card')).slice(-6);
            added.forEach(el=>el.remove());
        }
    });
})();
</script>
@endsection
