@extends('layouts.app')
@section('title', 'Swim Spas – Expert Reviews & Buyer Guides')
@section('content')

<div class="ht-page-header">
    <div class="container">
        <h1 class="ht-page-title">Swim Spas</h1>
    </div>
</div>

<div class="ht-filters-bar">
    <div class="container">
        <div class="ht-filters-panel">
        <form class="ht-filters" method="GET" action="{{ route('swim-spas') }}" id="filterForm">
            <div class="ht-filter-group">
                <label class="ht-filter-label">Tier</label>
                <select class="ht-filter-select" name="tier" id="filter-tier">
                    @php $tierSel = request('tier'); @endphp
                    <option value="">All Tiers</option>
                    @foreach(($tierFilters ?? ['entry-level' => 'Entry Level', 'luxury' => 'Luxury', 'mid-range' => 'Mid-range']) as $val => $label)
                        <option value="{{ $val }}" {{ $tierSel == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">Minimum Seats</label>
                <select class="ht-filter-select" name="min_seats" id="filter-seats">
                    @php $seatSel = request('min_seats'); @endphp
                    <option value="">Any</option>
                    @foreach(($seatOptions ?? []) as $s)
                        <option value="{{ $s }}" {{ (string)$seatSel===(string)$s ? 'selected' : '' }}>{{ $s }}+</option>
                    @endforeach
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">Brand</label>
                <select class="ht-filter-select" name="brand" id="filter-brand">
                    <option value="">All Brands</option>
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
                <label class="ht-filter-label">Model</label>
                <select class="ht-filter-select" name="model" id="filter-model">
                    <option value="">All Models</option>
                </select>
            </div>
            <div class="ht-filter-results">
                @php $total = method_exists($items,'total') ? $items->total() : (isset($items) ? count($items) : 0); @endphp
                <span id="results-count">{{ $total }} swim spa{{ $total==1?'':'s' }}</span>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="ht-products-section">
    <div class="container">
        <div class="ht-products-grid" id="products-grid">
            @if(isset($items) && count($items))
                @foreach($items as $it)
                    @include('components.swim-spa-card', ['it' => $it])
                @endforeach
            @else
                @php
                    $catalogTotal = (int) ($swimSpaCatalogTotal ?? 0);
                    $noProductsInCatalog = $catalogTotal === 0;
                @endphp
                <div class="ht-no-results" id="no-results-initial" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
                    @if($noProductsInCatalog)
                        <div class="ht-no-results__icon" aria-hidden="true">🛁</div>
                        <h3>No products available</h3>
                        <p class="text-muted" style="margin-top:0.5rem;">There are no swim spas listed at the moment. Please check back later.</p>
                    @else
                        <div class="ht-no-results__icon" aria-hidden="true">🔍</div>
                        <h3>No swim spas match your filters</h3>
                        <p style="margin-top:0.5rem;">Try adjusting your filter criteria to see more results.</p>
                        <button type="button" class="btn btn--outline btn--pill" style="margin-top:1rem;" onclick="document.getElementById('filterForm').reset();document.getElementById('filterForm').submit();">Reset Filters</button>
                    @endif
                </div>
            @endif
        </div>

        @if(method_exists($items,'hasMorePages') && $items->hasMorePages())
        <div class="mt-4" style="padding:1rem;text-align:center">
            <button id="loadMore" class="btn btn--outline" data-next-page="{{ $items->currentPage()+1 }}">Load More</button>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
const form = document.getElementById('filterForm');
const selBrand = document.getElementById('filter-brand');
const selModel = document.getElementById('filter-model');
const modelsByBrand = @json($modelsByBrand ?? []);

function populateModels(brandSlug, keepSelected=true){
    const slugMap = {};
    @if(isset($brands))
        @foreach($brands as $b)
            slugMap['{{ $b->slug }}'] = '{{ addslashes($b->name) }}';
        @endforeach
    @endif
    const brandName = slugMap[brandSlug] || '';
    const list = brandName && modelsByBrand[brandName] ? modelsByBrand[brandName] : [];
    const current = keepSelected ? '{{ request('model','') }}' : '';
    selModel.innerHTML = '<option value=\"\">All Models</option>';
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
populateModels(selBrand.value, true);

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
        const url = '{{ route('swim-spas') }}' + '?' + params.toString();
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
