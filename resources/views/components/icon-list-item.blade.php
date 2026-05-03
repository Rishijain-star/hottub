@php
    $itemType = $type ?? 'check';
    $text = trim((string) ($text ?? ''));
    $isCross = $itemType === 'cons' || $itemType === 'cross';
    $iconColor = $isCross ? '#dc2626' : 'var(--teal)';
@endphp

@if($text !== '')
<li style="display:flex;align-items:flex-start;gap:8px;margin-bottom:6px">
    @if($isCross)
    <svg width="15" height="15" fill="none" stroke="{{ $iconColor }}" stroke-width="2.5" viewBox="0 0 24 24" style="margin-top:2px;flex:0 0 auto">
        <circle cx="12" cy="12" r="9"></circle>
        <path d="M9 9l6 6M15 9l-6 6" stroke-linecap="round"></path>
    </svg>
    @else
    <svg width="15" height="15" fill="none" stroke="{{ $iconColor }}" stroke-width="2.5" viewBox="0 0 24 24" style="margin-top:2px;flex:0 0 auto">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    @endif
    <span>{{ $text }}</span>
</li>
@endif
