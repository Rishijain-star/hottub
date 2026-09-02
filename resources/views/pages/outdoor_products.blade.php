@extends('layouts.app')
@section('title', __('pages.outdoor_products.page_title'))
@section('meta_description', __('pages.outdoor_products.meta'))

@section('content')

{{-- Page Header --}}
<div class="ht-page-header">
    <div class="container">
        <h1 class="ht-page-title">{{ __('pages.outdoor_products.title') }}</h1>
    </div>
</div>

{{-- Filters Bar --}}
<div class="ht-filters-bar">
    <div class="container">
        <div class="ht-filters-panel">
        <form class="ht-filters" method="GET" action="{{ route('outdoor-products') }}" id="filterForm">
            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.tier') }}</label>
                <select class="ht-filter-select" name="tier" id="filter-tier">
                    @php $tierSel = request('tier'); @endphp
                    <option value="">{{ __('pages.filters.all_tiers') }}</option>
                    @foreach(($tierFilters ?? ['entry-level' => 'Entry Level', 'luxury' => 'Luxury', 'mid-range' => 'Mid-range']) as $val => $label)
                        <option value="{{ $val }}" @selected($tierSel == $val)>{{ __('pages.tiers.'.$val) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ht-filter-group">
                <label class="ht-filter-label">{{ __('pages.filters.brand') }}</label>
                <select class="ht-filter-select" name="brand" id="filter-brand">
                    <option value="">{{ __('pages.all_brands') }}</option>
                    @if(isset($brands) && count($brands))
                        @foreach($brands as $b)
                            <option value="{{ $b->slug }}" @selected(request('brand') == $b->slug)>
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
                <span id="results-count">{{ trans_choice('pages.outdoor_products.results', $total, ['count' => $total]) }}</span>
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
                @include('components.outdoor-product-card', ['it' => $it])
            @endforeach
            @endif
        </div>

        {{-- No results message --}}
        <div class="ht-no-results" id="no-results" style="{{ (isset($items) && count($items)) ? 'display:none;' : '' }}">
            <div class="ht-no-results__icon">🔍</div>
            <h3>{{ __('pages.outdoor_products.no_match_title') }}</h3>
            <p>{{ __('pages.no_results.desc') }}</p>
        </div>
        
        @if(method_exists($items,'hasMorePages') && $items->hasMorePages())
        <div class="mt-4" style="padding:1rem;text-align:center">
            <button id="loadMore" class="btn btn--outline" data-next-page="{{ $items->currentPage()+1 }}">{{ __('pages.load_more') }}</button>
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
const slugMap = @json(($brands ?? collect())->mapWithKeys(fn($b) => [$b->slug => $b->name]));

function populateModels(brandSlug, keepSelected=true){
    const brandName = slugMap[brandSlug] || '';
    const list = brandName && modelsByBrand[brandName] ? modelsByBrand[brandName] : [];
    const current = keepSelected ? '{{ request('model','') }}' : '';
    selModel.innerHTML = '<option value="">{{ __('pages.all_models') }}</option>';
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

populateModels(selBrand.value, true);

// Load More logic
(function(){
    const btn = document.getElementById('loadMore');
    if(!btn) return;
    const grid = document.getElementById('products-grid');
    btn.addEventListener('click', async function(){
        const next = parseInt(btn.dataset.nextPage || '2', 10);
        const params = new URLSearchParams(new FormData(form));
        params.set('page', String(next));
        params.set('fragment', '1');
        const url = '{{ route('outdoor-products') }}' + '?' + params.toString();
        try{
            const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
            const data = await res.json();
            const holder = document.createElement('div');
            holder.innerHTML = data.html;
            Array.from(holder.children).forEach(ch=>grid.appendChild(ch));
            if(data.hasMore){
                btn.dataset.nextPage = String(data.nextPage);
            }else{
                btn.remove();
            }
        }catch(e){}
    });
})();
</script>
@endsection
