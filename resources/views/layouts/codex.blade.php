<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Azorsvault — MTG MCP for Claude')</title>
    <meta name="description" content="@yield('description', 'An MCP server for Magic: The Gathering. Every card, every ruling, every printing — wired into Claude through one tidy little server.')">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', 'Azorsvault — MTG MCP for Claude')">
    <meta property="og:description" content="@yield('og_description', 'An MCP server for Magic: The Gathering, wired into Claude.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ url('/icon.png') }}">
    <meta name="theme-color" content="#060b1a">

    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <link rel="icon" type="image/png" sizes="128x128" href="/icon.png">
    <link rel="icon" type="image/jpeg" sizes="736x736" href="/icon.jpeg">
    <link rel="apple-touch-icon" sizes="128x128" href="/icon.png">
    <link rel="shortcut icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-parchment font-sans antialiased">
    <div class="codex-bg relative min-h-screen isolate overflow-hidden">
        <div class="codex-mist absolute inset-0 pointer-events-none overflow-hidden opacity-90" data-mist aria-hidden="true">
            <svg viewBox="0 0 1200 800" preserveAspectRatio="none">
                <defs>
                    <radialGradient id="mist-1" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="rgba(124,196,255,0.25)"/>
                        <stop offset="60%" stop-color="rgba(124,196,255,0.05)"/>
                        <stop offset="100%" stop-color="rgba(124,196,255,0)"/>
                    </radialGradient>
                    <radialGradient id="mist-2" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="rgba(180,220,255,0.20)"/>
                        <stop offset="100%" stop-color="rgba(180,220,255,0)"/>
                    </radialGradient>
                </defs>
                <ellipse cx="280" cy="220" rx="380" ry="160" fill="url(#mist-1)"/>
                <ellipse cx="900" cy="600" rx="420" ry="180" fill="url(#mist-1)"/>
                <ellipse cx="700" cy="200" rx="240" ry="100" fill="url(#mist-2)"/>
                <ellipse cx="200" cy="640" rx="200" ry="90" fill="url(#mist-2)"/>
            </svg>
        </div>

        <div class="codex-stars absolute inset-0 pointer-events-none opacity-70 z-0" aria-hidden="true"></div>

        <header class="relative z-10 flex items-center justify-between gap-4 flex-wrap px-7 py-6 border-b border-parchment/15 sm:px-18">
            <a href="{{ url('/') }}" class="flex items-center gap-3 font-serif text-parchment no-underline">
                <span class="codex-glyph-glow text-accent text-[22px] leading-none -translate-y-px">⟁</span>
                <span class="text-[19px] font-medium tracking-[0.06em]">Azorsvault</span>
            </a>
            <nav class="flex items-center gap-7 text-[13px] text-parchment/60">
                <a href="{{ url('/#queries') }}" class="hover:text-parchment transition-colors">Examples</a>
                <a href="{{ url('/#filters') }}" class="hover:text-parchment transition-colors">Filters</a>
                <a href="{{ url('/#tools') }}" class="hover:text-parchment transition-colors">Tools</a>
                <a href="https://github.com/Binary-Hype" class="text-accent font-mono text-xs hover:opacity-80 transition-opacity">GitHub ↗</a>
            </nav>
        </header>

        @yield('content')

        <footer class="relative z-10 border-t border-parchment/15 px-7 pt-15 pb-8 bg-ink/60 sm:px-18">
            <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-12 mb-12">
                <div class="flex items-center gap-3 font-serif text-[22px] text-parchment">
                    <span class="codex-glyph-glow text-accent">⟁</span>
                    <span>Azorsvault</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-7">
                    <div class="flex flex-col gap-2.5 text-[13px]">
                        <div class="font-mono text-[10px] tracking-[0.2em] uppercase text-accent mb-1.5">Resources</div>
                        <a href="https://modelcontextprotocol.io" class="text-parchment/60 hover:text-parchment transition-colors">MCP spec</a>
                        <a href="https://magic.wizards.com/en/rules" class="text-parchment/60 hover:text-parchment transition-colors">Comprehensive Rules</a>
                        <a href="https://scryfall.com/docs/api" class="text-parchment/60 hover:text-parchment transition-colors">Scryfall API</a>
                    </div>
                    <div class="flex flex-col gap-2.5 text-[13px]">
                        <div class="font-mono text-[10px] tracking-[0.2em] uppercase text-accent mb-1.5">Legal</div>
                        <a href="{{ route('imprint') }}" class="text-parchment/60 hover:text-parchment transition-colors">Imprint</a>
                        <a href="{{ route('privacy') }}" class="text-parchment/60 hover:text-parchment transition-colors">Privacy Policy</a>
                    </div>
                </div>
            </div>
            <div class="max-w-[1080px] mx-auto pt-6 border-t border-parchment/15 flex flex-wrap justify-between gap-4 font-mono text-[11px] text-parchment/60">
                <span>Unaffiliated with Wizards of the Coast. Card data via Scryfall.</span>
                <span>© MMXXVI · The Azorsvault</span>
            </div>
        </footer>
    </div>
</body>
</html>
