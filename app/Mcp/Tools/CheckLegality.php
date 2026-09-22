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

#[Description('Check which formats a Magic: The Gathering card is legal in. Returns the full legality map plus the formats the card is banned or restricted in, and whether it is a Commander Game Changer or on the Reserved List. Use this instead of a full card lookup when the question is only about legality.')]
#[IsReadOnly]
#[IsIdempotent]
class CheckLegality extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:300',
        ]);

        $card = Card::query()
            ->byExactName($validated['name'])
            ->orderByDesc('released_at')
            ->first();

        if (! $card) {
            return Response::error("Card not found: \"{$validated['name']}\". Try using search-cards-advanced with a name search for partial matches.");
        }

        $legalities = $card->legalities ?? [];

        return Response::text(json_encode([
            'name' => $card->name,
            'legalities' => $legalities,
            'banned_in' => array_keys(array_filter($legalities, fn (string $status) => $status === 'banned')),
            'restricted_in' => array_keys(array_filter($legalities, fn (string $status) => $status === 'restricted')),
            'game_changer' => (bool) $card->game_changer,
            'reserved' => (bool) $card->reserved,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('The exact card name to check. Case-insensitive. For double-faced cards use the full name with " // " separator.')
                ->required(),
        ];
    }
}
