@extends('layouts.app')
@section('title', ($item->brand ?? '').' '.($item->model ?? '').' | '.__('pages.detail.hot_tubs_title_suffix'))
@section('meta_description', __('pages.detail.meta_description', ['brand' => $item->brand, 'model' => $item->model]))

@section('content')
<div class="container" style="margin-top:24px;margin-bottom:24px">
    <a href="{{ route('hot-tubs') }}" style="display:inline-block;color:#0ea5a3;text-decoration:none;font-weight:700">{{ __('pages.detail.back_to_hot_tubs') }}</a>
    <div class="product-detail-hero">
        <div class="product-detail-gallery">
            @php
                $rawImgs = $item->images;
                if ($rawImgs instanceof \Illuminate\Support\Collection) {
                    $rawImgs = $rawImgs->all();
                }
                $imgs = is_array($rawImgs) ? $rawImgs : (is_string($rawImgs) ? (json_decode($rawImgs, true) ?: []) : []);
                $imgs = array_values(array_filter(array_map(function ($v) {
                    if (is_string($v)) return $v;
                    if (is_array($v)) return $v['path'] ?? $v['url'] ?? $v['file'] ?? ($v[0] ?? null);
                    return null;
                }, $imgs), fn ($v) => is_string($v) && $v !== ''));
                $galleryResolved = collect($imgs)->map(fn ($p) => \App\Support\PublicMedia::url($p))->filter()->values()->all();
                $img = count($galleryResolved) ? $galleryResolved[0] : 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=900&q=80&auto=format&fit=crop';
            @endphp
            <div class="ht-gallery__stage ht-detail__img-wrap">
                <button id="htPrev" class="ht-gallery__nav ht-gallery__nav--prev">‹</button>
                <img id="htMainImg" src="{{ $img }}" alt="{{ $item->model }}" class="ht-detail__img" loading="lazy" decoding="async">
                <button id="htNext" class="ht-gallery__nav ht-gallery__nav--next">›</button>
            </div>
            @if(count($imgs) > 1)
            <div class="ht-gallery__thumbs">
                @foreach($imgs as $i => $src)
                    <img src="{{ \App\Support\PublicMedia::url($src) }}" class="ht-gallery__thumb {{ $i===0 ? 'active' : '' }}" data-idx="{{ $i }}" loading="lazy" decoding="async">
                @endforeach
            </div>
            @endif
        </div>
        <div class="product-detail-summary" style="background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px">
            <p style="margin:0 0 6px 0;color:#0ea5a3;font-weight:700">{{ $item->brand }}</p>
            <h1 style="margin:0 0 10px 0;font-size:28px;line-height:1.2">{{ $item->model }}</h1>
            <div class="product-detail-rating">
                <span class="ht-stars ht-stars--lg">★★★★★</span>
                <span style="font-weight:700">{{ $item->overall ? number_format($item->overall,1) : '—' }} {{ __('pages.detail.overall_score') }}</span>
                <span style="color:#9ca3af">({{ __('pages.detail.reviews') }})</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
                @if($item->tier)
                <span style="background:#f5d76e;color:#6b4f00;font-weight:800;font-size:12px;padding:6px 10px;border-radius:999px">{{ strtoupper($item->tier) }} {{ __('pages.detail.tier') }}</span>
                @endif
                <span style="background:#d1fae5;color:#065f46;font-weight:700;font-size:12px;padding:6px 10px;border-radius:999px;display:inline-flex;align-items:center;gap:6px">
                    <svg viewBox="0 0 24 24" fill="none" width="14" height="14"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    {{ __('pages.detail.expert_reviewed') }}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:16px">
                <div style="display:flex;align-items:center;gap:10px;background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px">
                    <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
                    <div><div style="font-size:12px;color:#6b7280">{{ __('pages.detail.seats') }}</div><div style="font-weight:700">{{ $item->seats ?? '—' }}</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px">
                    <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <div><div style="font-size:12px;color:#6b7280">{{ __('pages.detail.jets') }}</div><div style="font-weight:700">{{ $item->jets ?? '—' }}</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px">
                    <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M3 6l3 1m0 0-3 9a5.002 5.002 0 0 0 6.001 0M6 7l3 9M6 7l6-2m6 2 3-1m-3 1-3 9a5.002 5.002 0 0 0 6.001 0M18 7l3 9m-3-9-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <div><div style="font-size:12px;color:#6b7280">{{ __('pages.detail.dimensions') }}</div><div style="font-weight:700">{{ $item->dimensions ?? '—' }}</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px">
                    <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <div><div style="font-size:12px;color:#6b7280">{{ __('pages.detail.power') }}</div><div style="font-weight:700">{{ $item->power_requirements ?? '—' }}</div></div>
                </div>
            </div>
            <button onclick='window.__openEnquiryModal({ title: @json(__('pages.detail.quote_title', ['brand' => $item->brand, 'model' => $item->model])), type: "hot_tub", product_id: "{{ $item->id }}" })' class="ht-get-quote-btn" style="display:block; width:100%; text-align:center;">{{ __('pages.detail.get_free_quote') }}</button>
            <p style="margin-top: 1rem; font-size: 0.82rem; color: #6b7280; text-align: center; line-height: 1.4;">
                {{ __('pages.detail.buying_support_text') }}
            </p>
        </div>
    </div>

    <div style="margin-top:24px;display:grid;grid-template-columns:1fr;gap:24px">
        <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
            <h3 style="margin:0 0 12px 0">{{ __('pages.detail.expert_review_scores') }}</h3>
            @php
                $scores = [
                    __('pages.detail.score_labels.comfort') => $item->comfort,
                    __('pages.detail.score_labels.efficiency') => $item->efficiency,
                    __('pages.detail.score_labels.features') => $item->features,
                    __('pages.detail.score_labels.quality') => $item->quality,
                    __('pages.detail.score_labels.value') => $item->value,
                ];
            @endphp
            <div class="product-detail-scores-grid">
                @foreach($scores as $label => $val)
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px">
                            <span>{{ $label }}</span>
                            <span>{{ $val !== null ? number_format($val,1) : '—' }}/5.0</span>
                        </div>
                        <div style="height:8px;background:#f3f4f6;border-radius:999px;overflow:hidden">
                            <div style="height:100%;background:#0ea5a3;width:{{ $val !== null ? ($val/5*100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="product-detail-two-col">
            <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
                <h3 style="margin:0 0 12px 0">{{ __('pages.detail.pros') }}</h3>
                <ul style="margin:0;padding:0;list-style:none">
                    @forelse($item->pros ?? [] as $p)
                        @include('components.icon-list-item', ['text' => $p, 'type' => 'pros'])
                    @empty
                        <li>—</li>
                    @endforelse
                </ul>
            </div>
            <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
                <h3 style="margin:0 0 12px 0">{{ __('pages.detail.cons') }}</h3>
                <ul style="margin:0;padding:0;list-style:none">
                    @forelse($item->cons ?? [] as $c)
                        @include('components.icon-list-item', ['text' => $c, 'type' => 'cons'])
                    @empty
                        <li>—</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
            <h3 style="margin:0 0 12px 0">{{ __('pages.detail.about_this_model') }}</h3>
            <p style="margin:0">{{ $item->description ?: __('pages.detail.details_coming_soon') }}</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
    const urls = @json(collect($imgs ?? [])->map(fn ($p) => \App\Support\PublicMedia::url($p))->filter()->values());
    if (!urls || urls.length === 0) return;
    let idx = 0;
    const main = document.getElementById('htMainImg');
    const prev = document.getElementById('htPrev');
    const next = document.getElementById('htNext');
    const thumbs = Array.from(document.querySelectorAll('.ht-gallery__thumb'));
    function show(i){
        idx = (i + urls.length) % urls.length;
        if(main) { main.src = urls[idx]; main.loading = 'lazy'; }
        thumbs.forEach((t,k)=>t.classList.toggle('active', k===idx));
    }
    prev && prev.addEventListener('click', function(e){ e.preventDefault(); show(idx-1); });
    next && next.addEventListener('click', function(e){ e.preventDefault(); show(idx+1); });
    thumbs.forEach((t,k)=> t.addEventListener('click', function(){ show(k); }));
})();
</script>
@endsection
