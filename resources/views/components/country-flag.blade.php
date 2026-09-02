@props(['locale' => 'en_GB', 'size' => 'md'])

@php
    $sizeMap = ['sm' => 'sm', 'md' => 'md', 'lg' => 'lg'];
    $sizeClass = $sizeMap[$size] ?? 'md';
    $iso = strtolower(explode('_', (string) $locale)[1] ?? 'gb');
@endphp

<span {{ $attributes->merge(['class' => 'country-flag country-flag--'.$sizeClass.' fi fi-'.$iso]) }} aria-hidden="true"></span>
