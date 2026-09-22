@props(['data'])

{{-- JSON_HEX_TAG keeps a stray "</script>" inside the data from closing this tag. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
