# Azorsvault

> A Model Context Protocol (MCP) server for Magic: The Gathering. It gives Claude and other
> MCP clients {{ $toolCount }} read-only tools over every card Scryfall knows about, every
> ruling attached to them, and the full Comprehensive Rules{{ $rulesVersion ? " ({$rulesVersion})" : '' }}.

Connect it from the command line:

    claude mcp add --transport http azorsvault {{ url('/mcp/mtg') }}

Or hand the endpoint to any MCP-capable client: {{ url('/mcp/mtg') }}

## Tools

- search-card — one card by name, newest printing.
- search-cards — up to 100 names at once, for decklists.
- search-cards-advanced — stackable filters on name, mana cost, oracle text, type, subtype, colours, colour identity, rarity, set, keyword, power, toughness, mana value, format legality, EDHREC rank and Game Changer status; up to 50 unique cards.
- search-rules — full-text search across the Comprehensive Rules and glossary.
- get-rule — a rule, chapter, section or glossary term by number, with its subrules.
- check-legality — per-format legality for a card, plus Game Changer and Reserved List status.
- get-banned-list — a format's complete banned or restricted list.
- validate-deck — weighs a Commander decklist against deck size, singleton, colour identity, banned list, commander eligibility and bracket rules. Reads Moxfield, Archidekt, MTG Arena and plain-text exports.

## Pages

@foreach ($pages as $page)
- [{!! $page['title'] !!}]({{ route($page['route']) }}): {!! $page['summary'] !!}
@endforeach

## Notes

- Azorsvault is not affiliated with or endorsed by Wizards of the Coast. Card data comes from Scryfall.
- All tools are read-only and idempotent; the server stores nothing about who asks.
