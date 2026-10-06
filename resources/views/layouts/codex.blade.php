@inject('latinFonts', 'App\\Services\\LatinFonts')
@inject('vaultStatus', 'App\\Services\\VaultStatus')
@use('Illuminate\Support\Facades\View')
@use('Illuminate\Support\HtmlString')
@use('Illuminate\Support\Str')
@php
    /*
     * Section content arrives already escaped, so these are echoed raw. The
     * social title follows the page title, and the social description follows
     * the page's own description before falling back to the short site line.
     */
    $title = View::yieldContent('title', 'Azorsvault — Magic: The Gathering MCP Server for Claude');
    $description = View::yieldContent('description', 'An MCP server for Magic: The Gathering. Card search, format legality and the Comprehensive Rules — sealed in one vault, and Claude already knows the knock.');
    $ogTitle = View::yieldContent('og_title', new HtmlString($title));
    $ogDescription = View::yieldContent('og_description', View::hasSection('description') ? new HtmlString($description) : 'An MCP server for Magic: The Gathering, wired into Claude.');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{!! $title !!}</title>
    <meta name="description" content="{!! $description !!}">

    {{-- Let search engines show full snippets and the large card image. --}}
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    {{-- current() drops the query string, so tracking parameters collapse onto one canonical URL. --}}
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Azorsvault">
    <meta property="og:locale" content="en_US">
    <meta property="og:title" content="{!! $ogTitle !!}">
    <meta property="og:description" content="{!! $ogDescription !!}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ url('/og-image.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="The Azorsvault seal above the wordmark, with the MCP endpoint azorsvault.cards/mcp/mtg">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{!! $ogTitle !!}">
    <meta name="twitter:description" content="{!! $ogDescription !!}">
    <meta name="twitter:image" content="{{ url('/og-image.png') }}">

    <meta name="theme-color" content="#060b1a">

    <link rel="alternate" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">

    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <link rel="icon" type="image/png" sizes="128x128" href="/icon.png">
    <link rel="icon" type="image/jpeg" sizes="736x736" href="/icon.jpeg">
    <link rel="apple-touch-icon" sizes="128x128" href="/icon.png">
    <link rel="shortcut icon" href="/favicon.ico">

    {{-- Sitewide entity graph: who publishes this, and what the site is. --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => route('home').'#organization',
                'name' => 'Azorsvault',
                'url' => route('home'),
                'logo' => url('/icon.png'),
                'sameAs' => ['https://github.com/Binary-Hype'],
            ],
            [
                '@type' => 'WebSite',
                '@id' => route('home').'#website',
                'name' => 'Azorsvault',
                'url' => route('home'),
                'description' => 'An MCP server for Magic: The Gathering: card search, format legality and the Comprehensive Rules, wired into Claude.',
                'inLanguage' => 'en',
                'publisher' => ['@id' => route('home').'#organization'],
            ],
        ],
    ]"/>

    @stack('structured-data')

    {{-- Ahead of the font CSS: the latin files render every page, and having
         them before first paint is what keeps the swap from reflowing. --}}
    {{ $latinFonts->preloadTags() }}

    {{-- Only the basic-latin @font-face rules; the package would inline all of them. --}}
    {{ $latinFonts->toStyleTag() }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-parchment font-sans antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:rounded-[3px] focus:bg-ink-2 focus:border focus:border-gold/45 focus:text-parchment">Skip to content</a>
    <div class="codex-bg relative min-h-screen isolate overflow-hidden">
        <header class="relative z-10 flex items-center justify-between gap-x-6 gap-y-3 flex-wrap px-5 py-5 border-b border-gold/25 sm:px-22 sm:py-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3.5 text-parchment no-underline">
                <x-seal class="w-[34px] h-[34px]" stroke="1.6"/>
                <span class="font-display text-[40px] leading-none tracking-[0.01em]">Azorsvault</span>
            </a>
            <nav class="flex flex-wrap items-center justify-end gap-x-5 gap-y-1.5 text-[14px] sm:gap-x-7 sm:text-[15px] tracking-[0.02em]">
                <a href="{{ route('home') }}#incantations" class="text-parchment/72 hover:text-parchment transition-colors">Incantations</a>
                <a href="{{ route('home') }}#sieve" class="text-parchment/72 hover:text-parchment transition-colors">The Sieve</a>
                <a href="{{ route('home') }}#weighing" class="text-parchment/72 hover:text-parchment transition-colors">The Weighing</a>
                <a href="{{ route('home') }}#keys" class="text-parchment/72 hover:text-parchment transition-colors">The {{ Str::title($vaultStatus->toolCountInWords()) }} Keys</a>
                <a href="https://github.com/Binary-Hype" class="text-accent font-mono text-[13px] hover:opacity-80 transition-opacity">GitHub ↗</a>
            </nav>
        </header>

        <main id="content">
            @yield('content')
        </main>

        <footer class="relative z-10 border-t border-gold/25 bg-ink/60 px-5 pt-12 pb-8 sm:px-22 sm:pt-16 sm:pb-9">
            <div class="max-w-[1180px] mx-auto grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)] gap-12 md:gap-18">
                <div class="flex flex-col gap-3.5">
                    <div class="flex items-center gap-3.5">
                        <x-seal class="w-[30px] h-[30px]" stroke="1.6"/>
                        <span class="font-display text-[42px] leading-none">Azorsvault</span>
                    </div>
                    <p class="italic text-[17px] leading-snug text-parchment/55 max-w-[300px] m-0">Kept by one small server, and open at every hour.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-10">
                    <nav aria-label="Elsewhere" class="flex flex-col gap-3 text-base">
                        <div class="font-mono text-[10.5px] tracking-[0.22em] uppercase text-gold mb-1">Elsewhere</div>
                        <a href="https://modelcontextprotocol.io" class="text-parchment/65 hover:text-parchment transition-colors">The MCP specification</a>
                        <a href="https://magic.wizards.com/en/rules" class="text-parchment/65 hover:text-parchment transition-colors">The Comprehensive Rules</a>
                        <a href="https://scryfall.com/docs/api" class="text-parchment/65 hover:text-parchment transition-colors">Scryfall, who keeps the cards</a>
                    </nav>
                    <nav aria-label="The fine print" class="flex flex-col gap-3 text-base">
                        <div class="font-mono text-[10.5px] tracking-[0.22em] uppercase text-gold mb-1">The fine print</div>
                        <a href="{{ route('imprint') }}" class="text-parchment/65 hover:text-parchment transition-colors">Imprint</a>
                        <a href="{{ route('privacy') }}" class="text-parchment/65 hover:text-parchment transition-colors">Privacy Policy</a>
                    </nav>
                </div>
            </div>
            <div class="max-w-[1180px] mx-auto mt-11 pt-5.5 border-t border-gold/16 flex flex-wrap justify-between gap-4 font-mono text-[11.5px] text-parchment/50">
                <span>No pact with Wizards of the Coast. Card lore carried in from Scryfall.</span>
                <span>© MMXXVI · The Azorsvault</span>
            </div>
        </footer>
    </div>
</body>
</html>
