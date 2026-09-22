@inject('vaultStatus', 'App\\Services\\VaultStatus')
@extends('layouts.codex')

@php
    $mcpUrl = url('/mcp/mtg');
    $cliCommand = "claude mcp add --transport http azorsvault {$mcpUrl}";
@endphp

@push('structured-data')
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        '@id' => url('/').'#app',
        'name' => 'Azorsvault',
        'applicationCategory' => 'DeveloperApplication',
        'applicationSubCategory' => 'MCP server',
        'operatingSystem' => 'Any',
        'url' => url('/'),
        'description' => 'A Model Context Protocol server for Magic: The Gathering. It gives Claude read-only tools over every Scryfall card, its rulings, format legality and the Comprehensive Rules, plus Commander deck validation.',
        'inLanguage' => 'en',
        'isAccessibleForFree' => true,
        'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        'publisher' => ['@id' => url('/').'#organization'],
        'featureList' => [
            'Card lookup by name, newest printing',
            'Batch card lookup for decklists, up to 100 names',
            'Advanced card search across fifteen stackable filters',
            'Full-text search of the Comprehensive Rules and glossary',
            'Rule, chapter, section and glossary lookup with subrules',
            'Per-format legality, Game Changer and Reserved List status',
            'Complete banned and restricted lists per format',
            'Commander decklist validation with bracket detection',
        ],
    ]"/>
@endpush

@section('content')
    {{-- The seal --}}
    <section id="top" class="relative z-10 flex flex-col items-center text-center px-5 py-12 sm:px-22 sm:py-24">
        <div class="flex flex-wrap items-center justify-center gap-x-4.5 gap-y-1 text-[11px] tracking-[0.18em] uppercase text-gold/85 mb-7 sm:text-[15px] sm:tracking-[0.26em] sm:mb-8">
            <span class="codex-rule hidden sm:block"></span>
            <span>Bound for Claude · opened by a whisper</span>
            <span class="codex-rule codex-rule-end hidden sm:block"></span>
        </div>

        <div class="relative w-[116px] h-[116px] sm:w-[168px] sm:h-[168px] flex items-center justify-center mb-5">
            <span class="codex-seal-aura" aria-hidden="true"></span>
            <x-seal detailed class="relative w-full h-full" stroke="1.2"/>
        </div>

        <h1 class="codex-wordmark font-display text-[clamp(86px,11vw,156px)] leading-[0.92] m-0 mb-3.5 text-[#f3ecd9]">The Azorsvault</h1>

        <p class="max-w-[640px] text-[18px] sm:text-[23px] leading-[1.5] sm:leading-[1.55] text-parchment/74 text-pretty m-0 mb-10">
            Every Magic: The Gathering card ever printed. Every ruling ever argued over. Sealed behind one door, and
            Claude already knows the knock.
        </p>

        {{-- The plain-spoken part: words of power must be exact. --}}
        <div class="w-full max-w-[860px] flex flex-col gap-5 mb-9 text-left">
            <div class="flex flex-col gap-2.5">
                <div class="flex items-center gap-2.5 font-mono text-[11.5px] tracking-[0.2em] uppercase text-gold/90">
                    <span class="block w-4 h-px bg-gold/60"></span>
                    <span>Speak it into your terminal</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-stretch bg-ink/85 border border-gold/30 rounded overflow-hidden hover:border-gold/45 transition-colors">
                    <pre class="flex-1 m-0 px-4 py-4 sm:px-5.5 font-mono text-[12px] sm:text-[14.5px] leading-[1.55] text-[#e8edf4] whitespace-pre-wrap break-all sm:whitespace-pre sm:break-normal sm:overflow-x-auto"><span class="text-accent/80 select-none">$ </span>{{ $cliCommand }}</pre>
                    <x-copy-button
                        :value="$cliCommand"
                        label="Copy the install command"
                        class="shrink-0 flex items-center justify-center h-11 sm:h-auto sm:min-w-[116px] px-5.5 border-0 border-t sm:border-t-0 sm:border-l border-gold/30 bg-gold/12 text-[#e4c87d] text-[15px] font-medium tracking-[0.04em] cursor-pointer hover:bg-gold/20 hover:text-parchment transition-colors"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2.5 font-mono text-[11.5px] tracking-[0.2em] uppercase text-parchment/50">
                    <span class="block w-4 h-px bg-parchment/30"></span>
                    <span>…or hand this to the web interface</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 sm:pl-4.5 bg-parchment/5 border border-parchment/15 rounded hover:border-accent/30 transition-colors">
                    <code class="flex-1 font-mono text-[12px] sm:text-[13.5px] text-white/88 break-all sm:break-normal">{{ $mcpUrl }}</code>
                    <x-copy-button
                        :value="$mcpUrl"
                        label="Copy the vault address"
                        class="shrink-0 flex items-center justify-center h-11 sm:h-auto px-4.5 py-2.5 border border-accent/28 rounded-[3px] bg-accent/10 text-[#9ed3ff] text-[14.5px] font-medium cursor-pointer hover:bg-accent/20 hover:text-parchment transition-colors"
                    />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 text-[15px] sm:text-[16px] text-parchment/62 text-left">
            <span class="codex-live-dot shrink-0 w-2 h-2 rounded-full bg-live"></span>
            <span>{{ implode(' · ', $vaultStatus->segments()) }}</span>
        </div>
    </section>

    {{-- I. Incantations --}}
    <section id="incantations" class="relative z-10 px-5 py-14 sm:px-22 sm:py-22 border-t border-gold/20">
        <x-chapter numeral="I" name="Incantations" heading="Say these out loud">
            No syntax to memorise. Claude picks the key and turns it for you.
        </x-chapter>

        <div class="max-w-[1180px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6.5">
            @foreach ([
                ['tool' => 'search-cards-advanced', 'ask' => 'Find me a blue instant under three mana that answers a spell on the stack.'],
                ['tool' => 'search-rules',          'ask' => 'Untangle the layers for me — my creature copied theirs and then grew.'],
                ['tool' => 'search-cards-advanced', 'ask' => 'Name the twenty Sultai commanders the tables love most.'],
                ['tool' => 'search-card',           'ask' => 'Show me both faces of Delver of Secrets.'],
                ['tool' => 'get-rule',              'ask' => 'Read me 702.3 — Defender — word for word.'],
                ['tool' => 'search-cards',          'ask' => 'Here are sixty names from my deck — tell me which ones are real.'],
                ['tool' => 'validate-deck',         'ask' => 'Weigh my Commander list — anything banned, off-colour or doubled up?'],
                ['tool' => 'get-banned-list',       'ask' => 'What is banned in Commander these days?'],
            ] as $incantation)
                <article class="flex flex-col gap-3 p-5 md:px-7.5 md:py-7 bg-ink-2/72 border border-gold/22 rounded-[3px] hover:border-gold/40 hover:bg-ink-2 transition-colors">
                    <div class="font-mono text-[10px] md:text-[10.5px] tracking-[0.2em] uppercase text-accent/80">{{ $incantation['tool'] }}</div>
                    <div class="italic text-[19px] md:text-[22px] leading-[1.42] text-parchment">&ldquo;{{ $incantation['ask'] }}&rdquo;</div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- II. The Sieve --}}
    <section id="sieve" class="codex-band relative z-10 px-5 py-14 sm:px-22 sm:py-22 border-t border-gold/20">
        <x-chapter numeral="II" name="The Sieve" heading="Fifteen ways to narrow a fate" :wide="true">
            Every sieve below can be laid over the others at once. Ask for one, ask for nine. What falls through is
            deduplicated by oracle identity, fifty at most.
        </x-chapter>

        <div class="max-w-[1180px] mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-px bg-gold/20 border border-gold/20">
            @foreach ([
                ['filter' => 'name',              'reads' => 'whole or part, spelled loosely'],
                ['filter' => 'mana_cost',         'reads' => 'exactly, as written: {2}{R}{R}'],
                ['filter' => 'oracle_text',       'reads' => "any phrase in the card's own words"],
                ['filter' => 'type_line',         'reads' => 'Legendary Creature, and the like'],
                ['filter' => 'subtype',           'reads' => 'whatever follows the em-dash'],
                ['filter' => 'colors',            'reads' => 'W U B R G — all of them, together'],
                ['filter' => 'color_identity',    'reads' => 'what a commander permits'],
                ['filter' => 'rarity',            'reads' => 'common · uncommon · rare · mythic'],
                ['filter' => 'set',               'reads' => 'the three letters on the spine'],
                ['filter' => 'keyword',           'reads' => 'Flying, Ward, Cascade…'],
                ['filter' => 'power · toughness', 'reads' => 'asterisks welcome'],
                ['filter' => 'cmc + cmc_operator','reads' => '= · < · > · <= · >='],
                ['filter' => 'format',            'reads' => 'standard · commander · modern…'],
                ['filter' => 'legality',          'reads' => 'legal · not_legal · restricted · banned'],
                ['filter' => 'max_edhrec_rank',   'reads' => 'lower means better loved'],
            ] as $sieve)
                <div class="flex flex-col gap-1.5 px-6 py-5 bg-[#08101f] hover:bg-ink-2 transition-colors">
                    <div class="font-mono text-[13.5px] text-parchment">{{ $sieve['filter'] }}</div>
                    <div class="text-[15.5px] text-parchment/58">{{ $sieve['reads'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- III. The Weighing --}}
    <section id="weighing" class="relative z-10 px-5 py-14 sm:px-22 sm:py-22 border-t border-gold/20">
        <x-chapter numeral="III" name="The Weighing" heading="Hand over your hundred" :wide="true">
            Paste a Commander list as it left your deckbuilder. The vault counts it, weighs every card against the
            format's laws, and tells you plainly what is wrong.
        </x-chapter>

        <div class="max-w-[1180px] mx-auto grid grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] gap-6 lg:gap-10 items-start">
            <div class="flex flex-col p-5 sm:px-8.5 sm:pt-7.5 sm:pb-6 bg-ink-2/72 border border-gold/22 rounded-[3px]">
                <div class="font-mono text-[10.5px] tracking-[0.2em] uppercase text-accent/80 mb-2">validate-deck</div>
                <div class="font-display text-[38px] sm:text-[46px] leading-none text-[#f3ecd9] mb-2.5">What the scales test</div>

                @foreach ([
                    ['law' => 'A hundred, counted',        'note' => 'Commander asks for exactly one hundred cards, the commander among them.'],
                    ['law' => 'One of each',               'note' => 'Only basic lands may be doubled. Everything else is singleton.'],
                    ['law' => 'A commander who may lead',  'note' => 'Legendary, or bearing the line that says it can. Two only under Partner or a Background.'],
                    ['law' => 'Nothing banned',            'note' => 'Every card weighed against the Commander banned list.'],
                    ['law' => 'Inside the colours',        'note' => "No card may carry a colour its commander's identity does not."],
                    ['law' => 'Game Changers, counted',    'note' => 'How many you are carrying, and the lowest bracket that still allows them.'],
                ] as $scale)
                    <div class="grid grid-cols-[22px_minmax(0,1fr)] gap-4 py-3.5 border-t border-gold/14">
                        <svg viewBox="0 0 22 22" fill="none" class="w-[22px] h-[22px] mt-0.5" aria-hidden="true">
                            <circle cx="11" cy="11" r="10" stroke="currentColor" class="text-gold/50" stroke-width="1"/>
                            <path d="M6.5 11.2l3 3 6-6.4" stroke="currentColor" class="text-gold-bright" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex flex-col gap-1">
                            <div class="text-[17px] sm:text-[18px] text-parchment">{{ $scale['law'] }}</div>
                            <div class="text-[15.5px] sm:text-base leading-normal text-parchment/60">{{ $scale['note'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-4.5 p-5 sm:p-8.5 bg-ink/60 border border-gold/22 rounded-[3px]">
                <div class="flex flex-col gap-2.5">
                    <div class="font-mono text-[10.5px] tracking-[0.2em] uppercase text-gold/90">Lists it can read</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['Moxfield', 'Archidekt', 'MTG Arena'] as $export)
                            <span class="px-3.5 py-1.5 border border-gold/32 rounded-full text-[15px] text-parchment">{{ $export }}</span>
                        @endforeach
                        <span class="px-3.5 py-1.5 border border-parchment/16 rounded-full text-[15px] text-parchment/60">plain text</span>
                    </div>
                </div>

                <pre class="m-0 px-5 py-4.5 bg-ink/85 border border-parchment/10 rounded-[3px] font-mono text-[12px] sm:text-[13px] leading-[1.75] text-white/82 overflow-x-auto">Commander
1 Atraxa, Grand Unifier

Deck
1 Sol Ring
1x Cultivate (m21) 177
10 Forest

Sideboard
1 Nature's Claim</pre>

                <p class="m-0 text-base leading-[1.55] text-parchment/60">
                    One card to a line, with or without the set and collector number. Section headers are understood —
                    and sideboards, maybeboards and token piles are left out of the count, then named in the answer so
                    you know what was set aside.
                </p>
            </div>
        </div>
    </section>

    {{-- IV. The Eight Keys --}}
    <section id="keys" class="relative z-10 px-5 py-14 sm:px-22 sm:py-22 border-t border-gold/20">
        <x-chapter numeral="IV" name="The Eight Keys" heading="A small ring, on purpose">
            Eight doors is all the vault has. Anything worth asking fits through one of them.
        </x-chapter>

        <div class="max-w-[1180px] mx-auto flex flex-col">
            @foreach ([
                ['n' => 'I',    'tool' => 'search-card',           'does' => 'One card, called by its true name. The vault hands back its newest printing.',                                                              'takes' => 'name'],
                ['n' => 'II',   'tool' => 'search-cards',          'does' => 'A whole roll call at once — for decklists. Each name comes back with its card, or with nothing.',                                          'takes' => 'names[1–100]'],
                ['n' => 'III',  'tool' => 'search-cards-advanced', 'does' => 'The Sieve itself. Stack any of the fifteen filters; fifty results come through, no duplicates.',                                           'takes' => '15 filters'],
                ['n' => 'IV',   'tool' => 'search-rules',          'does' => 'Hunt a phrase through the Comprehensive Rules and the glossary that guards them.',                                                         'takes' => 'query · section · chapter'],
                ['n' => 'V',    'tool' => 'get-rule',              'does' => 'One rule, one chapter, one section, one glossary term — whichever shape you ask in. Ask for 702.19 and its lettered subrules come with it.', 'takes' => 'rule_number'],
                ['n' => 'VI',   'tool' => 'check-legality',        'does' => 'Where a card may be played, and where it is forbidden. Also whether it is a Game Changer, and whether the Reserved List guards it.',       'takes' => 'name'],
                ['n' => 'VII',  'tool' => 'get-banned-list',       'does' => "A format's whole banned or restricted roll, called out in alphabetical order.",                                                            'takes' => 'format · status'],
                ['n' => 'VIII', 'tool' => 'validate-deck',         'does' => 'A hundred cards weighed against the Commander laws — singleton, colour identity, legality, an eligible commander — and the bracket your Game Changers put you in.', 'takes' => 'decklist · commanders'],
            ] as $key)
                <div class="grid grid-cols-[44px_minmax(0,1fr)] md:grid-cols-[90px_minmax(0,1fr)_260px] gap-3.5 md:gap-7 items-start md:items-center py-4.5 md:py-6.5 px-1 md:px-4 border-t border-gold/18 last:border-b hover:bg-gold/5 transition-colors">
                    <div class="flex items-center justify-center w-11 h-11 md:w-[58px] md:h-[58px] rounded-full border border-gold/55 bg-gold/8 text-gold-bright text-[14px] md:text-[17px] font-medium tracking-[0.1em] indent-[0.1em]">{{ $key['n'] }}</div>
                    <div class="flex flex-col gap-1.5">
                        <div class="font-mono text-[14px] md:text-base text-parchment">{{ $key['tool'] }}</div>
                        <div class="text-base md:text-[17px] leading-[1.5] text-parchment/62 max-w-[620px]">{{ $key['does'] }}</div>
                        <div class="md:hidden font-mono text-xs text-accent/75">{{ $key['takes'] }}</div>
                    </div>
                    <div class="hidden md:block font-mono text-[13px] text-accent/75 text-right">{{ $key['takes'] }}</div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
