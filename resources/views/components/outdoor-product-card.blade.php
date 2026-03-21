@php
$img = ($it->images && count($it->images))
    ? asset('storage/' . $it->images[0])
    : 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=400&q=80&auto=format&fit=crop';
@endphp
<div class="ht-card" data-brand="{{ strtolower($it->brand) }}">
    <a class="ht-card__img" href="{{ route('outdoor-products.detail', $it->slug) }}">
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
            <a href="{{ route('outdoor-products.detail', $it->slug) }}" style="color:inherit;text-decoration:none">{{ $it->model }}</a>
        </h3>
        <div class="ht-card__specs">
            <span class="ht-card__spec">{{ $it->product_type }}</span>
            @if($it->dimensions)
            <span class="ht-card__spec">{{ $it->dimensions }}</span>
            @endif
        </div>
        <div class="ht-card__rating">
            <span class="ht-stars">★★★★★</span>
            <span class="ht-rating-num">{{ $it->overall ?? '—' }}</span>
        </div>
        <a class="ht-quote-btn" href="{{ route('outdoor-products.detail', $it->slug) }}">View Details</a>
    </div>
</div>
