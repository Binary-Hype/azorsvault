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

#[Description('Get the complete banned or restricted list for a Magic: The Gathering format, e.g. the Commander banned list or the Vintage restricted list. Returns card names sorted alphabetically.')]
#[IsReadOnly]
#[IsIdempotent]
class GetBannedList extends Tool
{
    private const MAX_RESULTS = 500;

    /**
     * Formats tracked in the Scryfall legality data.
     */
    private const FORMATS = [
        'standard', 'future', 'historic', 'timeless', 'gladiator', 'pioneer',
        'explorer', 'modern', 'legacy', 'pauper', 'vintage', 'penny',
        'commander', 'oathbreaker', 'standardbrawl', 'brawl', 'alchemy',
        'paupercommander', 'duel', 'oldschool', 'premodern', 'predh',
    ];

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'format' => ['required', 'string', 'in:'.implode(',', self::FORMATS)],
            'status' => 'nullable|string|in:banned,restricted',
        ]);

        $format = $validated['format'];
        $status = $validated['status'] ?? 'banned';

        $names = Card::query()
            ->byLegality($format, $status)
            ->distinct()
            ->orderBy('name')
            ->limit(self::MAX_RESULTS + 1)
            ->pluck('name')
            ->all();

        $truncated = count($names) > self::MAX_RESULTS;
        $names = array_slice($names, 0, self::MAX_RESULTS);

        if ($names === []) {
            return Response::error("No cards are {$status} in {$format}.");
        }

        $result = [
            'format' => $format,
            'status' => $status,
            'count' => count($names),
            'cards' => $names,
        ];

        if ($truncated) {
            $result['note'] = 'Results truncated at '.self::MAX_RESULTS.' cards.';
        }

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'format' => $schema->string()
                ->enum(self::FORMATS)
                ->description('The format whose list to fetch, e.g. "commander", "modern", "vintage".')
                ->required(),
            'status' => $schema->string()
                ->enum(['banned', 'restricted'])
                ->description('Whether to list banned or restricted cards. Defaults to "banned".'),
        ];
    }
}
