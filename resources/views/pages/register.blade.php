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

        {{-- Role selector --}}
        <div class="reg-role-wrap">
            <p class="reg-role__label">I am a:</p>
            <div class="reg-role-grid">
                <button type="button" class="reg-role-btn reg-role-btn--active" data-role="customer" onclick="selectRole('customer')">
                    <span class="reg-role-btn__title">Customer</span>
                    <span class="reg-role-btn__sub">Looking for a hot tub</span>
                </button>
                <button type="button" class="reg-role-btn" data-role="dealer" onclick="selectRole('dealer')">
                    <span class="reg-role-btn__title">Dealer</span>
                    <span class="reg-role-btn__sub">Sell hot tubs</span>
                </button>
                <button type="button" class="reg-role-btn" data-role="manufacturer" onclick="selectRole('manufacturer')">
                    <span class="reg-role-btn__title">Manufacturer</span>
                    <span class="reg-role-btn__sub">Hot tub brand</span>
                </button>
            </div>
        </div>

        <script>
            // Set initial state on page load
            document.addEventListener('DOMContentLoaded', () => {
                selectRole('customer');
            });
        </script>

        {{-- Form --}}
        <form class="auth-form" method="POST" action="/register" onsubmit="return handleRegister(event)">
            @csrf
            <input type="hidden" name="role" id="roleInput" value="customer">
            @if(request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

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

            {{-- Postcode + Phone row --}}
            <div class="reg-row">
                <div class="form-group">
                    <label class="form-label" for="postcode">Postcode *</label>
                    <input class="form-input auth-input" type="text" id="postcode" name="postcode" placeholder="e.g. SW1A 1AA" value="{{ old('postcode') }}" required>
                    @error('postcode') <span class="auth-field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input class="form-input auth-input" type="text" id="phone" name="phone" placeholder="Your contact number" value="{{ old('phone') }}">
                    @error('phone') <span class="auth-field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Extra fields for Dealer/Manufacturer --}}
            <div id="businessExtraRow" style="display: none; margin-bottom: 1.5rem;">
                <div class="reg-row">
                    <div class="form-group">
                        <label class="form-label" for="company_number">Company Number</label>
                        <input class="form-input auth-input" type="text" id="company_number" name="company_number" placeholder="Your company number" value="{{ old('company_number') }}">
                        @error('company_number') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="vat_number">VAT Number</label>
                        <input class="form-input auth-input" type="text" id="vat_number" name="vat_number" placeholder="Your VAT number" value="{{ old('vat_number') }}">
                        @error('vat_number') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <input class="form-input auth-input" type="text" id="address" name="address" placeholder="Your address" value="{{ old('address') }}">
                    @error('address') <span class="auth-field-error">{{ $message }}</span> @enderror
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
                Create Account
            </button>
            <p id="enquiryNotice" style="margin-top: 1.25rem; font-size: 0.85rem; color: #6b7280; text-align: center; line-height: 1.4; display: none;">
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
/* Role selector */
function selectRole(role) {
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.reg-role-btn').forEach(btn => {
        btn.classList.toggle('reg-role-btn--active', btn.dataset.role === role);
    });

    const businessExtraRow = document.getElementById('businessExtraRow');
    const postcodeLabel = document.querySelector('label[for="postcode"]');
    const enquiryNotice = document.getElementById('enquiryNotice');
    const termsLabel = document.getElementById('termsLabel');

    if (role === 'customer') {
        businessExtraRow.style.display = 'none';
        postcodeLabel.textContent = 'Postcode *';
        enquiryNotice.style.display = 'block';
        termsLabel.innerHTML = 'I agree to the <a href="{{ route('privacy') }}" target="_blank" style="color:var(--teal);font-weight:600;text-decoration:underline;">Privacy Policy</a>';
    } else {
        businessExtraRow.style.display = 'block';
        postcodeLabel.textContent = 'Business Postcode *';
        enquiryNotice.style.display = 'none';
        termsLabel.innerHTML = 'I agree to the <a href="{{ route('terms') }}" target="_blank" style="color:var(--teal);font-weight:600;text-decoration:underline;">Terms &amp; Conditions</a>, <a href="{{ route('privacy') }}" target="_blank" style="color:var(--teal);font-weight:600;text-decoration:underline;">Privacy Policy</a> and <a href="{{ route('dealer-agreement') }}" target="_blank" style="color:var(--teal);font-weight:600;text-decoration:underline;">Dealer Agreement</a>';
    }
}

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

/* Submit loading state */
function handleRegister(e) {
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
    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.innerHTML = `
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin .8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Creating Account…`;
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = `
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            Create Account`;
    }, 8000);
    return true;
}
</script>

@endsection