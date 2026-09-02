@extends('layouts.app')
@section('title', ($item->name ?? '').' | '.__('pages.services.title'))
@section('content')

<div class="container" style="margin-top:24px;margin-bottom:24px">
    @php
        $detailImage = null;
        if (!empty($item->image_url)) {
            $detailImage = (str_starts_with($item->image_url, 'http://') || str_starts_with($item->image_url, 'https://'))
                ? $item->image_url
                : \App\Support\PublicMedia::url(ltrim($item->image_url, '/'));
        }
    @endphp
    <a href="{{ route('services') }}" style="display:inline-block;color:#0ea5a3;text-decoration:none;font-weight:700">{{ __('pages.detail.back_to_services') }}</a>
    <div class="product-detail-hero">
        <div class="product-detail-gallery">
            <div class="ht-detail__img-wrap" style="aspect-ratio:4/3">
                <img src="{{ $detailImage ?: 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=900&q=80&auto=format&fit=crop' }}" class="ht-detail__img" alt="{{ $item->name }}">
            </div>
        </div>
        <div class="product-detail-summary" style="background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px">
            <h1 style="margin:0 0 10px 0;font-size:28px;line-height:1.2">{{ $item->name }}</h1>
            @if($item->description)
            <p style="margin:0 0 14px 0;color:#6b7280;">{{ $item->description }}</p>
            @endif
            <div class="product-detail-rating">
                <span style="font-size:.85rem;color:#6b7280">{{ __('pages.service_detail.starting_from') }}</span>
                <span style="font-size:1.6rem;font-weight:800">{{ !is_null($item->price) ? '£'.number_format($item->price,2) : __('pages.service_detail.contact') }}</span>
            </div>
            @if(is_array($item->includes) && count($item->includes))
            <div class="card" style="background:#f8fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px">
                <div style="display:flex;align-items:center;gap:8px;font-weight:800;margin-bottom:10px">
                    <svg width="16" height="16" fill="none" stroke="#0ea5a3" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ __('pages.service_detail.whats_included') }}
                </div>
                <ul class="product-detail-includes">
                    @foreach($item->includes as $inc)
                        <li style="margin-bottom:6px">{{ $inc }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <a href="#request" class="ht-get-quote-btn" style="display:inline-block;text-align:center;margin-top:14px">{{ __('pages.service_detail.request_service') }}</a>
        </div>
    </div>
</div>
@endsection
