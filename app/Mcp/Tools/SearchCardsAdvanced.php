<?php

namespace App\Mcp\Tools;

use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Search Magic: The Gathering cards using multiple filters. All provided filters are combined with AND logic. Text searches on name and oracle_text use partial matching. Returns up to 50 results, grouped by oracle_id to show unique cards rather than every printing.')]
#[IsReadOnly]
#[IsIdempotent]
class SearchCardsAdvanced extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:300',
            'mana_cost' => 'nullable|string|max:100',
            'oracle_text' => 'nullable|string|max:500',
            'type_line' => 'nullable|string|max:300',
            'subtype' => 'nullable|string|max:100',
            'colors' => 'nullable|array',
            'colors.*' => 'string|in:W,U,B,R,G',
            'color_identity' => 'nullable|array',
            'color_identity.*' => 'string|in:W,U,B,R,G',
            'rarity' => 'nullable|string|in:common,uncommon,rare,mythic',
            'set' => 'nullable|string|max:10',
            'keyword' => 'nullable|string|max:50',
            'power' => 'nullable|string|max:10',
            'toughness' => 'nullable|string|max:10',
            'cmc' => 'nullable|numeric|min:0',
            'cmc_operator' => 'nullable|string|in:=,<,>,<=,>=',
            'format' => 'nullable|string|max:30',
            'legality' => 'nullable|string|in:legal,not_legal,restricted,banned',
            'max_edhrec_rank' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $hasFilter = collect($validated)->except('limit', 'cmc_operator', 'legality')->filter()->isNotEmpty();

        if (! $hasFilter) {
            return Response::error('At least one search filter must be provided.');
        }

        $query = Card::query();

        $scopes = [
            'name' => 'byNameSearch',
            'mana_cost' => 'byManaCost',
            'oracle_text' => 'byOracleText',
            'type_line' => 'byTypeLine',
            'subtype' => 'bySubtype',
            'colors' => 'byColors',
            'color_identity' => 'byColorIdentity',
            'rarity' => 'byRarity',
            'set' => 'bySet',
            'keyword' => 'byKeyword',
            'power' => 'byPower',
            'toughness' => 'byToughness',
            'max_edhrec_rank' => 'byMaxEdhrecRank',
        ];

        foreach ($scopes as $field => $scope) {
            if (! empty($validated[$field])) {
                $query->{$scope}($validated[$field]);
            }
        }

        if (isset($validated['cmc'])) {
            $query->byCmc((float) $validated['cmc'], $validated['cmc_operator'] ?? '=');
        }

        if (! empty($validated['format'])) {
            $query->byLegality($validated['format'], $validated['legality'] ?? 'legal');
        }

        $limit = $validated['limit'] ?? 20;

        $cards = $query
            ->orderByDesc('released_at')
            ->limit($limit * 3)
            ->get()
            ->unique('oracle_id')
            ->take($limit)
            ->map(fn (Card $card) => $card->toSearchResult())
            ->values();

        return Response::text(json_encode([
            'count' => $cards->count(),
            'cards' => $cards,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Search card names using fulltext matching. Partial matches supported.'),
            'mana_cost' => $schema->string()
                ->description('Exact mana cost to match, e.g. "{2}{R}{R}" or "{G}".'),
            'oracle_text' => $schema->string()
                ->description('Search rules text using fulltext matching. E.g. "draw a card" or "destroy target creature".'),
            'type_line' => $schema->string()
                ->description('Partial match on the type line. E.g. "Creature", "Legendary", "Artifact".'),
            'subtype' => $schema->string()
                ->description('Search for a specific subtype (after the em-dash). E.g. "Wizard", "Dragon", "Equipment".'),
            'colors' => $schema->array()
                ->items($schema->string()->enum(['W', 'U', 'B', 'R', 'G']))
                ->description('Filter by card colors. W=White, U=Blue, B=Black, R=Red, G=Green. Cards must contain ALL specified colors.'),
            'color_identity' => $schema->array()
                ->items($schema->string()->enum(['W', 'U', 'B', 'R', 'G']))
                ->description('Filter by color identity (for Commander). Cards must contain ALL specified colors in their identity.'),
            'rarity' => $schema->string()
                ->enum(['common', 'uncommon', 'rare', 'mythic'])
                ->description('Filter by rarity.'),
            'set' => $schema->string()
                ->description('Filter by set code. E.g. "m21", "neo", "mkm".'),
            'keyword' => $schema->string()
                ->description('Filter by keyword ability. E.g. "Flying", "Trample", "Deathtouch".'),
            'power' => $schema->string()
                ->description('Filter by power. E.g. "3", "*".'),
            'toughness' => $schema->string()
                ->description('Filter by toughness. E.g. "4", "*".'),
            'cmc' => $schema->number()
                ->description('Filter by converted mana cost (mana value). Used with cmc_operator.'),
            'cmc_operator' => $schema->string()
                ->enum(['=', '<', '>', '<=', '>='])
                ->description('Comparison operator for cmc filter. Defaults to "=".'),
            'format' => $schema->string()
                ->description('Filter by format legality. E.g. "standard", "commander", "modern", "legacy", "pioneer", "pauper", "vintage".'),
            'legality' => $schema->string()
                ->enum(['legal', 'not_legal', 'restricted', 'banned'])
                ->description('Legality status for the format filter. Defaults to "legal".'),
            'max_edhrec_rank' => $schema->integer()
                ->description('Maximum EDHREC rank. Lower numbers = more popular in Commander. E.g. 100 for top 100 cards.'),
            'limit' => $schema->integer()
                ->description('Maximum number of results to return. Default 20, max 50.'),
        ];
    }
}
