# Azorsvault — Magic: The Gathering MCP

An [MCP](https://modelcontextprotocol.io) server that wires the Scryfall card database and the Magic: The Gathering Comprehensive Rules into Claude (or any MCP-compatible client).

Built on [Laravel](https://laravel.com) + [`laravel/mcp`](https://github.com/laravel/mcp).

## Tools

| Tool | Purpose |
| --- | --- |
| `search-card` | Find a single card by exact name. Returns the most recent printing. |
| `search-cards` | Batch lookup by name (1–100). Useful for decklists. Returns `name → card | null`. |
| `search-cards-advanced` | Multi-filter search across colors, mana cost, type, oracle text, rarity, format legality, EDHREC rank, and more. All filters AND-combined; deduped by `oracle_id`; max 50 results. |
| `search-rules` | Keyword search across the Comprehensive Rules and glossary. |
| `get-rule` | Fetch a precise rule, chapter, section, or glossary term by number. |

## Integrating with Claude

The server is exposed over **streamable HTTP** at `/mcp/mtg`.

### Hosted instance

Point your client at the hosted URL (replace with your deployment if self-hosting):

```
https://<your-domain>/mcp/mtg
```

### Claude Code CLI

```bash
claude mcp add --transport http azorsvault https://<your-domain>/mcp/mtg
```

### Claude Desktop / Claude.ai

Add a custom connector and paste the URL above. No auth required — the endpoint is public, throttled at 300 req/min/IP.

### Other MCP clients

Any client that speaks the streamable-HTTP MCP transport works. Use the same URL.

## Self-hosting

Requirements: PHP 8.4, Composer, Node 20+, a database (SQLite by default).

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

The MCP routes are registered in `routes/ai.php`:

```php
Mcp::local('mtg', MtgServer::class);                       // stdio / CLI
Mcp::web('/mcp/mtg', MtgServer::class)->middleware(...);   // streamable HTTP
```

### Local stdio transport

To run the server against a local checkout (no HTTP), register the artisan binding directly with your client:

```bash
claude mcp add azorsvault -- php /path/to/mtg-mcp/artisan mcp:start mtg
```

### DDEV

This project ships with DDEV. Prefix commands accordingly:

```bash
ddev composer install
ddev npm install
ddev artisan migrate
ddev npm run build
```

The DDEV URL (`https://mtg-mcp.ddev.site/mcp/mtg`) is reachable from MCP clients running on the same host.

## Inspecting the server

```bash
php artisan mcp:inspector mtg
```

Lists tools, schemas, and lets you fire test calls.

## Tests

```bash
php artisan test --compact
```

## License

MIT.
