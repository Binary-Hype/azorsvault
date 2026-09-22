@props(['href'])

{{-- External links carry rel="nofollow noopener"; internal ones do not need it. --}}
<a
    href="{{ $href }}"
    @class(['text-accent hover:text-parchment transition-colors'])
    @if (str_starts_with($href, 'http')) rel="nofollow noopener" @endif
>{{ $slot }}</a>
