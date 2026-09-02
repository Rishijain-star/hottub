@if(config('localization.google_translate.enabled', true))
<script>
(function () {
    var targetLang = document.documentElement.getAttribute('data-google-translate') || '';

    function getCookie(name) {
        var escaped = name.replace(/[$()*+./?[\\\]^{|}-]/g, '\\$&');
        var match = document.cookie.match(new RegExp('(?:^|; )' + escaped + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function expireCookie(name, domain) {
        var base = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
        document.cookie = base;
        if (domain) {
            document.cookie = base + '; domain=' + domain;
        }
    }

    function clearGoogTransCookies() {
        expireCookie('googtrans', null);
        var host = window.location.hostname;
        expireCookie('googtrans', host);
        var parts = host.split('.');
        if (parts.length > 1 && host !== 'localhost' && host !== '127.0.0.1') {
            expireCookie('googtrans', '.' + parts.slice(-2).join('.'));
        }
    }

    function setGoogTrans(lang) {
        var val = '/auto/' + lang;
        document.cookie = 'googtrans=' + val + '; path=/';
        var host = window.location.hostname;
        if (host !== 'localhost' && host !== '127.0.0.1') {
            var parts = host.split('.');
            if (parts.length > 1) {
                document.cookie = 'googtrans=' + val + '; path=/; domain=.' + parts.slice(-2).join('.');
            }
        }
    }

    var current = getCookie('googtrans');
    var desired = targetLang ? '/auto/' + targetLang : '';

    if (!targetLang) {
        if (current) {
            clearGoogTransCookies();
            window.location.reload();
        }
        return;
    }

    if (current !== desired) {
        setGoogTrans(targetLang);
        window.location.reload();
    }
})();
</script>
@endif
