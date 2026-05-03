@php
    $rawText = $text ?? '';
    $cleanText = trim(strip_tags((string) $rawText));
    $lineCount = isset($lines) ? (int) $lines : 3;
@endphp

@if($cleanText !== '')
    <div class="expandable-text" data-expandable-text>
        <p class="card-description-clamp is-collapsed {{ $class ?? '' }}"
           data-expandable-content
           style="--line-clamp: {{ max(1, $lineCount) }};">
            {{ $cleanText }}
        </p>
        <button type="button"
                class="expandable-text__toggle"
                data-expandable-toggle
                aria-expanded="false">
            Learn More
        </button>
    </div>
@endif

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.__expandableTextInitialized) {
        return;
    }
    window.__expandableTextInitialized = true;

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-expandable-toggle]');
        if (!btn) {
            return;
        }

        const wrapper = btn.closest('[data-expandable-text]');
        const content = wrapper ? wrapper.querySelector('[data-expandable-content]') : null;
        if (!wrapper || !content) {
            return;
        }

        const isExpanded = content.classList.toggle('is-expanded');
        content.classList.toggle('is-collapsed', !isExpanded);
        btn.textContent = isExpanded ? 'Show Less' : 'Learn More';
        btn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    });
});
</script>
@endonce
