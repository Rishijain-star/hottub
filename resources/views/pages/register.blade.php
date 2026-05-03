@extends('layouts.app')
@section('title', 'Create Account – Hot Tub Buyer')
@section('content')

<div class="auth-page">
    <div class="auth-card" style="max-width:580px;">

        {{-- Icon --}}
        <div class="auth-card__icon">
            <svg width="26" height="26" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
        </div>

        {{-- Heading --}}
        <h1 class="auth-card__title">Create Account</h1>
        <p class="auth-card__sub">Join Hot Tub Buyer today</p>

        <p style="padding:12px 14px;background:#f0fdfa;border:1px solid #99f6e4;border-radius:10px;font-size:0.88rem;color:#134e4a;margin-bottom:1.25rem;line-height:1.5">
            Please ensure all details are accurate. This platform is designed for long-term ownership and service tracking.
        </p>
        @php($otpPending = session('otp_pending') || session()->has('registration_otp'))
        <p class="text-sm text-muted" style="margin-bottom:1rem">Choose account type: Customer, Dealer, or Manufacturer.</p>

        {{-- Form --}}
        <form class="auth-form" method="POST" action="/register" onsubmit="return handleRegister(event)">
            @csrf
            <input type="hidden" name="registration_step" id="registrationStep" value="{{ $otpPending ? 'verify_otp' : 'request_otp' }}">
            @if(request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

            <div class="form-group">
                <label class="form-label">Register as</label>
                <div style="display:flex;gap:14px;flex-wrap:wrap">
                    @php($selectedRole = old('role', 'customer'))
                    <label class="form-check" style="display:flex;align-items:center;gap:6px">
                        <input type="radio" name="role" value="customer" {{ $selectedRole === 'customer' ? 'checked' : '' }}> Customer
                    </label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px">
                        <input type="radio" name="role" value="dealer" {{ $selectedRole === 'dealer' ? 'checked' : '' }}> Dealer
                    </label>
                    <label class="form-check" style="display:flex;align-items:center;gap:6px">
                        <input type="radio" name="role" value="manufacturer" {{ $selectedRole === 'manufacturer' ? 'checked' : '' }}> Manufacturer
                    </label>
                </div>
            </div>

            {{-- Name + Email row --}}
            <div class="reg-row">
                <div class="form-group">
                    <label class="form-label" for="name">Name *</label>
                    <input
                        class="form-input auth-input"
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Your full name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        required
                    >
                    @error('name')
                        <span class="auth-field-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input
                        class="form-input auth-input"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                    >
                    @error('email')
                        <span class="auth-field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group" id="otpBlock" style="{{ $otpPending ? '' : 'display:none;' }}">
                <label class="form-label" for="code">OTP Verification Code *</label>
                <input class="form-input auth-input" type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6-digit OTP">
                @error('code')
                    <span class="auth-field-error">{{ $message }}</span>
                @enderror
                @if(session('dev_registration_otp_code'))
                    <div class="alert alert--success mt-2">Dev OTP: <strong>{{ session('dev_registration_otp_code') }}</strong></div>
                @endif
                <div style="margin-top:.6rem">
                    <button type="button" class="btn btn--ghost btn--sm" id="resendOtpBtn">Resend OTP</button>
                </div>
            </div>

            {{-- Mobile + Postcode (dealer/manufacturer & customer) --}}
            <div class="reg-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Mobile Number *</label>
                    <input class="form-input auth-input" type="text" id="phone" name="phone" placeholder="For SMS verification" value="{{ old('phone') }}" required autocomplete="tel">
                    @error('phone') <span class="auth-field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="postcode">Postcode *</label>
                    <input class="form-input auth-input" type="text" id="postcode" name="postcode" placeholder="e.g. SW1A 1AA" value="{{ old('postcode') }}" required autocomplete="postal-code">
                    @error('postcode') <span class="auth-field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Dealer / Manufacturer: optional company details --}}
            <div id="partnerOptionalFields" style="display:none;margin-bottom:1.25rem;">
                <p class="text-sm text-muted" style="margin:0 0 0.75rem;font-size:0.85rem;">Dealer &amp; manufacturer: company details (optional)</p>
                <div class="reg-row">
                    <div class="form-group">
                        <label class="form-label" for="vat_number">VAT Number</label>
                        <input class="form-input auth-input" type="text" id="vat_number" name="vat_number" placeholder="e.g. GB123456789" value="{{ old('vat_number') }}" maxlength="255" autocomplete="off">
                        @error('vat_number') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_number">Company Registration Number</label>
                        <input class="form-input auth-input" type="text" id="company_number" name="company_number" placeholder="e.g. 12345678" value="{{ old('company_number') }}" maxlength="255" autocomplete="off">
                        @error('company_number') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Password + Confirm row --}}
            <div class="reg-row" style="margin-bottom:1.75rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="password">Password *</label>
                    <div class="auth-pw-wrap">
                        <input
                            class="form-input auth-input"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimum 8 characters"
                            autocomplete="new-password"
                            required
                            minlength="8"
                        >
                        <button type="button" class="auth-pw-toggle" onclick="togglePw('password','eyeShow1','eyeHide1')" tabindex="-1">
                            <svg id="eyeShow1" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeHide1" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="auth-field-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="password_confirmation">Confirm Password *</label>
                    <div class="auth-pw-wrap">
                        <input
                            class="form-input auth-input"
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Re-enter password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="auth-pw-toggle" onclick="togglePw('password_confirmation','eyeShow2','eyeHide2')" tabindex="-1">
                            <svg id="eyeShow2" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeHide2" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Password strength indicator --}}
            <div class="reg-pw-strength" id="pwStrength" style="display:none;">
                <div class="reg-pw-strength__bars">
                    <span class="reg-pw-bar" id="bar1"></span>
                    <span class="reg-pw-bar" id="bar2"></span>
                    <span class="reg-pw-bar" id="bar3"></span>
                    <span class="reg-pw-bar" id="bar4"></span>
                </div>
                <span class="reg-pw-strength__label" id="pwLabel">Weak</span>
            </div>

            {{-- Error alert --}}
            @if(session('error'))
                <div class="alert alert--danger" style="margin-bottom:1.25rem;">{{ session('error') }}</div>
            @endif

            {{-- Terms --}}
            <div class="ht-quote__terms" style="margin-bottom:1.5rem;">
                <input type="checkbox" id="regTerms" name="terms" style="margin-top:2px;accent-color:var(--teal);flex-shrink:0;width:16px;height:16px;cursor:pointer;" required>
                <label for="regTerms" id="termsLabel" style="font-size:.85rem;color:var(--gray-700);">
                    {{-- Dynamically updated via JS --}}
                </label>
            </div>

            <button type="submit" class="auth-submit-btn" id="registerBtn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                {{ $otpPending ? 'Verify OTP & Create Account' : 'Create Account' }}
            </button>
            <p id="enquiryNotice" style="margin-top: 1.25rem; font-size: 0.85rem; color: #6b7280; text-align: center; line-height: 1.4;">
                Buying a hot tub is exciting. Our platform connects you with trusted dealers who will support you from purchase to installation and long-term ownership.
            </p>
        </form>

        {{-- Footer --}}
        <p class="auth-card__footer-link">
            Already have an account? <a href="/login">Sign in here</a>
        </p>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const termsLabel = document.getElementById('termsLabel');
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const otpBlock = document.getElementById('otpBlock');
    const registrationStep = document.getElementById('registrationStep');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const partnerOptionalFields = document.getElementById('partnerOptionalFields');

    function updateTermsAndStep() {
        const selectedRole = document.querySelector('input[name="role"]:checked')?.value || 'customer';
        const isPartner = selectedRole === 'dealer' || selectedRole === 'manufacturer';
        const hasOtpBlockVisible = otpBlock && otpBlock.style.display !== 'none';

        if (partnerOptionalFields) {
            partnerOptionalFields.style.display = isPartner ? 'block' : 'none';
            partnerOptionalFields.querySelectorAll('input').forEach(function (inp) {
                inp.disabled = !isPartner;
            });
        }

        if (termsLabel) {
            if (isPartner) {
                termsLabel.innerHTML = 'I agree to the <a href="{{ route('privacy') }}" target="_blank" style="color:var(--teal);font-weight:600;text-decoration:underline;">Privacy Policy</a> and understand my account requires admin approval after OTP verification.';
            } else {
                termsLabel.innerHTML = 'I agree to the <a href="{{ route('privacy') }}" target="_blank" style="color:var(--teal);font-weight:600;text-decoration:underline;">Privacy Policy</a>';
            }
        }

        if (registrationStep) {
            registrationStep.value = hasOtpBlockVisible && isPartner ? 'verify_otp' : 'request_otp';
        }
    }

    roleInputs.forEach(input => input.addEventListener('change', updateTermsAndStep));
    updateTermsAndStep();

    if (resendOtpBtn) {
        let resendBusy = false;
        resendOtpBtn.addEventListener('click', function () {
            if (resendBusy) {
                return;
            }
            resendBusy = true;
            resendOtpBtn.disabled = true;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('register.otp.resend') }}";

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            document.body.appendChild(form);
            form.submit();
        });
    }
});

/* Password show/hide */
function togglePw(inputId, showId, hideId) {
    const input = document.getElementById(inputId);
    const show  = document.getElementById(showId);
    const hide  = document.getElementById(hideId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    show.style.display = isHidden ? 'none'  : 'block';
    hide.style.display = isHidden ? 'block' : 'none';
}

/* Password strength */
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    const wrap = document.getElementById('pwStrength');
    const label = document.getElementById('pwLabel');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'flex';

    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
    const labels = ['Weak','Fair','Good','Strong'];

    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('bar' + i);
        bar.style.background = i <= score ? colors[score - 1] : 'var(--gray-200)';
    }
    label.textContent  = labels[score - 1] || 'Weak';
    label.style.color  = colors[score - 1] || '#ef4444';
});

/* Block double-submit: two POSTs reuse one CSRF token → second request gets 419 */
let registerFormSubmitting = false;

function handleRegister(e) {
    if (registerFormSubmitting) {
        e.preventDefault();
        return false;
    }

    const selectedRole = document.querySelector('input[name="role"]:checked')?.value || 'customer';
    const step = document.getElementById('registrationStep')?.value || 'request_otp';
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('password_confirmation').value;
    if (pw !== cpw) {
        alert('Passwords do not match. Please check and try again.');
        return false;
    }
    if (pw.length < 8) {
        alert('Password must be at least 8 characters.');
        return false;
    }
    if ((selectedRole === 'dealer' || selectedRole === 'manufacturer') && step === 'verify_otp') {
        const code = document.getElementById('code')?.value?.trim();
        if (!code || code.length !== 6) {
            alert('Please enter the 6-digit OTP code.');
            return false;
        }
    }

    registerFormSubmitting = true;

    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.innerHTML = `
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin .8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Creating Account…`;
    return true;
}
</script>

@endsection