@extends('layouts.admin')
@section('title', __('panel.admin.nav.price') . ' - ' . __('panel.admin_title'))
@section('content')
<div class="panel-page-header">
    <div><h1 class="panel-page-title">{{ __('panel.admin.pricing.title') }}</h1><p class="panel-page-sub">{{ __('panel.admin.pricing.sub') }}</p></div>
</div>
@if(session('success')) <div class="alert alert--success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert--danger">{{ $errors->first() }}</div> @endif

<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.pricing.credit_package_pricing') }}</div>
    <form method="POST" action="{{ route('admin.pricing.packages') }}">
        @csrf
        <div id="packagesWrap" class="grid grid--3">
            @php $pk = $packages ?? collect(); @endphp
            @if($pk->isEmpty())
                @php $pk = collect([['credits'=>'','price'=>'','savings_label'=>'','most_popular'=>false]]); @endphp
            @endif
            @foreach($pk as $i=>$p)
            <div class="card" style="padding:1rem;border:1px solid var(--gray-200);border-radius:12px">
                <div class="grid grid--2">
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.plans.credits_included') }}</label><input class="form-input" name="credits[]" type="number" min="1" value="{{ old('credits.'.$i, is_object($p)?$p->credits:$p['credits']) }}"></div>
                    <div class="form-group"><label class="form-label">{{ __('panel.admin.common.price') }} (£)</label><input class="form-input" name="price[]" type="number" step="0.01" min="0" value="{{ old('price.'.$i, is_object($p)?$p->price:$p['price']) }}"></div>
                </div>
                <div class="form-group"><label class="form-label">{{ __('panel.admin.pricing.savings_label') }}</label><input class="form-input" name="savings_label[]" value="{{ old('savings_label.'.$i, is_object($p)?$p->savings_label:$p['savings_label']) }}" placeholder="e.g., 20% off"></div>
                <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="most_popular[{{ $i }}]" @checked(old('most_popular.'.$i, is_object($p)?$p->most_popular:$p['most_popular']))> {{ __('panel.admin.pricing.mark_most_popular') }}</label>
                <div class="text-sm text-muted">{{ __('panel.admin.pricing.price_per_credit') }}: £<span data-ppc>0.00</span></div>
                <button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endforeach
        </div>
        <div class="modal-actions" style="justify-content:flex-start;gap:8px;margin-top:12px">
            <button type="button" class="btn btn--ghost" onclick="addPackage()">{{ __('panel.admin.pricing.add_package') }}</button>
            <button class="btn btn--primary">{{ __('panel.admin.pricing.save_credit_packages') }}</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.pricing.enquiry_type_pricing') }}</div>
    <form method="POST" action="{{ route('admin.pricing.enquiry') }}">
        @csrf
        @php $e = $settings->enquiry_prices ?? []; @endphp
        <div class="grid grid--3">
            <div class="form-group"><label class="form-label">Hot Tub</label><input class="form-input" name="hot_tub" type="number" step="0.01" min="0" value="{{ old('hot_tub',$e['hot_tub']??0) }}"></div>
            <div class="form-group"><label class="form-label">Swim Spa</label><input class="form-input" name="swim_spa" type="number" step="0.01" min="0" value="{{ old('swim_spa',$e['swim_spa']??0) }}"></div>
            <div class="form-group"><label class="form-label">Pool</label><input class="form-input" name="pool" type="number" step="0.01" min="0" value="{{ old('pool',$e['pool']??0) }}"></div>
            <div class="form-group"><label class="form-label">Sauna</label><input class="form-input" name="sauna" type="number" step="0.01" min="0" value="{{ old('sauna',$e['sauna']??0) }}"></div>
            <div class="form-group"><label class="form-label">Outdoor Product</label><input class="form-input" name="outdoor_product" type="number" step="0.01" min="0" value="{{ old('outdoor_product', $e['outdoor_product'] ?? $e['outdoor_kitchen'] ?? 0) }}"></div>
            <div class="form-group"><label class="form-label">Other</label><input class="form-input" name="other" type="number" step="0.01" min="0" value="{{ old('other',$e['other']??0) }}"></div>
        </div>
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">{{ __('panel.admin.pricing.save_enquiry_type_pricing') }}</button></div>
    </form>
</div>

<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.pricing.lead_credit_costs') }}</div>
    <form method="POST" action="{{ route('admin.pricing.leads') }}">
        @csrf
        @php $l = $settings->lead_credit_costs ?? []; @endphp
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Budget Tier</label><input class="form-input" name="budget" type="number" step="0.01" min="0" value="{{ old('budget',$l['budget']??0) }}"></div>
            <div class="form-group"><label class="form-label">Mid-Range Tier</label><input class="form-input" name="mid_range" type="number" step="0.01" min="0" value="{{ old('mid_range',$l['mid_range']??0) }}"></div>
            <div class="form-group"><label class="form-label">Premium Tier</label><input class="form-input" name="premium" type="number" step="0.01" min="0" value="{{ old('premium',$l['premium']??0) }}"></div>
            <div class="form-group"><label class="form-label">Luxury Tier</label><input class="form-input" name="luxury" type="number" step="0.01" min="0" value="{{ old('luxury',$l['luxury']??0) }}"></div>
            <div class="form-group"><label class="form-label">Swim Spa</label><input class="form-input" name="swim_spa" type="number" step="0.01" min="0" value="{{ old('swim_spa',$l['swim_spa']??0) }}"></div>
            <div class="form-group"><label class="form-label">Service Enquiries</label><input class="form-input" name="service" type="number" step="0.01" min="0" value="{{ old('service',$l['service']??0) }}"></div>
            <div class="form-group"><label class="form-label">Parts Enquiries</label><input class="form-input" name="parts" type="number" step="0.01" min="0" value="{{ old('parts',$l['parts']??0) }}"></div>
            <div class="form-group"><label class="form-label">Manufacturer Price Multiplier</label><input class="form-input" name="manufacturer_multiplier" type="number" step="0.01" min="0" value="{{ old('manufacturer_multiplier',$l['manufacturer_multiplier']??0) }}"></div>
        </div>
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">{{ __('panel.admin.pricing.save_lead_pricing') }}</button></div>
    </form>
</div>

<div class="card">
    <div class="fw-800 mb-2" style="font-size:1.05rem;color:var(--gray-900)">{{ __('panel.admin.pricing.featured_content_pricing') }}</div>
    <form method="POST" action="{{ route('admin.pricing.featured') }}">
        @csrf
        @php $f = $settings->featured_prices ?? []; @endphp
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">Product of the Month (£)</label><input class="form-input" name="product_of_month" type="number" step="0.01" min="0" value="{{ old('product_of_month',$f['product_of_month']??0) }}"></div>
            <div class="form-group"><label class="form-label">Delivery of the Week (£)</label><input class="form-input" name="delivery_of_week" type="number" step="0.01" min="0" value="{{ old('delivery_of_week',$f['delivery_of_week']??0) }}"></div>
        </div>
        <div class="modal-actions" style="justify-content:flex-start"><button class="btn btn--primary">{{ __('panel.admin.pricing.save_featured_pricing') }}</button></div>
    </form>
</div>

<script>
function addPackage(){
    const wrap = document.getElementById('packagesWrap');
    const div = document.createElement('div');
    div.className = 'card';
    div.style = 'padding:1rem;border:1px solid var(--gray-200);border-radius:12px';
    div.innerHTML = `
        <div class="grid grid--2">
            <div class="form-group"><label class="form-label">{{ __('panel.admin.plans.credits_included') }}</label><input class="form-input" name="credits[]" type="number" min="1"></div>
            <div class="form-group"><label class="form-label">{{ __('panel.admin.common.price') }} (£)</label><input class="form-input" name="price[]" type="number" step="0.01" min="0"></div>
        </div>
        <div class="form-group"><label class="form-label">{{ __('panel.admin.pricing.savings_label') }}</label><input class="form-input" name="savings_label[]" placeholder="e.g., 20% off"></div>
        <label class="form-check" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="most_popular[${wrap.children.length}]"> {{ __('panel.admin.pricing.mark_most_popular') }}</label>
        <div class="text-sm text-muted">{{ __('panel.admin.pricing.price_per_credit') }}: £<span data-ppc>0.00</span></div>
        <button type="button" class="btn" onclick="this.parentElement.remove()">✕</button>
    `;
    wrap.appendChild(div);
}
document.addEventListener('input', function(e){
    if (e.target && (e.target.name==='credits[]' || e.target.name==='price[]')) {
        const card = e.target.closest('.card');
        const credits = card.querySelector('input[name=\"credits[]\"]').value;
        const price = card.querySelector('input[name=\"price[]\"]').value;
        const ppc = card.querySelector('[data-ppc]');
        const c = parseFloat(credits); const p = parseFloat(price);
        ppc.textContent = c>0&&p>=0 ? (p/c).toFixed(2) : '0.00';
    }
});
</script>
@endsection

