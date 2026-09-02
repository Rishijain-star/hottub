@extends('layouts.app')
@section('title', __('pages.legal.privacy.page_title'))
@section('content')
<div class="container" style="max-width: 900px; margin: 4rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
    <div style="border-bottom: 2px solid #f3f4f6; padding-bottom: 2rem; margin-bottom: 2.5rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; color: #111827; margin-bottom: 0.75rem;">{{ __('pages.legal.privacy.heading') }}</h1>
        <p style="color: #6b7280; font-size: 1.1rem;">{{ __('pages.legal.last_updated', ['date' => '9 March 2026']) }}</p>
    </div>

    <div style="line-height: 1.8; color: #374151; font-size: 1.05rem;">
        <p style="margin-bottom: 2rem;">{{ __('pages.legal.privacy.intro') }}</p>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">1</span>
                {{ __('pages.legal.privacy.sections.who_we_are.title') }}
            </h2>
            <p>{{ __('pages.legal.privacy.sections.who_we_are.body') }}</p>
            <p style="margin-top: 1rem;">
                <strong>{{ __('pages.legal.privacy.sections.who_we_are.email_label') }}</strong> <a href="mailto:privacy@hottubbuyer.co.uk" style="color: #0ea5a3; text-decoration: underline;">privacy@hottubbuyer.co.uk</a><br>
                <strong>{{ __('pages.legal.privacy.sections.who_we_are.website_label') }}</strong> <a href="https://www.hottubbuyer.co.uk" style="color: #0ea5a3; text-decoration: underline;">www.hottubbuyer.co.uk</a>
            </p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">2</span>
                {{ __('pages.legal.privacy.sections.personal_data.title') }}
            </h2>
            <p>{{ __('pages.legal.privacy.sections.personal_data.body') }}</p>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: #111827; margin: 1.5rem 0 0.75rem;">{{ __('pages.legal.privacy.sections.personal_data.direct_info_title') }}</h3>
            <ul style="list-style: none; padding-left: 1.5rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.personal_data.points.name_email_phone') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.personal_data.points.postcode_location') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.personal_data.points.product_interests') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.personal_data.points.login_history') }}</li>
            </ul>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">3</span>
                {{ __('pages.legal.privacy.sections.use_data.title') }}
            </h2>
            <p>{{ __('pages.legal.privacy.sections.use_data.body') }}</p>
            <ul style="list-style: none; padding-left: 1.5rem; margin-top: 1rem;">
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.use_data.points.connect_customers') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.use_data.points.manage_enquiries') }}</li>
                <li style="margin-bottom: 0.5rem; display: flex; align-items: center;"><span style="color: #0ea5a3; margin-right: 10px;">•</span> {{ __('pages.legal.privacy.sections.use_data.points.improve_website') }}</li>
            </ul>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">4</span>
                {{ __('pages.legal.privacy.sections.sharing_data.title') }}
            </h2>
            <p>{{ __('pages.legal.privacy.sections.sharing_data.body') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">5</span>
                {{ __('pages.legal.privacy.sections.retention_security.title') }}
            </h2>
            <p>{{ __('pages.legal.privacy.sections.retention_security.body') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem; display: flex; align-items: center;">
                <span style="background: #0ea5a3; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 12px;">6</span>
                {{ __('pages.legal.privacy.sections.your_rights.title') }}
            </h2>
            <p>{{ __('pages.legal.privacy.sections.your_rights.body') }}</p>
        </section>
    </div>

    <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #f3f4f6; text-align: center;">
        <a href="{{ route('register') }}" class="btn btn--primary" style="padding: 0.75rem 2.5rem; border-radius: 999px;">{{ __('pages.legal.back_to_registration') }}</a>
    </div>
</div>
@endsection
