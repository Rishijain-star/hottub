@php
    $oldRaw = $oldPrice ?? null;
    $newRaw = $newPrice ?? null;

    $hasOld = $oldRaw !== null && $oldRaw !== '';
    $hasNew = $newRaw !== null && $newRaw !== '';
@endphp

@if($hasOld || $hasNew)
<div class="product-price-badge">
    @if($hasOld && $hasNew)
        <span class="product-price-badge__old">{{ number_format((float) $oldRaw, 2) }}</span>
        <span class="product-price-badge__new">{{ number_format((float) $newRaw, 2) }}</span>
    @elseif($hasNew)
        <span class="product-price-badge__new">{{ number_format((float) $newRaw, 2) }}</span>
    @else
        <span class="product-price-badge__new">{{ number_format((float) $oldRaw, 2) }}</span>
    @endif
</div>
@endif
