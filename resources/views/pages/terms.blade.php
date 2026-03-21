@extends('layouts.app')
@section('title', 'Terms & Conditions – Hot Tub Buyer')
@section('content')
<div class="container" style="max-width: 900px; margin: 4rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
    <div style="border-bottom: 2px solid #f3f4f6; padding-bottom: 2rem; margin-bottom: 2.5rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; color: #111827; margin-bottom: 0.75rem;">Terms & Conditions</h1>
        <p style="color: #6b7280; font-size: 1.1rem;">Last updated: 9 March 2026</p>
    </div>

    <div style="line-height: 1.8; color: #374151; font-size: 1.05rem;">
        <p style="margin-bottom: 2rem;">
            By using Hot Tub Buyer, you agree to the following terms and conditions.
        </p>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">1. Using the Platform</h2>
            <p>Our platform is designed to connect buyers with sellers of hot tubs, swim spas, and related products. We do not sell these products directly and are not responsible for the ultimate transaction between buyer and seller.</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">2. User Conduct</h2>
            <p>Users must provide accurate information and use the platform in good faith. Any attempt to manipulate our lead system or provide false information may result in suspension.</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">3. Intellectual Property</h2>
            <p>All content on this website is the property of Hot Tub Buyer or its licensors and is protected by copyright laws.</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">4. Limitation of Liability</h2>
            <p>We are not liable for any direct, indirect, or consequential loss resulting from your use of the platform or transactions with third parties found through our site.</p>
        </section>
    </div>

    <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #f3f4f6; text-align: center;">
        <a href="{{ url()->previous() ?: route('register') }}" class="btn btn--primary" style="padding: 0.75rem 2.5rem; border-radius: 999px;">Back</a>
    </div>
</div>
@endsection
