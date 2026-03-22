<div class="ht-modal-overlay" id="enquiryOverlay" style="display:none;" onclick="window.__closeEnquiryModal(event)">
    <div class="ht-quote-modal" id="enquiryModal">
        <button class="ht-modal__close" onclick="window.__hideEnquiry()">&times;</button>
        <h2 class="ht-quote-modal__title" id="enquiryTitle">Request Service</h2>
        <p class="ht-quote-modal__sub" id="enquirySubtitle">Fill in your details and a certified technician will get in touch.</p>

        @auth
            @if(auth()->user()->role === 'user')
                <div id="enqAuthFields" style="margin-bottom:1.5rem;background:#f9fafb;padding:1rem;border-radius:12px;border:1px solid #e5e7eb;">
                    <p class="text-sm fw-700" style="margin-bottom:0.5rem;color:var(--gray-900)">Enquiry for:</p>
                    <div class="text-sm text-muted">{{ auth()->user()->name }}</div>
                    <div class="text-sm text-muted">{{ auth()->user()->email }}</div>
                    <div class="text-sm text-muted">{{ auth()->user()->phone ?? 'No phone' }} · {{ auth()->user()->postcode }}</div>
                    <input type="hidden" id="enqName" value="{{ auth()->user()->name }}">
                    <input type="hidden" id="enqEmail" value="{{ auth()->user()->email }}">
                    <input type="hidden" id="enqPhone" value="{{ auth()->user()->phone }}">
                    <input type="hidden" id="enqPostcode" value="{{ auth()->user()->postcode }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Additional Details</label>
                    <textarea class="form-input" rows="4" id="enqDetails" placeholder="Describe your requirements..." style="resize:vertical;"></textarea>
                </div>
                <div class="ht-quote__terms">
                    <input type="checkbox" id="enqTerms" style="margin-top:2px;accent-color:var(--teal);flex-shrink:0;width:16px;height:16px;cursor:pointer;" checked>
                    <label for="enqTerms">I agree to the <a href="{{ route('terms') }}" class="text-teal" target="_blank">Terms &amp; Conditions</a> and consent to Hot Tub Buyer processing my personal data. *</label>
                </div>
                <button class="ht-get-quote-btn" id="enqSubmit">
                    Submit Enquiry
                </button>
            @else
                <div style="padding: 2rem; text-align: center; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px; margin-bottom: 1.5rem;">
                    <p style="color: #92400e; font-weight: 600; margin-bottom: 1rem;">Only customers can submit enquiries.</p>
                    <p class="text-sm text-muted">You are currently logged in as a {{ auth()->user()->role }}.</p>
                </div>
            @endif
        @else
            <div id="enqGuestNotice" style="padding: 2rem; text-align: center; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 12px; margin-bottom: 1.5rem;">
                <p style="color: #0d9488; font-weight: 700; margin-bottom: 1rem; font-size: 1.1rem;">Please login or register as a customer to submit an enquiry.</p>
                <p class="text-sm text-muted" style="margin-bottom: 1.5rem;">Registering allows you to track your enquiries and chat directly with dealers.</p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="{{ route('login', ['redirect' => url()->current() . '?reopen_enquiry=1']) }}" class="ht-get-quote-btn" style="display: block; text-decoration: none;">
                        Login to Your Account
                    </a>
                    <a href="{{ route('register', ['redirect' => url()->current() . '?reopen_enquiry=1']) }}" class="btn btn--outline" style="display: block; text-decoration: none; border-radius: 999px; padding: 0.8rem;">
                        New Customer Registration
                    </a>
                </div>
            </div>
        @endauth
        <p style="margin-top: 1rem; font-size: 0.85rem; color: #6b7280; text-align: center; line-height: 1.4;">
            Buying a hot tub is exciting. Our platform connects you with trusted dealers who will support you from purchase to installation and long-term ownership.
        </p>
    </div>
</div>

<div class="ht-modal-overlay" id="enquirySuccessOverlay" style="display:none;">
    <div class="ht-success-modal">
        <button class="ht-modal__close" onclick="window.__hideEnquirySuccess()" style="position:absolute;top:1.25rem;right:1.25rem;">&times;</button>
        <div class="ht-success__icon">✅</div>
        <h2>Enquiry Submitted!</h2>
        <p>Thanks! A team member will contact you shortly.</p>
        <button class="ht-get-quote-btn" onclick="window.__hideEnquirySuccess()">Done</button>
    </div>
</div>

<script>
window.__openEnquiryModal = function(opts){
    const title = (opts && opts.title) ? opts.title : 'Request Service';
    const subtitle = (opts && opts.subtitle) ? opts.subtitle : 'Fill in your details and a certified technician will get in touch.';
    const initialMessage = (opts && opts.message) ? opts.message : '';

    document.getElementById('enquiryTitle').textContent = title;
    document.getElementById('enquirySubtitle').textContent = subtitle;
    if (document.getElementById('enqDetails')) {
        document.getElementById('enqDetails').value = initialMessage;
    }

    document.getElementById('enquiryOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    window.__enquiryType = (opts && opts.type) ? opts.type : 'hot_tub';
    window.__enquiryTimeframe = (opts && opts.timeframe) ? opts.timeframe : null;
    window.__enquiryProductId = (opts && opts.product_id) ? opts.product_id : null;
    
    // Store info in session for guest users
    if (window.__enquiryProductId) {
        sessionStorage.setItem('pending_enquiry_product_id', window.__enquiryProductId);
    } else {
        sessionStorage.removeItem('pending_enquiry_product_id');
    }
    sessionStorage.setItem('pending_enquiry_title', title);
    sessionStorage.setItem('pending_enquiry_type', window.__enquiryType);
    sessionStorage.setItem('pending_enquiry_message', initialMessage);
};
window.__closeEnquiryModal = function(e){
    if (!e || e.target === document.getElementById('enquiryOverlay')) window.__hideEnquiry();
};
window.__hideEnquiry = function(){
    document.getElementById('enquiryOverlay').style.display = 'none';
    document.body.style.overflow = '';
};
window.__hideEnquirySuccess = function(){
    document.getElementById('enquirySuccessOverlay').style.display = 'none';
    document.body.style.overflow = '';
};
/* ── INIT ─────────────────────────────────────────────────────────────────── */
(function(){
    // Check if we need to auto-open after redirect
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('reopen_enquiry')) {
        window.__openEnquiryModal({
            title: sessionStorage.getItem('pending_enquiry_title') || 'Request Service',
            type: sessionStorage.getItem('pending_enquiry_type') || 'hot_tub',
            product_id: sessionStorage.getItem('pending_enquiry_product_id'),
            message: sessionStorage.getItem('pending_enquiry_message') || ''
        });
    }

    const btn = document.getElementById('enqSubmit');
    if(!btn) return;
    btn.addEventListener('click', function(){
        const name  = document.getElementById('enqName').value.trim();
        const email = document.getElementById('enqEmail').value.trim();
        const phone = document.getElementById('enqPhone') ? document.getElementById('enqPhone').value.trim() : '';
        const pc    = document.getElementById('enqPostcode') ? document.getElementById('enqPostcode').value.trim() : '';
        const msg   = document.getElementById('enqDetails').value.trim();
        const terms = document.getElementById('enqTerms').checked;
        if(!name){ alert('Please enter your name.'); return; }
        if(!email || !email.includes('@')){ alert('Please enter a valid email.'); return; }
        if(!pc){ alert('Please submit postcode.'); return; }
        if(!terms){ alert('Please agree to the Terms & Conditions.'); return; }
        fetch('{{ route('enquiry.submit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                name, email, phone, postcode: pc, message: msg,
                timeframe: window.__enquiryTimeframe || null,
                type: window.__enquiryType || 'hot_tub',
                product_id: window.__enquiryProductId || sessionStorage.getItem('pending_enquiry_product_id'),
            }),
        }).then(async res => {
            const data = await res.json().catch(()=>({}));
            if(res.ok && data.ok){
                sessionStorage.removeItem('pending_enquiry_product_id');
                window.__hideEnquiry();
                const ov = document.getElementById('enquirySuccessOverlay');
                if (ov) {
                    ov.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } else {
                alert(data.msg || 'Unable to submit enquiry.');
            }
        }).catch(()=> alert('Network error. Please try again.'));
    });
})();
</script>
