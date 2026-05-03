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
    ? (\App\Support\PublicMedia::url($imgs[0]) ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&q=80&auto=format&fit=crop')
    : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&q=80&auto=format&fit=crop';
@endphp
<div class="ht-card" data-seats="{{ $it->seats ?? 0 }}" data-brand="{{ strtolower($it->brand) }}">
    <a class="ht-card__img" href="{{ route('swim-spas.detail', $it->slug) }}">
        <img src="{{ $img }}" alt="{{ $it->model }}" loading="lazy">
    </a>
    <div class="ht-card__body">
        <div class="ht-card__top">
            <span class="ht-card__brand">{{ $it->brand }}</span>
            @if($it->tier)
            <span class="ht-card__tier ht-card__tier--{{ strtolower(str_replace(' ','-',$it->tier)) }}">{{ strtoupper($it->tier) }}</span>
            @endif
        </div>
        <h3 class="ht-card__name">
            <a href="{{ route('swim-spas.detail', $it->slug) }}" style="color:inherit;text-decoration:none">{{ $it->model }}</a>
        </h3>
        <div class="ht-card__specs">
            <span class="ht-card__spec">
                <svg viewBox="0 0 24 24" fill="none" width="13" height="13"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
                {{ $it->seats ?? '—' }} people
            </span>
            <span class="ht-card__spec">
                <svg viewBox="0 0 24 24" fill="none" width="13" height="13"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                {{ $it->jets ?? '—' }} jets
            </span>
        </div>
        <div class="ht-card__rating">
            <span class="ht-stars">★★★★★</span>
            <span class="ht-rating-num">{{ $it->overall ?? '—' }}</span>
            <span class="ht-rating-count">(reviews)</span>
        </div>
        <a class="ht-quote-btn" href="{{ route('swim-spas.detail', $it->slug) }}">Get Quote</a>
    </div>
</div>
