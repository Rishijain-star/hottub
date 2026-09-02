@once
@push('scripts')
<script>
(function () {
    const refreshUrl = @json(route('csrf.refresh'));

    async function refreshCsrfToken() {
        try {
            const res = await fetch(refreshUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            if (!data || !data.token) {
                return;
            }
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', data.token);
            }
            document.querySelectorAll('input[name="_token"]').forEach(function (input) {
                input.value = data.token;
            });
        } catch (e) {}
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            refreshCsrfToken();
        }
    });

    window.addEventListener('focus', refreshCsrfToken);
    setInterval(refreshCsrfToken, 4 * 60 * 1000);
    refreshCsrfToken();
})();
</script>
@endpush
@endonce
