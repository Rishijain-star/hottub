{{-- ============================================================
     resources/views/layouts/footer.blade.php
     Hot Tub Buyer — Footer
     ============================================================ --}}

<footer class="footer">
    <div class="footer__body">
        <div class="footer__grid">

            {{-- ── Col 1: Brand ── --}}
            <div class="footer__col">
                <div class="footer__logo-row">
                    <div class="footer__logo-box">
                        <svg viewBox="0 0 24 24" fill="none" width="22" height="22">
                            <path d="M12 2C12 2 5 9.5 5 14a7 7 0 0014 0C19 9.5 12 2 12 2Z" fill="white"/>
                            <path d="M9 16c0 1.657 1.343 3 3 3" stroke="rgba(0,160,150,0.5)" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <p class="footer__brand-name">Hot Tub <span style="font-weight:800;color:var(--teal-lt)">Buyer</span></p>
                        <p class="footer__brand-sub">{{ __('footer.brand_sub') }}</p>
                    </div>
                </div>
                <p class="footer__desc">{{ __('footer.desc') }}</p>
                <p class="footer__social-label">{{ __('footer.social_label') }}</p>
                @php
                    $social = $siteSocialLinks ?? ['facebook'=>null,'twitter'=>null,'instagram'=>null,'tiktok'=>null];
                @endphp
                <div class="footer__socials">
                    <a href="{{ $social['facebook'] ?: '#' }}" class="social-btn" aria-label="Facebook" @if($social['facebook']) target="_blank" rel="noopener noreferrer" @endif>f</a>
                    <a href="{{ $social['twitter'] ?: '#' }}" class="social-btn" aria-label="X / Twitter" @if($social['twitter']) target="_blank" rel="noopener noreferrer" @endif>𝕏</a>
                    <a href="{{ $social['instagram'] ?: '#' }}" class="social-btn" aria-label="Instagram" @if($social['instagram']) target="_blank" rel="noopener noreferrer" @endif>
                        <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                            <path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4c0 3.2-2.6 5.8-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8C2 4.6 4.6 2 7.8 2Zm-.2 2A3.6 3.6 0 0 0 4 7.6v8.8A3.6 3.6 0 0 0 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6A3.6 3.6 0 0 0 16.4 4H7.6Zm9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5ZM12 7a5 5 0 1 1 0 10A5 5 0 0 1 12 7Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/>
                        </svg>
                    </a>
                    <a href="{{ $social['tiktok'] ?: '#' }}" class="social-btn" aria-label="TikTok" @if($social['tiktok']) target="_blank" rel="noopener noreferrer" @endif>
                        <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.75a4.85 4.85 0 0 1-1.01-.06Z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- ── Col 2: Quick Links ── --}}
            <div class="footer__col">
                <h5>{{ __('footer.quick_links') }}</h5>
                <ul class="footer__links">
                    <li><a href="{{ route('home') }}">{{ __('footer.home') }}</a></li>
                    <li><a href="{{ route('hot-tubs') }}">{{ __('footer.hot_tubs') }}</a></li>
                    <li><a href="{{ route('swim-spas') }}">{{ __('footer.swim_spas') }}</a></li>
                    <li><a href="{{ route('services') }}">{{ __('footer.services') }}</a></li>
                    <li><a href="{{ route('parts') }}">{{ __('footer.parts') }}</a></li>
                </ul>
            </div>

            {{-- ── Col 3: Explore ── --}}
            <div class="footer__col">
                <h5>{{ __('footer.explore') }}</h5>
                <ul class="footer__links">
                    <li><a href="{{ route('brands') }}">{{ __('footer.brands') }}</a></li>
                    <li><a href="{{ route('find-dealer') }}">{{ __('footer.find_dealer') }}</a></li>
                    <li><a href="{{ route('care-guide') }}">{{ __('footer.care_guide') }}</a></li>
                    <li><a href="{{ route('faq') }}">{{ __('footer.faq') }}</a></li>
                </ul>
            </div>

            {{-- ── Col 4: Account ── --}}
            <div class="footer__col">
                <h5>{{ __('footer.account') }}</h5>
                <ul class="footer__links">
                    <li><a href="{{ route('login') }}">{{ __('footer.login') }}</a></li>
                    <li><a href="{{ route('register') }}">{{ __('footer.register') }}</a></li>
                    <li><a href="{{ route('dashboard') }}">{{ __('footer.dashboard') }}</a></li>
                </ul>
            </div>

            {{-- ── Col 5: Legal ── --}}
            <div class="footer__col">
                <h5>{{ __('footer.legal') }}</h5>
                <ul class="footer__links">
                    <li><a href="{{ route('privacy') }}">{{ __('footer.privacy') }}</a></li>
                    <li><a href="{{ route('terms') }}">{{ __('footer.terms') }}</a></li>
                    <li><a href="{{ route('dealer-agreement') }}">{{ __('footer.dealer_agreement') }}</a></li>
                    <li><a href="{{ route('faq') }}">{{ __('footer.support_faq') }}</a></li>
                </ul>
            </div>

        </div>
    </div>

    @php
        $biz = $siteBusinessDetails ?? ['company_name'=>'Hot Tub Buyer Ltd','vat_number'=>null,'company_number'=>null,'fca_number'=>null];
        $bizBits = [];
        if (!empty($biz['vat_number'])) $bizBits[] = __('footer.vat_no', ['number' => $biz['vat_number']]);
        if (!empty($biz['company_number'])) $bizBits[] = __('footer.company_no', ['number' => $biz['company_number']]);
        if (!empty($biz['fca_number'])) $bizBits[] = __('footer.fca_no', ['number' => $biz['fca_number']]);
    @endphp

    @if(!empty($bizBits))
        <div class="footer__reg-strip">
            <div class="footer__reg-inner">
                <div class="footer__reg-heading">{{ __('footer.reg_heading') }}</div>
                <div class="footer__reg-chips">
                    @if(!empty($biz['vat_number']))
                        <div class="footer__reg-chip">
                            <span class="footer__reg-chip-label">{{ __('footer.vat_label') }}</span>
                            <span class="footer__reg-chip-value">{{ $biz['vat_number'] }}</span>
                        </div>
                    @endif
                    @if(!empty($biz['company_number']))
                        <div class="footer__reg-chip">
                            <span class="footer__reg-chip-label">{{ __('footer.company_no_label') }}</span>
                            <span class="footer__reg-chip-value">{{ $biz['company_number'] }}</span>
                        </div>
                    @endif
                    @if(!empty($biz['fca_number']))
                        <div class="footer__reg-chip">
                            <span class="footer__reg-chip-label">{{ __('footer.fca_label') }}</span>
                            <span class="footer__reg-chip-value">{{ $biz['fca_number'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── Bottom Bar ── --}}
    <div class="footer__bottom">
        <p class="footer__copy">{{ __('footer.copyright', ['year' => date('Y'), 'company' => $biz['company_name'] ?? 'Hot Tub Buyer']) }}</p>
        <p class="footer__credit">
            <a href="mailto:support@hottubbuyer.co.uk">{{ __('footer.contact_support') }}</a>
        </p>
    </div>
</footer>
