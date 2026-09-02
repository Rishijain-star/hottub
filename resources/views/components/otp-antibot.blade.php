@php
    $turnstileKey = config('services.turnstile.site_key');
    $turnstileAppearance = config('services.turnstile.appearance', 'always');
    $turnstileSize = config('services.turnstile.size', 'normal');
    $widgetId = 'cf-turnstile-' . bin2hex(random_bytes(4));
@endphp
@if($turnstileKey && config('services.turnstile.enabled', true) && ! app(\App\Services\GeoRestrictionService::class)->skipsChecksOnLocal())
    <div class="cf-turnstile-wrap" style="margin:1rem 0;">
        <div id="{{ $widgetId }}" class="cf-turnstile-target"></div>
        <p id="{{ $widgetId }}-error" class="cf-turnstile-error" style="display:none;margin-top:8px;color:#b91c1c;font-size:0.85rem;line-height:1.4;"></p>
    </div>
    @once
        @push('head')
        <link rel="preconnect" href="https://challenges.cloudflare.com" crossorigin>
        @endpush
        @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" defer></script>
        @endpush
    @endonce
    @push('scripts')
    <script>
    (function () {
        var widgetId = @json($widgetId);
        var siteKey = @json($turnstileKey);
        var appearance = @json($turnstileAppearance);
        var size = @json($turnstileSize);
        var attempts = 0;

        function showError(message) {
            var el = document.getElementById(widgetId + '-error');
            if (el) {
                el.textContent = message;
                el.style.display = 'block';
            }
        }

        function renderWidget() {
            if (!window.turnstile || typeof window.turnstile.render !== 'function') {
                attempts += 1;
                if (attempts < 50) {
                    setTimeout(renderWidget, 200);
                    return;
                }
                showError('Cloudflare security could not load. Refresh the page, disable VPN/ad-blockers, or try another network.');
                return;
            }

            try {
                window.turnstile.render('#' + widgetId, {
                    sitekey: siteKey,
                    theme: 'light',
                    appearance: appearance,
                    size: size,
                    retry: 'auto',
                    'refresh-expired': 'auto',
                });
            } catch (e) {
                showError('Cloudflare security could not start. Please refresh the page.');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderWidget);
        } else {
            renderWidget();
        }
    })();
    </script>
    @endpush
@endif

<div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none;">
    <label for="company_website">Leave blank</label>
    <input type="text" name="{{ config('services.turnstile.honeypot_field', 'company_website') }}" id="company_website" tabindex="-1" autocomplete="off" value="">
</div>
<input type="hidden" name="client_fp" id="clientFp" value="">
<input type="hidden" name="client_hw_fp" id="clientHwFp" value="">
<input type="hidden" name="client_pid" id="clientPid" value="">

@once
@push('scripts')
<script>
(function () {
    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function setCookie(name, value, days) {
        var maxAge = days * 86400;
        var secure = location.protocol === 'https:' ? ';Secure' : '';
        document.cookie = name + '=' + encodeURIComponent(value) + ';path=/;max-age=' + maxAge + ';SameSite=Lax' + secure;
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function hardwareProfile() {
        return [
            (screen && screen.width ? screen.width : ''),
            (screen && screen.height ? screen.height : ''),
            (screen && screen.colorDepth ? screen.colorDepth : ''),
            String(new Date().getTimezoneOffset()),
            navigator.hardwareConcurrency || '',
            navigator.maxTouchPoints || 0,
            navigator.deviceMemory || ''
        ].join('|');
    }

    function initDeviceSignals() {
        var pid = localStorage.getItem('htb_pid') || getCookie('htb_pid');
        if (!pid) {
            pid = uuid();
            localStorage.setItem('htb_pid', pid);
        }
        setCookie('htb_pid', pid, 365);

        var hw = hardwareProfile();
        setCookie('htb_hw', hw, 365);

        var fpEl = document.getElementById('clientFp');
        if (fpEl && !fpEl.value) {
            try {
                var parts = [
                    navigator.userAgent || '',
                    navigator.language || '',
                    hw,
                    navigator.platform || ''
                ];
                fpEl.value = btoa(unescape(encodeURIComponent(parts.join('|')))).slice(0, 180);
            } catch (e) {}
        }

        var hwEl = document.getElementById('clientHwFp');
        if (hwEl) hwEl.value = hw;

        var pidEl = document.getElementById('clientPid');
        if (pidEl) pidEl.value = pid;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeviceSignals);
    } else {
        initDeviceSignals();
    }
})();
</script>
@endpush
@endonce
