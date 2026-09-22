<?php

namespace App\Mcp\Tools;

use App\Services\Decklist\CommanderDeckValidator;
use App\Services\Decklist\DecklistParser;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Validate a Commander (EDH) decklist against the format rules: exactly 100 cards, the singleton rule, commander eligibility, Commander legality and colour identity. Accepts plain-text decklists as exported by Moxfield, Archidekt or MTG Arena, including "Commander" and "Sideboard" section headers. Also reports how many Commander Game Changers the deck contains and the lowest bracket that allows them.')]
#[IsReadOnly]
#[IsIdempotent]
class ValidateDeck extends Tool
{
    public function __construct(
        private readonly DecklistParser $parser,
        private readonly CommanderDeckValidator $validator,
    ) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'decklist' => 'required|string|max:30000',
            'commanders' => 'nullable|array|max:2',
            'commanders.*' => 'required|string|max:300',
        ]);

        $parsed = $this->parser->parse($validated['decklist']);

        if ($parsed['entries'] === [] && $parsed['commanders'] === []) {
            return Response::error('No card entries found in the decklist. Use one card per line, e.g. "1 Sol Ring".');
        }

        $result = $this->validator->validate($parsed, $validated['commanders'] ?? []);

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'decklist' => $schema->string()
                ->description('The decklist as plain text, one card per line ("1 Sol Ring", "1x Sol Ring (m21) 234"). Section headers such as "Commander", "Deck" and "Sideboard" are recognised; sideboard, maybeboard and token sections are ignored.')
                ->required(),
            'commanders' => $schema->array()
                ->items($schema->string())
                ->description('The commander name(s), one or two. Only needed when the decklist has no "Commander" section; it overrides that section when given.'),
        ];
    }
}
