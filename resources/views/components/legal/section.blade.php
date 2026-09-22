@props(['heading'])

<section class="space-y-4">
    <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-12 mb-4">{{ $heading }}</h2>
    {{ $slot }}
</section>
