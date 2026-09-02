@extends('layouts.app')
@section('title', __('pages.legal.dealer_agreement.page_title'))
@section('content')
<div class="container" style="max-width: 900px; margin: 4rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
    <div style="border-bottom: 2px solid #f3f4f6; padding-bottom: 2rem; margin-bottom: 2.5rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; color: #111827; margin-bottom: 0.75rem;">{{ __('pages.legal.dealer_agreement.heading') }}</h1>
        <p style="color: #6b7280; font-size: 1.1rem;">{{ __('pages.legal.last_updated', ['date' => '9 March 2026']) }}</p>
    </div>

    <div style="line-height: 1.8; color: #374151; font-size: 1.05rem;">
        <p style="margin-bottom: 2rem; font-weight: 500; color: #4b5563;">
            {{ __('pages.legal.dealer_agreement.intro') }}
        </p>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">1</span>
                {{ __('pages.legal.dealer_agreement.sections.purpose.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.purpose.body') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">2</span>
                {{ __('pages.legal.dealer_agreement.sections.eligibility.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.eligibility.requirements_intro') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.eligibility.requirements.business') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.eligibility.requirements.brands') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.eligibility.requirements.insurance') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.eligibility.requirements.consumer_law') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.eligibility.requirements.data_protection') }}</li>
            </ul>
            <p style="margin-top: 1rem; font-style: italic; color: #6b7280;">{{ __('pages.legal.dealer_agreement.sections.eligibility.note') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">3</span>
                {{ __('pages.legal.dealer_agreement.sections.lead_distribution.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.lead_distribution.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_distribution.criteria.location') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_distribution.criteria.product_interest') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_distribution.criteria.availability') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_distribution.criteria.service_coverage') }}</li>
            </ul>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">4</span>
                {{ __('pages.legal.dealer_agreement.sections.lead_usage.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.lead_usage.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.allowed.responding') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.allowed.consultations') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.allowed.quotations') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.allowed.relevant_discussions') }}</li>
            </ul>
            <p style="margin-top: 1rem; font-weight: 600;">{{ __('pages.legal.dealer_agreement.sections.lead_usage.not_allowed_intro') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.5rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.not_allowed.sell_transfer') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.not_allowed.unrelated_marketing') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.lead_usage.not_allowed.excessive_contact') }}</li>
            </ul>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">5</span>
                {{ __('pages.legal.dealer_agreement.sections.service_standards.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.service_standards.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.service_standards.points.respond_promptly') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.service_standards.points.clear_info') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.service_standards.points.professional_installation') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.service_standards.points.aftercare_support') }}</li>
            </ul>
            <p style="margin-top: 1rem;">{{ __('pages.legal.dealer_agreement.sections.service_standards.note') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">6</span>
                {{ __('pages.legal.dealer_agreement.sections.fees.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.fees.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.fees.points.individual_leads') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.fees.points.monthly_membership') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.fees.points.premium_placements') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.fees.points.marketing_exposure') }}</li>
            </ul>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">7</span>
                {{ __('pages.legal.dealer_agreement.sections.platform_integrity.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.platform_integrity.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.platform_integrity.points.bypass_system') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.platform_integrity.points.manipulate_reviews') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.platform_integrity.points.misrepresent') }}</li>
            </ul>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">8</span>
                {{ __('pages.legal.dealer_agreement.sections.termination.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.termination.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 0.75rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.termination.reasons.service_standards') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.termination.reasons.data_protection') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #ef4444; margin-right: 10px;">•</span> {{ __('pages.legal.dealer_agreement.sections.termination.reasons.misleading_info') }}</li>
            </ul>
            <p style="margin-top: 1rem;">{{ __('pages.legal.dealer_agreement.sections.termination.note') }}</p>
        </section>

        <section>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">9</span>
                {{ __('pages.legal.dealer_agreement.sections.governing_law.title') }}
            </h2>
            <p>{{ __('pages.legal.dealer_agreement.sections.governing_law.body') }}</p>
        </section>
    </div>

    <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #f3f4f6; text-align: center;">
        <a href="{{ route('register') }}" class="btn btn--primary" style="padding: 0.75rem 2.5rem; border-radius: 999px;">{{ __('pages.legal.back_to_registration') }}</a>
    </div>
</div>
@endsection
