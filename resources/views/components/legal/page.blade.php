@props(['heading'])

{{-- Shared shell for the imprint and privacy pages so their spacing cannot drift apart. --}}
<article class="relative z-10 mx-auto max-w-[720px] px-7 py-20 sm:px-10 sm:py-24">
    <div class="text-center mb-12">
        <div class="font-mono text-[11px] tracking-[0.22em] uppercase text-accent mb-3.5">Legal</div>
        <h1 class="font-serif font-normal italic leading-tight tracking-tight text-[clamp(44px,6vw,64px)] m-0 text-parchment">{{ $heading }}</h1>
    </div>

    <div class="space-y-4 text-[15px] leading-relaxed text-parchment/75">
        {{ $slot }}
    </div>
</article>
