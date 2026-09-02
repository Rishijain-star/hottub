@extends('layouts.admin')
@section('title', __('panel.admin.nav.homepage_images') . ' - ' . __('panel.admin_title'))
@section('content')
<div class="panel-page-header">
    <div>
        <h1 class="panel-page-title">{{ __('panel.admin.settings.title') }}</h1>
        <p class="panel-page-sub">{{ __('panel.admin.settings.sub') }}</p>
    </div>
</div>
@if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
<div class="card">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label class="form-label">{{ __('panel.admin.settings.upload_hero_images') }}</label>
            <input type="file" name="hero_images[]" class="form-input" accept="image/*" multiple>
            <p class="text-xs text-muted" style="margin-top:.45rem;">Select one or more images from your computer. They will display as hero slides.</p>
        </div>

        <div class="form-group" style="margin-bottom:1.2rem;">
            <label class="form-label">{{ __('panel.admin.settings.cta_image') }}</label>
            <input type="file" name="cta_image" class="form-input" accept="image/*">
            <p class="text-xs text-muted" style="margin-top:.45rem;">This image appears in the "Ready To Find Your Perfect Hot Tub?" section.</p>
        </div>

        @if(!empty($homepageCtaImage))
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label class="form-label">{{ __('panel.admin.settings.current_cta_image') }}</label>
                <div style="display:grid;grid-template-columns:160px 1fr auto;gap:.75rem;align-items:center;border:1px solid rgb(44, 45, 45);border-radius:10px;padding:.7rem;">
                    <img src="{{ $homepageCtaImage['url'] }}" alt="CTA Image" style="width:160px;height:96px;object-fit:cover;object-position:center;border-radius:8px;">
                    <div class="text-sm" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $homepageCtaImage['path'] }}">{{ $homepageCtaImage['url'] }}</div>
                    <label class="text-xs" style="display:flex;align-items:center;gap:.35rem;">
                        <input type="checkbox" name="remove_cta_image" value="1">
                        {{ __('panel.admin.settings.remove') }}
                    </label>
                    <input type="hidden" name="existing_cta_path" value="{{ $homepageCtaImage['path'] }}">
                </div>
            </div>
        @endif

        @php($heroImages = $homepageHeroImages ?? [])
        @if(count($heroImages))
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label class="form-label">{{ __('panel.admin.settings.current_hero_images') }}</label>
                <div style="display:grid;gap:.75rem;">
                    @foreach($heroImages as $idx => $img)
                        <div style="display:grid;grid-template-columns:120px 1fr auto auto;gap:.75rem;align-items:center;border:1px solid rgb(44, 45, 45);border-radius:10px;padding:.7rem;">
                            <img src="{{ \App\Support\PublicMedia::url($img['path']) }}" alt="Hero Image {{ $idx + 1 }}" style="width:120px;height:68px;object-fit:cover;object-position:center;border-radius:8px;">
                            <div class="text-sm" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Stored path (relative to disk): {{ $img['path'] }}">{{ \App\Support\PublicMedia::url($img['path']) }}</div>
                            <div style="min-width:110px;">
                                <label class="text-xs text-muted" style="display:block;margin-bottom:.2rem;">{{ __('panel.admin.settings.order') }}</label>
                                <input type="number" min="1" name="existing_hero_sorts[]" value="{{ $img['sort'] ?? ($idx + 1) }}" class="form-input" style="padding:.45rem .6rem;">
                            </div>
                            <label class="text-xs" style="display:flex;align-items:center;gap:.35rem;">
                                <input type="checkbox" name="remove_hero_paths[]" value="{{ $img['path'] }}">
                                {{ __('panel.admin.settings.remove') }}
                            </label>
                            <input type="hidden" name="existing_hero_paths[]" value="{{ $img['path'] }}">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Business Details ────────────────────────────────────── --}}
        <div style="margin-top:2rem;padding-top:1.2rem;border-top:1px solid rgb(44, 45, 45);">
            <h2 class="panel-page-title" style="font-size:1.1rem;margin-bottom:.25rem;">{{ __('panel.admin.settings.business_details') }}</h2>
            <p class="panel-page-sub" style="margin-bottom:1rem;">These values are shown in the site footer and on customer/partner invoices.</p>

            <div class="grid grid--2" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;">
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.dealers.company_name') }}</label>
                    <input type="text" name="company_name" class="form-input" value="{{ old('company_name', $businessDetails['company_name'] ?? '') }}" placeholder="Hot Tub Buyer Ltd">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.settings.support_email') }}</label>
                    <input type="email" name="company_email" class="form-input" value="{{ old('company_email', $businessDetails['company_email'] ?? '') }}" placeholder="support@hottubbuyer.com">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">{{ __('panel.admin.settings.registered_address') }}</label>
                    <textarea name="company_address" class="form-input" rows="2" placeholder="Registered business address">{{ old('company_address', $businessDetails['company_address'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.dealers.vat_number') }}</label>
                    <input type="text" name="company_vat_number" class="form-input" value="{{ old('company_vat_number', $businessDetails['vat_number'] ?? '') }}" placeholder="e.g., 842368419">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.dealers.company_number') }}</label>
                    <input type="text" name="company_number" class="form-input" value="{{ old('company_number', $businessDetails['company_number'] ?? '') }}" placeholder="e.g., 049947">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.settings.fca_number') }}</label>
                    <input type="text" name="company_fca_number" class="form-input" value="{{ old('company_fca_number', $businessDetails['fca_number'] ?? '') }}" placeholder="Enter FCA registration number">
                </div>
            </div>
        </div>

        {{-- ── Social Media Links ──────────────────────────────────── --}}
        <div style="margin-top:2rem;padding-top:1.2rem;border-top:1px solid rgb(44, 45, 45);">
            <h2 class="panel-page-title" style="font-size:1.1rem;margin-bottom:.25rem;">{{ __('panel.admin.settings.social_media_links') }}</h2>
            <p class="panel-page-sub" style="margin-bottom:1rem;">Used by the footer social icons. Leave blank to hide an icon.</p>

            <div class="grid grid--2" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;">
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.settings.facebook_url') }}</label>
                    <input type="url" name="social_facebook_url" class="form-input" value="{{ old('social_facebook_url', $socialLinks['facebook'] ?? '') }}" placeholder="https://facebook.com/yourpage">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.settings.twitter_url') }}</label>
                    <input type="url" name="social_twitter_url" class="form-input" value="{{ old('social_twitter_url', $socialLinks['twitter'] ?? '') }}" placeholder="https://x.com/yourhandle">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.settings.instagram_url') }}</label>
                    <input type="url" name="social_instagram_url" class="form-input" value="{{ old('social_instagram_url', $socialLinks['instagram'] ?? '') }}" placeholder="https://instagram.com/yourhandle">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('panel.admin.settings.tiktok_url') }}</label>
                    <input type="url" name="social_tiktok_url" class="form-input" value="{{ old('social_tiktok_url', $socialLinks['tiktok'] ?? '') }}" placeholder="https://tiktok.com/@yourhandle">
                </div>
            </div>
        </div>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="btn btn--primary">{{ __('panel.admin.common.save') }}</button>
        </div>
    </form>
</div>
@endsection
