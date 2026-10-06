<?php

namespace App\Mcp\Tools;

use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Look up multiple Magic: The Gathering cards by name at once. Accepts an array of card names and returns matches for each. Useful for decklist lookups. Returns the most recent printing of each card.')]
#[IsReadOnly]
#[IsIdempotent]
class SearchCards extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'names' => 'required|array|min:1|max:100',
            'names.*' => 'required|string|max:300',
        ]);

        $names = $validated['names'];

        $cards = Card::with('rulings')
            ->latestPrintingsNamed($names)
            ->get()
            ->keyBy(fn (Card $card) => mb_strtolower($card->name));

        $results = [];

        foreach ($names as $name) {
            $results[$name] = $cards->get(mb_strtolower($name))?->toSearchResult();
        }

        return Response::text(json_encode($results, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'names' => $schema->array()
                ->items($schema->string())
                ->description('Array of exact card names to look up. Max 100 names per request.')
                ->required(),
        ];
    }
}
