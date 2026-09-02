@if(session('locale_manual'))
@else
<script>
(function () {
    if (sessionStorage.getItem('geo_locator_done') === '1') {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    if (!token) {
        return;
    }

    function postLocation(body) {
        return fetch('{{ route('location.preference') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
            credentials: 'same-origin',
        }).then(function (res) { return res.ok ? res.json() : null; });
    }

    function markUnavailable() {
        sessionStorage.setItem('geo_locator_done', '1');
        postLocation({ unavailable: true }).catch(function () {});
    }

    if (!navigator.geolocation) {
        markUnavailable();
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            postLocation({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            })
                .then(function (data) {
                    if (!data || !data.ok || data.unavailable) {
                        return;
                    }
                    sessionStorage.setItem('geo_locator_done', '1');
                    const prev = sessionStorage.getItem('geo_applied_locale');
                    if (prev !== data.locale || !prev) {
                        sessionStorage.setItem('geo_applied_locale', data.locale);
                        window.location.reload();
                    }
                })
                .catch(function () {
                    markUnavailable();
                });
        },
        function () {
            markUnavailable();
        },
        { enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 }
    );
})();
</script>
@endif
