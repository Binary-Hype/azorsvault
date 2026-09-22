@props(['heading'])

<section class="space-y-4">
    <h2 class="font-medium text-[22px] leading-snug tracking-[0.01em] text-parchment mt-12 mb-4">{{ $heading }}</h2>
    {{ $slot }}
</section>
