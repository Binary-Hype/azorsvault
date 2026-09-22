@inject('latinFonts', 'App\\Services\\LatinFonts')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Azorsvault — MTG MCP for Claude')</title>
    <meta name="description" content="@yield('description', 'An MCP server for Magic: The Gathering. Every card ever printed, every ruling ever argued over — sealed in one vault, and Claude already knows the knock.')">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', 'Azorsvault — MTG MCP for Claude')">
    <meta property="og:description" content="@yield('og_description', 'An MCP server for Magic: The Gathering, wired into Claude.')">
    {{-- current() drops the query string, so tracking parameters collapse onto one canonical URL. --}}
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ url('/icon.png') }}">
    <meta name="theme-color" content="#060b1a">

    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <link rel="icon" type="image/png" sizes="128x128" href="/icon.png">
    <link rel="icon" type="image/jpeg" sizes="736x736" href="/icon.jpeg">
    <link rel="apple-touch-icon" sizes="128x128" href="/icon.png">
    <link rel="shortcut icon" href="/favicon.ico">

    {{-- Ahead of the font CSS: the latin files render every page, and having
         them before first paint is what keeps the swap from reflowing. --}}
    {{ $latinFonts->toHtml() }}

    {{-- Only the basic-latin @font-face rules; the package would inline all of them. --}}
    {{ $latinFonts->toStyleTag() }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://analytics.notonfire.systems">

    <script defer src="https://analytics.notonfire.systems/script.js" data-website-id="01a0726d-09b5-711e-8e63-0579e92d9c4f" data-domains="azorsvault.cards" data-do-not-track="true" data-exclude-search="true" data-exclude-hash="true" data-performance="true" referrerpolicy="no-referrer"></script>
</head>
<body class="bg-ink text-parchment font-sans antialiased">
    <div class="codex-bg relative min-h-screen isolate overflow-hidden">
        <header class="relative z-10 flex items-center justify-between gap-x-6 gap-y-3 flex-wrap px-5 py-5 border-b border-gold/25 sm:px-22 sm:py-6">
            <a href="{{ url('/') }}" class="flex items-center gap-3.5 text-parchment no-underline">
                <x-seal class="w-[34px] h-[34px]" stroke="1.6"/>
                <span class="font-display text-[40px] leading-none tracking-[0.01em]">Azorsvault</span>
            </a>
            <nav class="flex flex-wrap items-center justify-end gap-x-5 gap-y-1.5 text-[14px] sm:gap-x-7 sm:text-[15px] tracking-[0.02em]">
                <a href="{{ url('/#incantations') }}" class="text-parchment/72 hover:text-parchment transition-colors">Incantations</a>
                <a href="{{ url('/#sieve') }}" class="text-parchment/72 hover:text-parchment transition-colors">The Sieve</a>
                <a href="{{ url('/#weighing') }}" class="text-parchment/72 hover:text-parchment transition-colors">The Weighing</a>
                <a href="{{ url('/#keys') }}" class="text-parchment/72 hover:text-parchment transition-colors">The Eight Keys</a>
                <a href="https://github.com/Binary-Hype" class="text-accent font-mono text-[13px] hover:opacity-80 transition-opacity">GitHub ↗</a>
            </nav>
        </header>

        @yield('content')

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
                    <div class="flex flex-col gap-3 text-base">
                        <div class="font-mono text-[10.5px] tracking-[0.22em] uppercase text-gold mb-1">Elsewhere</div>
                        <a href="https://modelcontextprotocol.io" class="text-parchment/65 hover:text-parchment transition-colors">The MCP specification</a>
                        <a href="https://magic.wizards.com/en/rules" class="text-parchment/65 hover:text-parchment transition-colors">The Comprehensive Rules</a>
                        <a href="https://scryfall.com/docs/api" class="text-parchment/65 hover:text-parchment transition-colors">Scryfall, who keeps the cards</a>
                    </div>
                    <div class="flex flex-col gap-3 text-base">
                        <div class="font-mono text-[10.5px] tracking-[0.22em] uppercase text-gold mb-1">The fine print</div>
                        <a href="{{ route('imprint') }}" class="text-parchment/65 hover:text-parchment transition-colors">Imprint</a>
                        <a href="{{ route('privacy') }}" class="text-parchment/65 hover:text-parchment transition-colors">Privacy Policy</a>
                    </div>
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
