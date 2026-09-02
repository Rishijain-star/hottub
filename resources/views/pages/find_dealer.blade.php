@extends('layouts.app')
@section('title', __('pages.find_dealer.page_title'))
@section('content')

<style>
  .fd-card label { color: var(--gray-900) !important; font-weight: 600 !important; }
  .fd-card .form-input { color: var(--gray-900) !important; border-color: var(--gray-300) !important; }
  .fd-card .form-input::placeholder { color: var(--gray-500) !important; }
</style>

<section class="svc-hero" style="border-bottom:1px solid var(--gray-200);">
  <div class="container" style="text-align:center;">
    <span class="svc-hero__badge">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l7 7-7 7-7-7 7-7z"/></svg>
      {{ __('pages.find_dealer.hero_badge') }}
    </span>
    <h1 class="svc-hero__title">{{ __('pages.find_dealer.title') }}</h1>
    <p class="svc-hero__desc">{{ __('pages.find_dealer.hero_desc') }}</p>
  </div>
  </section>

<section class="section section--white" style="padding-top:1.5rem;padding-bottom:2rem;">
  <div class="container" style="max-width:760px;">
    <div class="card fd-card" style="background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px;border:1px solid #e5e7eb">
      <h3 style="margin:0 0 12px 0; color: var(--gray-900);">{{ __('pages.find_dealer.request_callback') }}</h3>
      <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">{{ __('pages.find_dealer.name') }}</label>
          <input class="form-input" type="text" id="fdName" placeholder="{{ __('pages.find_dealer.name_placeholder') }}">
        </div>
        <div class="form-group">
          <label class="form-label">{{ __('pages.find_dealer.email') }}</label>
          <input class="form-input" type="email" id="fdEmail" placeholder="{{ __('pages.find_dealer.email_placeholder') }}">
        </div>
        <div class="form-group">
          <label class="form-label">{{ __('pages.find_dealer.phone') }}</label>
          <input class="form-input" type="tel" id="fdPhone" placeholder="{{ __('pages.find_dealer.phone_placeholder') }}">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">{{ __('pages.find_dealer.postcode') }}</label>
          <input class="form-input" type="text" id="fdPostcode" placeholder="{{ __('pages.find_dealer.postcode_placeholder') }}">
        </div>
      </div>

      <div class="form-group" style="margin-top:20px">
        <label class="form-label" style="color: #000000 !important; font-weight: 800 !important; font-size: 1.1rem !important; display: block; margin-bottom: 15px;">{{ __('pages.find_dealer.looking_for') }}</label>
        <div id="fdChips" style="display:flex; flex-wrap: wrap; gap:12px;">
          @foreach([
            ['value' => 'Hot Tub', 'label' => __('pages.find_dealer.interests.hot_tub')],
            ['value' => 'Swim Spa', 'label' => __('pages.find_dealer.interests.swim_spa')],
            ['value' => 'Pool', 'label' => __('pages.find_dealer.interests.pool')],
            ['value' => 'Sauna', 'label' => __('pages.find_dealer.interests.sauna')],
            ['value' => 'Outdoor Product', 'label' => __('pages.find_dealer.interests.outdoor_product')],
            ['value' => 'Other', 'label' => __('pages.find_dealer.interests.other')],
          ] as $interest)
          <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; background: #ffffff; padding: 14px 18px; border-radius: 12px; border: 2px solid #00a896; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.08); min-width: 180px;">
            <input type="checkbox" name="interest[]" value="{{ $interest['value'] }}" class="fd-checkbox" style="width: 24px; height: 24px; accent-color: #00a896; cursor: pointer; border: 2px solid #00a896;">
            <span style="font-size: 1rem; color: #000000 !important; font-weight: 700 !important;">{{ $interest['label'] }}</span>
          </label>
          @endforeach
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('pages.find_dealer.preferred_timeframe') }}</label>
        <select class="form-input" id="fdTime">
          <option value="">{{ __('pages.find_dealer.timeframes.select') }}</option>
          <option>{{ __('pages.find_dealer.timeframes.asap') }}</option>
          <option>{{ __('pages.find_dealer.timeframes.weeks_1_2') }}</option>
          <option>{{ __('pages.find_dealer.timeframes.within_1_month') }}</option>
          <option>{{ __('pages.find_dealer.timeframes.within_3_months') }}</option>
          <option>{{ __('pages.find_dealer.timeframes.just_looking') }}</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">{{ __('pages.find_dealer.message_optional') }}</label>
        <textarea class="form-input" rows="4" id="fdMsg" placeholder="{{ __('pages.find_dealer.message_placeholder') }}" style="resize:vertical;"></textarea>
      </div>

      <div class="ht-quote__terms">
        <input type="checkbox" id="fdTerms" checked>
        <label for="fdTerms">{!! __('pages.find_dealer.terms_consent', ['terms_url' => route('terms')]) !!}</label>
      </div>

      <button class="ht-get-quote-btn" id="fdSubmit" style="display:block; width:100%; margin-top: 20px;">{{ __('pages.find_dealer.get_free_quote') }}</button>
    </div>
  </div>
</section>

<section class="section section--white" style="padding-top:0;padding-bottom:2.5rem;">
  <div class="container" style="max-width:980px;">
    <div style="background:#ecfeff;border:1px solid #bae6fd;border-radius:14px;padding:22px;">
      <h3 style="margin:0 0 8px 0;text-align:center">{{ __('pages.find_dealer.how_it_works') }}</h3>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:8px">
        <div style="text-align:center">
          <div style="width:34px;height:34px;border-radius:999px;background:#0ea5a3;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800">1</div>
          <div style="margin-top:6px;font-weight:700">{{ __('pages.find_dealer.steps.step1_title') }}</div>
          <div style="color:#6b7280;font-size:.92rem">{{ __('pages.find_dealer.steps.step1_desc') }}</div>
        </div>
        <div style="text-align:center">
          <div style="width:34px;height:34px;border-radius:999px;background:#0ea5a3;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800">2</div>
          <div style="margin-top:6px;font-weight:700">{{ __('pages.find_dealer.steps.step2_title') }}</div>
          <div style="color:#6b7280;font-size:.92rem">{{ __('pages.find_dealer.steps.step2_desc') }}</div>
        </div>
        <div style="text-align:center">
          <div style="width:34px;height:34px;border-radius:999px;background:#0ea5a3;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800">3</div>
          <div style="margin-top:6px;font-weight:700">{{ __('pages.find_dealer.steps.step3_title') }}</div>
          <div style="color:#6b7280;font-size:.92rem">{{ __('pages.find_dealer.steps.step3_desc') }}</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" style="padding-top:0;padding-bottom:3.25rem;">
  <div class="container" style="max-width:980px;">
    <div style="background:#0f766e;border-radius:14px;padding:22px;color:#e6fffb;border:1px solid #0d9488;">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;text-align:center">
        <div>
          <div style="font-weight:800">{{ __('pages.find_dealer.trust.verified_title') }}</div>
          <div style="opacity:.9">{{ __('pages.find_dealer.trust.verified_desc') }}</div>
        </div>
        <div>
          <div style="font-weight:800">{{ __('pages.find_dealer.trust.free_title') }}</div>
          <div style="opacity:.9">{{ __('pages.find_dealer.trust.free_desc') }}</div>
        </div>
        <div>
          <div style="font-weight:800">{{ __('pages.find_dealer.trust.max_quotes_title') }}</div>
          <div style="opacity:.9">{{ __('pages.find_dealer.trust.max_quotes_desc') }}</div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@section('scripts')
<script>
(function(){
  document.getElementById('fdSubmit').addEventListener('click', function(){
    const name  = document.getElementById('fdName').value.trim();
    const email = document.getElementById('fdEmail').value.trim();
    const phone = document.getElementById('fdPhone').value.trim();
    const pc    = document.getElementById('fdPostcode').value.trim();
    const terms = document.getElementById('fdTerms').checked;
    const checkboxes = document.querySelectorAll('.fd-checkbox:checked');
    const interests = Array.from(checkboxes).map(cb => cb.value);

    if(!name){ alert(@json(__('pages.find_dealer.alerts.name_required'))); return; }
    if(!email || !email.includes('@')){ alert(@json(__('pages.find_dealer.alerts.email_valid'))); return; }
    if(!phone){ alert(@json(__('pages.find_dealer.alerts.phone_required'))); return; }
    if(!pc){ alert(@json(__('pages.find_dealer.alerts.postcode_required'))); return; }
    if(interests.length === 0){ alert(@json(__('pages.find_dealer.alerts.interests_required'))); return; }
    if(!terms){ alert(@json(__('pages.find_dealer.alerts.terms_required'))); return; }

    window.__openEnquiryModal({
      title: @json(__('pages.find_dealer.modal_title')),
      subtitle: @json(__('pages.find_dealer.modal_subtitle_prefix')) + interests.join(', ')
    });
  });
})();
</script>
@endsection

