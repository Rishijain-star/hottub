@extends('layouts.app')
@section('title', 'Find a Dealer – Hot Tub Buyer')
@section('content')

<section class="svc-hero" style="border-bottom:1px solid var(--gray-200);">
  <div class="container" style="text-align:center;">
    <span class="svc-hero__badge">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l7 7-7 7-7-7 7-7z"/></svg>
      Connect with Local Experts
    </span>
    <h1 class="svc-hero__title">Find a Dealer</h1>
    <p class="svc-hero__desc">Submit your details and we'll connect you with up to 3 verified dealers in your area.</p>
  </div>
  </section>

<section class="section section--white" style="padding-top:1.5rem;padding-bottom:2rem;">
  <div class="container" style="max-width:760px;">
    <div class="card" style="background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:24px;border:1px solid #e5e7eb">
      <h3 style="margin:0 0 12px 0;">Request a Callback</h3>
      <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Name *</label>
          <input class="form-input" type="text" id="fdName" placeholder="Your full name">
        </div>
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input class="form-input" type="email" id="fdEmail" placeholder="you@example.com">
        </div>
        <div class="form-group">
          <label class="form-label">Phone *</label>
          <input class="form-input" type="tel" id="fdPhone" placeholder="07XXX XXXXXX">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Postcode *</label>
          <input class="form-input" type="text" id="fdPostcode" placeholder="e.g., SW1A 1AA">
        </div>
      </div>

      <div class="form-group" style="margin-top:12px">
        <label class="form-label">What are you looking for? * (Select all that apply)</label>
        <div id="fdChips" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
          @foreach(['Hot Tub','Swim Spa','Pool','Sauna','Outdoor Kitchen','Other'] as $lab)
          <button type="button" class="btn btn--ghost" data-chip="{{ $lab }}" style="justify-content:flex-start;gap:.5rem;">
            <span>{{ $lab }}</span>
          </button>
          @endforeach
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Preferred Timeframe</label>
        <select class="form-input" id="fdTime">
          <option value="">Select timeframe</option>
          <option>As soon as possible</option>
          <option>1–2 weeks</option>
          <option>Within 1 month</option>
          <option>Within 3 months</option>
          <option>Just looking</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Message (optional)</label>
        <textarea class="form-input" rows="4" id="fdMsg" placeholder="Tell us about your requirements, budget, or any questions..." style="resize:vertical;"></textarea>
      </div>

      <div class="ht-quote__terms">
        <input type="checkbox" id="fdTerms">
        <label for="fdTerms">I agree to the <a href="#" class="text-teal">Terms &amp; Conditions</a> and consent to Hot Tub Buyer processing my personal data. *</label>
      </div>

      <button class="ht-get-quote-btn" id="fdSubmit">Request Dealer Contact</button>
    </div>
  </div>
</section>

<section class="section section--white" style="padding-top:0;padding-bottom:2.5rem;">
  <div class="container" style="max-width:980px;">
    <div style="background:#ecfeff;border:1px solid #bae6fd;border-radius:14px;padding:22px;">
      <h3 style="margin:0 0 8px 0;text-align:center">How It Works</h3>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:8px">
        <div style="text-align:center">
          <div style="width:34px;height:34px;border-radius:999px;background:#0ea5a3;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800">1</div>
          <div style="margin-top:6px;font-weight:700">Submit Your Details</div>
          <div style="color:#6b7280;font-size:.92rem">Fill in a form with your contact information and requirements.</div>
        </div>
        <div style="text-align:center">
          <div style="width:34px;height:34px;border-radius:999px;background:#0ea5a3;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800">2</div>
          <div style="margin-top:6px;font-weight:700">We Notify Dealers</div>
          <div style="color:#6b7280;font-size:.92rem">Approved dealers near you receive your location and inquiry details.</div>
        </div>
        <div style="text-align:center">
          <div style="width:34px;height:34px;border-radius:999px;background:#0ea5a3;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800">3</div>
          <div style="margin-top:6px;font-weight:700">Get Competitive Quotes</div>
          <div style="color:#6b7280;font-size:.92rem">Up to 3 dealers will contact you with their best offers within 48 hours.</div>
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
          <div style="font-weight:800">Verified Dealers Only</div>
          <div style="opacity:.9">All dealers are approved and vetted to ensure quality service.</div>
        </div>
        <div>
          <div style="font-weight:800">Free Service</div>
          <div style="opacity:.9">No charge to customers — dealers pay to contact you.</div>
        </div>
        <div>
          <div style="font-weight:800">Maximum 3 Quotes</div>
          <div style="opacity:.9">Limited to 3 dealer responses per lead — ensures quick replies.</div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@section('scripts')
<script>
(function(){
  const chips = document.querySelectorAll('#fdChips .btn');
  chips.forEach(c=>{
    c.addEventListener('click', ()=> c.classList.toggle('btn--selected'));
  });
  document.getElementById('fdSubmit').addEventListener('click', function(){
    const name  = document.getElementById('fdName').value.trim();
    const email = document.getElementById('fdEmail').value.trim();
    const phone = document.getElementById('fdPhone').value.trim();
    const pc    = document.getElementById('fdPostcode').value.trim();
    const terms = document.getElementById('fdTerms').checked;
    const anyChip = Array.from(chips).some(c=>c.classList.contains('btn--selected'));
    if(!name){ alert('Please enter your name.'); return; }
    if(!email || !email.includes('@')){ alert('Please enter a valid email.'); return; }
    if(!phone){ alert('Please enter your phone.'); return; }
    if(!pc){ alert('Please enter your postcode.'); return; }
    if(!anyChip){ alert('Please select at least one item you are looking for.'); return; }
    if(!terms){ alert('Please agree to the Terms & Conditions.'); return; }
    window.__openEnquiryModal({
      title: 'Request Dealer Contact',
      subtitle: 'We will connect you with up to 3 local approved dealers.'
    });
  });
})();
</script>
@endsection

