@if(config('localization.google_translate.enabled', true) && filled($googleTranslateLang))
    <div id="google_translate_element" class="google-translate-host" aria-hidden="true"></div>
    @php
        $gtScriptPath = public_path('js/google-translate.js');
        $gtScriptVersion = file_exists($gtScriptPath) ? filemtime($gtScriptPath) : null;
    @endphp
    <script src="{{ asset('js/google-translate.js') }}{{ $gtScriptVersion ? ('?v=' . $gtScriptVersion) : '' }}" defer></script>
@endif
