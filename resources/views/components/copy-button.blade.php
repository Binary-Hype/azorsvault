@props(['value', 'label', 'text' => 'Copy'])

{{-- The label flips to "Copied" for 1.6s; the swap itself lives in resources/js/landing.js. --}}
<button type="button" data-copy="{{ $value }}" aria-label="{{ $label }}" {{ $attributes }}>
    <span class="codex-flip">
        <span class="codex-flip-face">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="8" height="8" rx="1.2"/><path d="M2 9V3a1 1 0 0 1 1-1h6"/></svg>
            <span>{{ $text }}</span>
        </span>
        <span class="codex-flip-face codex-flip-face-back">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7.5l3 3 7-7"/></svg>
            <span>Copied</span>
        </span>
    </span>
</button>
