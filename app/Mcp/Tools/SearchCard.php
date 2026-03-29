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

#[Description('Find a single Magic: The Gathering card by exact name. Returns the most recent printing. For double-faced cards, use the full name with " // " separator, e.g. "Delver of Secrets // Insectile Aberration".')]
#[IsReadOnly]
#[IsIdempotent]
class SearchCard extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:300',
        ]);

        $card = Card::byExactName($validated['name'])
            ->orderByDesc('released_at')
            ->first();

        if (! $card) {
            return Response::error("Card not found: \"{$validated['name']}\". Try using search-cards-advanced with a name search for partial matches.");
        }

        return Response::text(json_encode($card->toSearchResult(), JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('The exact card name to search for. Case-insensitive.')
                ->required(),
        ];
    }
}
