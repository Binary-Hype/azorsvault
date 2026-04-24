@extends('layouts.codex')

@php
    $mcpUrl = url('/mcp/mtg');
    $cliCommand = "claude mcp add --transport http azorsvault {$mcpUrl}";
@endphp

@section('content')
    {{-- Hero --}}
    <section class="relative z-10 mx-auto max-w-[880px] flex flex-col items-center text-center px-7 py-16 sm:px-18 sm:py-26">
        <div class="flex items-center gap-3.5 font-mono text-[11px] tracking-[0.22em] uppercase text-parchment/60 mb-7">
            <span class="codex-rule"></span>
            <span>An MCP server for Magic: The Gathering</span>
            <span class="codex-rule"></span>
        </div>

        <div class="relative mb-6 w-22 h-22 inline-flex items-center justify-center text-accent" aria-hidden="true">
            <span class="codex-mana-aura"></span>
            <span class="codex-mana-disc relative w-full h-full rounded-full flex items-center justify-center">
                <svg viewBox="0 0 32 32" width="60%" height="60%" fill="none">
                    <path d="M16 4 C 11 12, 7 16, 7 21 a 9 9 0 0 0 18 0 c 0 -5 -4 -9 -9 -17 z" fill="currentColor" opacity="0.9"/>
                    <path d="M12 18 C 12 22, 14 24, 16 24" stroke="rgba(255,255,255,0.5)" stroke-width="1.2" stroke-linecap="round" fill="none"/>
                </svg>
            </span>
        </div>

        <h1 class="font-serif font-normal leading-[0.98] tracking-[-0.01em] text-[clamp(56px,8vw,88px)] m-0 mb-[22px]">
            <span class="italic font-light text-parchment/60 mr-[0.18em]">The</span>
            <span class="codex-wordmark">Azorsvault</span>
        </h1>

        <p class="font-serif italic text-[22px] leading-[1.45] text-parchment/60 max-w-[520px] mb-11">
            Every card. Every ruling. Every printing.<br>
            Wired into Claude through one tidy little server.
        </p>

        <div class="w-full max-w-[720px] flex flex-col gap-5 mb-8">
            {{-- Terminal copy block --}}
            <div class="w-full">
                <div class="codex-label-lead font-mono text-[10.5px] tracking-[0.18em] uppercase text-white/45 mb-2.5">
                    Add via the Claude CLI
                </div>
                <div class="flex items-stretch bg-ink/70 border border-accent/20 rounded-lg overflow-hidden backdrop-blur-sm hover:border-accent/35 transition-colors text-left">
                    <pre class="flex-1 m-0 px-4 py-4 font-mono text-[13.5px] leading-[1.5] text-[#e8edf4] whitespace-pre overflow-x-auto"><span class="text-accent mr-2.5 select-none opacity-80">$</span><span>{{ $cliCommand }}</span></pre>
                    <button
                        type="button"
                        data-copy="{{ $cliCommand }}"
                        aria-label="Copy install command"
                        class="shrink-0 px-4 min-w-[92px] border-0 border-l border-accent/15 bg-accent/5 text-accent text-xs font-medium tracking-wide cursor-pointer hover:bg-accent/15 hover:text-white transition-colors"
                    >
                        <span class="codex-flip">
                            <span class="codex-flip-face">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="8" height="8" rx="1.2"/><path d="M2 9V3a1 1 0 0 1 1-1h6"/></svg>
                                <span>Copy</span>
                            </span>
                            <span class="codex-flip-face codex-flip-face-back">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7.5l3 3 7-7"/></svg>
                                <span>Copied</span>
                            </span>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Inline copy block --}}
            <div class="w-full">
                <div class="font-mono text-[10px] tracking-[0.2em] uppercase text-white/45 mb-2 text-left">
                    …or paste this URL into the web interface
                </div>
                <div class="flex items-center gap-2 py-2.5 pl-3.5 pr-2 bg-white/5 border border-white/10 rounded-md hover:bg-accent/5 hover:border-accent/25 transition-colors">
                    <code class="flex-1 font-mono text-[12.5px] text-white/85 whitespace-nowrap overflow-x-auto text-left [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">{{ $mcpUrl }}</code>
                    <button
                        type="button"
                        data-copy="{{ $mcpUrl }}"
                        aria-label="Copy MCP URL"
                        class="shrink-0 px-3 py-1.5 border-0 rounded bg-accent/10 text-accent font-medium text-[11.5px] cursor-pointer hover:bg-accent/20 hover:text-white transition-colors"
                    >
                        <span class="codex-flip">
                            <span class="codex-flip-face">
                                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="8" height="8" rx="1.2"/><path d="M2 9V3a1 1 0 0 1 1-1h6"/></svg>
                                <span>Copy</span>
                            </span>
                            <span class="codex-flip-face codex-flip-face-back">
                                <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7.5l3 3 7-7"/></svg>
                                <span>Copied</span>
                            </span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 font-mono text-[11.5px] tracking-wider text-parchment/60">
            <span class="codex-live-dot w-[7px] h-[7px] rounded-full bg-live"></span>
            <span>Live · 5 tools · Comprehensive Rules v2026.04.10</span>
        </div>
    </section>

    {{-- Example queries --}}
    <section id="queries" class="relative z-10 py-20 px-7 border-t border-parchment/15 sm:px-18">
        <div class="max-w-[720px] mx-auto mb-12 text-center">
            <div class="font-mono text-[11px] tracking-[0.22em] uppercase text-accent mb-3.5">I.&nbsp; Example Incantations</div>
            <h2 class="font-serif italic font-normal text-[clamp(34px,4vw,44px)] leading-tight tracking-tight m-0">Try asking Claude…</h2>
        </div>

        <div class="max-w-[1080px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ([
                ['tag' => 'search-cards-advanced', 'text' => 'find me a blue instant under 3 mana with flash that counters a spell', 'typewriter' => true],
                ['tag' => 'search-rules',          'text' => 'how does the layers system handle a creature copying another?'],
                ['tag' => 'search-cards-advanced', 'text' => 'top 20 commanders in Sultai colors by EDHREC rank'],
                ['tag' => 'search-card',           'text' => 'show me Delver of Secrets // Insectile Aberration'],
                ['tag' => 'get-rule',              'text' => 'pull rule 702.3 — Defender, in full'],
                ['tag' => 'search-cards',          'text' => 'check my decklist of 60 names against the printing database'],
            ] as $q)
                <article class="codex-query relative py-5.5 pl-7 pr-6 bg-white/[0.025] border border-white/10 rounded-md hover:bg-accent/5 hover:border-accent/25 hover:-translate-y-px hover:shadow-[0_8px_24px_-8px_rgba(124,196,255,0.15)] transition-all">
                    <div class="font-mono text-[9.5px] tracking-[0.18em] uppercase text-accent/85 mb-2">{{ $q['tag'] }}</div>
                    <div class="codex-quote font-serif italic text-[19px] leading-[1.4] text-parchment">
                        @if (! empty($q['typewriter']))
                            <span data-typewriter data-text="{{ $q['text'] }}" data-start-delay="900"></span>
                        @else
                            {{ $q['text'] }}
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Filter apparatus --}}
    <section id="filters" class="relative z-10 py-20 px-7 border-t border-parchment/15 bg-ink-2/50 sm:px-18">
        <div class="max-w-[720px] mx-auto mb-12 text-center">
            <div class="font-mono text-[11px] tracking-[0.22em] uppercase text-accent mb-3.5">II.&nbsp; The Filtering Apparatus</div>
            <h2 class="font-serif italic font-normal text-[clamp(34px,4vw,44px)] leading-snug tracking-tight m-0 mb-4">
                <code class="font-mono not-italic text-[0.78em] align-middle px-2 py-0.5 rounded border border-accent/20 bg-accent/10 text-accent">search-cards-advanced</code>
                · every filter, AND-combined.
            </h2>
            <p class="text-[15px] leading-relaxed text-parchment/60 m-0">
                Multi-filter search across the printings database. Results deduped by oracle_id, max 50.
                Pass at least one non-meta filter; the rest compose freely.
            </p>
        </div>

        <div class="max-w-[1080px] mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 border-t border-l border-parchment/15">
            @foreach ([
                ['k' => 'name',                'v' => 'fulltext, partial'],
                ['k' => 'mana_cost',           'v' => 'exact, e.g. {2}{R}{R}'],
                ['k' => 'oracle_text',         'v' => 'fulltext, partial'],
                ['k' => 'type_line',           'v' => 'partial, e.g. Legendary Creature'],
                ['k' => 'subtype',             'v' => 'after the em-dash, e.g. Wizard'],
                ['k' => 'colors',              'v' => 'W/U/B/R/G — must contain ALL'],
                ['k' => 'color_identity',     'v' => 'W/U/B/R/G — Commander identity'],
                ['k' => 'rarity',              'v' => 'common · uncommon · rare · mythic'],
                ['k' => 'set',                 'v' => 'set code, e.g. neo'],
                ['k' => 'keyword',             'v' => 'e.g. Flying'],
                ['k' => 'power · toughness',  'v' => 'string — allows *'],
                ['k' => 'cmc + cmc_operator',  'v' => '= · < · > · <= · >='],
                ['k' => 'format',              'v' => 'standard · commander · modern · …'],
                ['k' => 'legality',            'v' => 'legal · not_legal · restricted · banned'],
                ['k' => 'max_edhrec_rank',     'v' => 'lower = more popular'],
            ] as $f)
                <div class="flex flex-col gap-1.5 px-5.5 py-4.5 bg-ink-2/60 hover:bg-accent/5 transition-colors border-r border-b border-parchment/15">
                    <div class="font-serif italic text-[17px] text-parchment">{{ $f['k'] }}</div>
                    <div class="font-mono text-xs text-accent/85">{{ $f['v'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Tools list --}}
    <section id="tools" class="relative z-10 py-20 px-7 border-t border-parchment/15 sm:px-18">
        <div class="max-w-[720px] mx-auto mb-12 text-center">
            <div class="font-mono text-[11px] tracking-[0.22em] uppercase text-accent mb-3.5">III.&nbsp; The Five Tools</div>
            <h2 class="font-serif italic font-normal text-[clamp(34px,4vw,44px)] leading-tight tracking-tight m-0">A small surface, deliberately.</h2>
        </div>

        <div class="max-w-[1080px] mx-auto flex flex-col">
            @foreach ([
                ['i' => '01', 'n' => 'search-card',           'd' => 'Find a single card by exact name (case-insensitive). Returns the most recent printing.',                   'a' => 'name'],
                ['i' => '02', 'n' => 'search-cards',          'd' => 'Batch lookup by exact names — for decklists. Returns name → card | null.',                                'a' => 'names[1–100]'],
                ['i' => '03', 'n' => 'search-cards-advanced', 'd' => 'Multi-filter search; all filters AND-combined. Deduped by oracle_id, max 50.',                            'a' => '15 filters · see above'],
                ['i' => '04', 'n' => 'search-rules',          'd' => 'Keyword search across the Comprehensive Rules and glossary.',                                              'a' => 'query · section · chapter'],
                ['i' => '05', 'n' => 'get-rule',              'd' => 'Fetch a precise rule, chapter, section, or glossary term — dispatch-routed by shape.',                    'a' => 'rule_number'],
            ] as $tool)
                <div class="grid grid-cols-[40px_1fr] gap-4 md:grid-cols-[60px_1fr_auto] md:gap-6 items-center py-5 px-2 border-t border-parchment/15 last:border-b hover:bg-accent/5 hover:pl-4 transition-all">
                    <div class="font-serif italic text-[28px] text-accent opacity-70">{{ $tool['i'] }}</div>
                    <div>
                        <div class="font-mono text-base text-parchment mb-1.5 font-medium">{{ $tool['n'] }}</div>
                        <div class="text-sm text-parchment/60 leading-relaxed max-w-[560px]">{{ $tool['d'] }}</div>
                    </div>
                    <div class="col-start-2 md:col-start-auto font-mono text-[11.5px] text-accent/70 text-left md:text-right tracking-wide">{{ $tool['a'] }}</div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
