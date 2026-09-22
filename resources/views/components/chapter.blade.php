@props(['numeral', 'name', 'heading', 'wide' => false])

{{-- Chapter head: gilt numeral and name, a display-face heading, and a lede. --}}
<div @class([
    'mx-auto mb-8 sm:mb-13 text-center flex flex-col items-center gap-2 sm:gap-3',
    'max-w-[860px]' => $wide,
    'max-w-[820px]' => ! $wide,
])>
    <div class="font-mono text-[10px] sm:text-[11.5px] tracking-[0.26em] uppercase text-gold/85">{{ $numeral }} · {{ $name }}</div>
    <h2 class="font-display text-[60px] sm:text-[82px] leading-[0.95] sm:leading-none m-0 text-[#f3ecd9]">{{ $heading }}</h2>
    <p class="m-0 sm:mt-1.5 text-[17px] sm:text-[19px] leading-[1.5] sm:leading-[1.6] text-parchment/62 max-w-[700px] text-pretty">{{ $slot }}</p>
</div>
