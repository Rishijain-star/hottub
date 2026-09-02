(function () {
    'use strict';

    var targetLang = document.documentElement.getAttribute('data-google-translate') || '';
    if (!targetLang || !document.getElementById('google_translate_element')) {
        return;
    }

    window.googleTranslateElementInit = function () {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            autoDisplay: false,
        }, 'google_translate_element');
    };

    var script = document.createElement('script');
    script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    script.defer = true;
    document.body.appendChild(script);
})();
