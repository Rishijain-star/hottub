@php
    $captchaUrl = app(\App\Services\ImageCaptchaService::class)->issue(request());
@endphp
<div class="form-group" style="margin:1rem 0;">
    <label class="form-label" for="image_captcha_code">Enter the 6-digit code from the image</label>
    <div style="display:flex;flex-direction:column;gap:10px;align-items:flex-start;">
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <img
                src="{{ $captchaUrl }}"
                alt="Security code"
                id="captchaImage"
                width="240"
                height="80"
                style="border:1px solid #e5e7eb;border-radius:8px;background:#fff;display:block;"
            >
            <button type="button" class="btn btn--ghost btn--sm" id="captchaRefreshBtn" style="white-space:nowrap;">New image</button>
        </div>
        <input
            class="form-input auth-input"
            type="text"
            id="image_captcha_code"
            name="image_captcha_code"
            inputmode="numeric"
            pattern="[0-9]{6}"
            maxlength="6"
            minlength="6"
            required
            autocomplete="off"
            placeholder="000000"
            style="max-width:180px;letter-spacing:0.25em;font-weight:700;"
        >
    </div>
</div>
@once
@push('scripts')
<script>
(function () {
    const btn = document.getElementById('captchaRefreshBtn');
    const img = document.getElementById('captchaImage');
    if (!btn || !img) return;
    btn.addEventListener('click', function () {
        btn.disabled = true;
        fetch('{{ route('captcha.refresh') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.url) img.src = data.url;
            })
            .finally(function () { btn.disabled = false; });
    });
})();
</script>
@endpush
@endonce
