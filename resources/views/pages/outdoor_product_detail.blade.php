@extends('layouts.app')
@section('title', ($item->brand ?? '').' '.($item->model ?? '').' | Hot Tub Buyer')
@section('meta_description', 'Expert review, specs, pros and cons for '.$item->brand.' '.$item->model)

@section('content')
<div class="container" style="margin-top:24px;margin-bottom:24px">
    <a href="{{ route('outdoor-products') }}" style="display:inline-block;color:#0ea5a3;text-decoration:none;font-weight:700">← Back to Outdoor Products</a>
    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:28px;margin-top:16px;align-items:start">
        <div>
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
        <div style="background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px">
            <p style="margin:0 0 6px 0;color:#0ea5a3;font-weight:700">{{ $item->brand }}</p>
            <h1 style="margin:0 0 10px 0;font-size:28px;line-height:1.2">{{ $item->model }}</h1>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                <span class="ht-stars ht-stars--lg">★★★★★</span>
                <span style="font-weight:700">{{ $item->overall ? number_format($item->overall,1) : '—' }} Overall Score</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
                @if($item->tier)
                <span style="background:#f5d76e;color:#6b4f00;font-weight:800;font-size:12px;padding:6px 10px;border-radius:999px">{{ strtoupper($item->tier) }} TIER</span>
                @endif
                <span style="background:#d1fae5;color:#065f46;font-weight:700;font-size:12px;padding:6px 10px;border-radius:999px;display:inline-flex;align-items:center;gap:6px">
                    <svg viewBox="0 0 24 24" fill="none" width="14" height="14"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Expert Reviewed
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:16px">
                <div style="display:flex;align-items:center;gap:10px;background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px">
                    <div><div style="font-size:12px;color:#6b7280">Type</div><div style="font-weight:700">{{ $item->product_type ?? '—' }}</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px">
                    <div><div style="font-size:12px;color:#6b7280">Dimensions</div><div style="font-weight:700">{{ $item->dimensions ?? '—' }}</div></div>
                </div>
            </div>
            <button onclick="window.__openEnquiryModal({ title: 'Quote: {{ addslashes($item->brand) }} {{ addslashes($item->model) }}', type: 'outdoor_product', product_id: '{{ $item->id }}' })" class="ht-get-quote-btn" style="display:block; width:100%; text-align:center;">Get Free Quote</button>
        </div>
    </div>

    <div style="margin-top:24px;display:grid;grid-template-columns:1fr;gap:24px">
        <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
            <h3 style="margin:0 0 12px 0">Expert Review Scores</h3>
            @php
                $scores = [
                    'Quality' => $item->quality,
                    'Durability' => $item->durability,
                    'Features' => $item->features,
                    'Value' => $item->value,
                ];
            @endphp
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
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

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
                <h3 style="margin:0 0 12px 0">Pros</h3>
                <ul style="margin:0;padding:0;list-style:none">
                    @forelse($item->pros ?? [] as $p)
                        @include('components.icon-list-item', ['text' => $p, 'type' => 'pros'])
                    @empty
                        <li>—</li>
                    @endforelse
                </ul>
            </div>
            <div class="card" style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e5e7eb">
                <h3 style="margin:0 0 12px 0">Cons</h3>
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
            <h3 style="margin:0 0 12px 0">About This Model</h3>
            <p style="margin:0">{{ $item->description ?: 'Details coming soon.' }}</p>
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
