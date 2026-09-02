@extends('layouts.app')
@section('title', __('pages.legal.terms.page_title'))
@section('content')
<div class="container" style="max-width: 900px; margin: 4rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
    <div style="border-bottom: 2px solid #f3f4f6; padding-bottom: 2rem; margin-bottom: 2.5rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; color: #111827; margin-bottom: 0.75rem;">{{ __('pages.legal.terms.heading') }}</h1>
        <p style="color: #6b7280; font-size: 1.1rem;">{{ __('pages.legal.last_updated', ['date' => '9 March 2026']) }}</p>
    </div>

    <div style="line-height: 1.8; color: #374151; font-size: 1.05rem;">
        <p style="margin-bottom: 2rem;">
            {{ __('pages.legal.terms.intro') }}
        </p>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">{{ __('pages.legal.terms.sections.using_platform.title') }}</h2>
            <p>{{ __('pages.legal.terms.sections.using_platform.body') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">{{ __('pages.legal.terms.sections.user_conduct.title') }}</h2>
            <p>{{ __('pages.legal.terms.sections.user_conduct.body') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">{{ __('pages.legal.terms.sections.intellectual_property.title') }}</h2>
            <p>{{ __('pages.legal.terms.sections.intellectual_property.body') }}</p>
        </section>

        <section style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">{{ __('pages.legal.terms.sections.liability.title') }}</h2>
            <p>{{ __('pages.legal.terms.sections.liability.body') }}</p>
        </section>
    </div>

    <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #f3f4f6; text-align: center;">
        <a href="{{ url()->previous() ?: route('register') }}" class="btn btn--primary" style="padding: 0.75rem 2.5rem; border-radius: 999px;">{{ __('pages.legal.back') }}</a>
    </div>
</div>
@endsection
