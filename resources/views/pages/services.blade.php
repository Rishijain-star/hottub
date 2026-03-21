@extends('layouts.app')
@section('title', 'Hot Tub Services – Expert Installation, Maintenance & Repair')
@section('content')

{{-- ══ HERO HEADER ══════════════════════════════════════════════════════════ --}}
<section class="svc-hero">
    <div class="container" style="text-align:center;">
        <span class="svc-hero__badge">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            Professional Services
        </span>
        <h1 class="svc-hero__title">Hot Tub Services</h1>
        <p class="svc-hero__desc">Expert installation, maintenance, and repair services to keep your hot tub running perfectly. Our network of certified technicians ensures professional care across the UK.</p>
    </div>
</section>

{{-- ══ TRUST BAR ════════════════════════════════════════════════════════════ --}}
<div class="svc-trust-bar">
    <div class="container">
        <div class="svc-trust-grid">
            <div class="svc-trust-item">
                <div class="svc-trust-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <strong>Certified Technicians</strong>
                    <p>All service providers are manufacturer-trained and fully insured for your peace of mind.</p>
                </div>
            </div>
            <div class="svc-trust-item">
                <div class="svc-trust-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <strong>Flexible Scheduling</strong>
                    <p>Book services at a time that suits you. Weekend and evening appointments available.</p>
                </div>
            </div>
            <div class="svc-trust-item">
                <div class="svc-trust-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <strong>Fast Response</strong>
                    <p>Emergency repairs typically scheduled within 24 hours. Get back to relaxing quickly.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ SERVICE CARDS ════════════════════════════════════════════════════════ --}}
<section class="section section--white">
    <div class="container">
        <div class="svc-cards-grid">
            @if(isset($items) && count($items))
                @foreach($items as $svc)
                <div class="svc-card">
                    <div class="svc-card__img svc-card__img--blue" @if($svc->image_url) style="background-image:url('{{ $svc->image_url }}');background-size:cover;background-position:center" @endif>
                        @if(empty($svc->image_url))
                        <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        @endif
                    </div>
                    <div class="svc-card__body">
                        <h3 class="svc-card__title">{{ $svc->name }}</h3>
                        @if($svc->description)
                        <p class="svc-card__desc">{{ $svc->description }}</p>
                        @endif
                        <div class="svc-card__price-row">
                            <span class="svc-card__from">Starting from</span>
                            <span class="svc-card__price">
                                @if(!is_null($svc->price)) £{{ number_format($svc->price,2) }} @else Contact @endif
                            </span>
                            <span class="svc-card__price-note">*Final price may vary based on requirements</span>
                        </div>
                        @if(is_array($svc->includes) && count($svc->includes))
                        <div class="svc-card__included">
                            <div class="svc-card__included-title">
                                <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                What's Included:
                            </div>
                            <ul class="svc-card__list">
                                @foreach($svc->includes as $inc)
                                <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> {{ $inc }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <button class="svc-request-btn" onclick="window.__openEnquiryModal({ title: 'Request {{ addslashes($svc->name) }}', type: 'service', product_id: '{{ $svc->id }}' })">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5A16 16 0 0 0 16 16.59l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 24 17z"/></svg>
                            Request Service
                        </button>
                    </div>
                </div>
                @endforeach
            @endif


            {{-- Annual Maintenance --}}
            <div class="svc-card">
                <div class="svc-card__img svc-card__img--blue">
                    <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div class="svc-card__body">
                    <h3 class="svc-card__title">Annual Maintenance Service</h3>
                    <p class="svc-card__desc">Complete annual service including water chemistry check, filter cleaning, jet inspection, and equipment testing.</p>
                    <div class="svc-card__price-row">
                        <span class="svc-card__from">Starting from</span>
                        <span class="svc-card__price">£250.00</span>
                        <span class="svc-card__price-note">*Final price may vary based on requirements</span>
                    </div>
                    <div class="svc-card__included">
                        <div class="svc-card__included-title">
                            <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            What's Included:
                        </div>
                        <ul class="svc-card__list">
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Water chemistry balance</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Filter deep clean</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Jet inspection</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Cover treatment</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Equipment testing</li>
                        </ul>
                    </div>
                    <button class="svc-request-btn" onclick="openServiceModal('Annual Maintenance Service')">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5A16 16 0 0 0 16 16.59l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 24 17z"/></svg>
                        Request Service
                    </button>
                </div>
            </div>

            {{-- Emergency Repair --}}
            <div class="svc-card">
                <div class="svc-card__img svc-card__img--red">
                    <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div class="svc-card__body">
                    <h3 class="svc-card__title">Emergency Repair Service</h3>
                    <p class="svc-card__desc">24/7 emergency repair service for urgent hot tub issues including pump failures, heater problems, and leaks.</p>
                    <div class="svc-card__price-row">
                        <span class="svc-card__from">Starting from</span>
                        <span class="svc-card__price">£200.00</span>
                        <span class="svc-card__price-note">*Final price may vary based on requirements</span>
                    </div>
                    <div class="svc-card__included">
                        <div class="svc-card__included-title">
                            <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            What's Included:
                        </div>
                        <ul class="svc-card__list">
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Emergency callout</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Diagnosis</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Labour (first hour)</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Parts extra</li>
                        </ul>
                    </div>
                    <button class="svc-request-btn" onclick="openServiceModal('Emergency Repair Service')">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5A16 16 0 0 0 16 16.59l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 24 17z"/></svg>
                        Request Service
                    </button>
                </div>
            </div>

            {{-- Installation --}}
            <div class="svc-card">
                <div class="svc-card__img svc-card__img--green">
                    <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div class="svc-card__body">
                    <h3 class="svc-card__title">Hot Tub Installation</h3>
                    <p class="svc-card__desc">Professional installation service including delivery, positioning, electrical connection, and initial setup.</p>
                    <div class="svc-card__price-row">
                        <span class="svc-card__from">Starting from</span>
                        <span class="svc-card__price">£500.00</span>
                        <span class="svc-card__price-note">*Final price may vary based on requirements</span>
                    </div>
                    <div class="svc-card__included">
                        <div class="svc-card__included-title">
                            <svg width="16" height="16" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            What's Included:
                        </div>
                        <ul class="svc-card__list">
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Delivery to site</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Positioning</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Electrical connection</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Water fill</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Chemical balance</li>
                            <li><svg width="15" height="15" fill="none" stroke="var(--teal)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Operation tutorial</li>
                        </ul>
                    </div>
                    <button class="svc-request-btn" onclick="openServiceModal('Hot Tub Installation')">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5A16 16 0 0 0 16 16.59l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 24 17z"/></svg>
                        Request Service
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══ PLANS + EMERGENCY 2-COL ══════════════════════════════════════════════ --}}
<section class="section section--gray">
    <div class="container">
        <div class="svc-bottom-grid">

            {{-- Maintenance Plans --}}
            <div class="svc-plans-box">
                <h2 class="svc-plans__title">Maintenance Plans</h2>
                <p class="svc-plans__desc">Save money with our annual maintenance plans. Regular servicing extends your hot tub's lifespan, reduces energy costs, and prevents expensive repairs.</p>

                <div class="svc-plan-list">
                    <div class="svc-plan-row">
                        <div>
                            <div class="svc-plan-name">Basic Plan</div>
                            <div class="svc-plan-sub">2 visits per year</div>
                        </div>
                        <span class="svc-plan-price">£250/yr</span>
                    </div>
                    <div class="svc-plan-row">
                        <div>
                            <div class="svc-plan-name">Premium Plan</div>
                            <div class="svc-plan-sub">4 visits per year + priority support</div>
                        </div>
                        <span class="svc-plan-price">£450/yr</span>
                    </div>
                    <div class="svc-plan-row svc-plan-row--best">
                        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                            <div>
                                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.2rem;">
                                    <span class="svc-best-badge">BEST VALUE</span>
                                    <span class="svc-plan-name">Platinum Plan</span>
                                </div>
                                <div class="svc-plan-sub">Monthly visits + 24/7 emergency cover</div>
                            </div>
                        </div>
                        <span class="svc-plan-price" style="color:var(--teal-dk);">£800/yr</span>
                    </div>
                </div>
            </div>

            {{-- Emergency Repairs --}}
            <div class="svc-emergency-box">
                <h2 class="svc-emergency__title">Emergency Repairs</h2>
                <p class="svc-emergency__desc">Hot tub broken? Our emergency repair service gets you back up and running fast. We stock common parts and can diagnose issues remotely to save time.</p>

                <div class="svc-emergency__features">
                    <div class="svc-emergency__feat">
                        <div class="svc-emergency__feat-icon">
                            <svg width="18" height="18" fill="none" stroke="var(--teal-lt)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <strong>24/7 Emergency Hotline</strong>
                            <span>Get help any time, day or night</span>
                        </div>
                    </div>
                    <div class="svc-emergency__feat">
                        <div class="svc-emergency__feat-icon">
                            <svg width="18" height="18" fill="none" stroke="var(--teal-lt)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <strong>Same-Day Service Available</strong>
                            <span>Most areas covered within 24 hours</span>
                        </div>
                    </div>
                    <div class="svc-emergency__feat">
                        <div class="svc-emergency__feat-icon">
                            <svg width="18" height="18" fill="none" stroke="var(--teal-lt)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <strong>All Major Brands</strong>
                            <span>Trained on Jacuzzi, Hot Spring, and more</span>
                        </div>
                    </div>
                </div>

                <button class="svc-call-btn" onclick="openServiceModal('Emergency Repair Service')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5A16 16 0 0 0 16 16.59l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 24 17z"/></svg>
                    Call Now: 0800 123 456
                </button>
            </div>

        </div>
    </div>
</section>

{{-- Modals replaced by global enquiry modal --}}

@endsection
