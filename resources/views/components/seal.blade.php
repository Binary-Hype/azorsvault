@props(['detailed' => false, 'stroke' => 1.4])

{{-- The vault's ward: a gilt ring around a drop of mana. Matches public/logo.svg. --}}
<svg viewBox="0 0 168 168" fill="none" aria-hidden="true" {{ $attributes }}>
    <circle cx="84" cy="84" r="80" stroke="currentColor" stroke-width="{{ $stroke }}" class="text-gold" opacity="0.65"/>

    @if ($detailed)
        <circle cx="84" cy="84" r="72" stroke="currentColor" stroke-width="{{ $stroke * 0.7 }}" class="text-gold" opacity="0.32"/>
        <circle cx="84" cy="84" r="56" stroke="currentColor" stroke-width="{{ $stroke * 0.8 }}" class="text-accent" opacity="0.4" stroke-dasharray="2 8"/>
        <g stroke="currentColor" stroke-width="{{ $stroke }}" class="text-gold" opacity="0.75" stroke-linecap="round">
            <path d="M84 4 v10"/>
            <path d="M84 154 v10"/>
            <path d="M4 84 h10"/>
            <path d="M154 84 h10"/>
            <path d="M27 27 l7 7"/>
            <path d="M134 134 l7 7"/>
            <path d="M141 27 l-7 7"/>
            <path d="M34 134 l-7 7"/>
        </g>
    @endif

    <path d="M84 30 C 68 60, 56 72, 56 88 a 28 28 0 0 0 56 0 c 0 -16 -12 -28 -28 -58 z" class="text-accent" fill="currentColor" opacity="0.95"/>
    <path d="M70 84 C 70 96, 76 104, 84 106" stroke="rgba(255,255,255,0.55)" stroke-width="2.4" stroke-linecap="round" fill="none"/>
</svg>
