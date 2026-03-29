<?php

namespace App\Mcp\Tools;

use App\Models\ComprehensiveRule;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Search the official Magic: The Gathering Comprehensive Rules by keyword or phrase. Searches rule text and glossary definitions. Use this to answer questions about game mechanics, timing, interactions, layers, state-based actions, combat, and other rules concepts.')]
#[IsReadOnly]
#[IsIdempotent]
class SearchRules extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => 'required|string|max:200',
            'section' => 'nullable|integer|min:1|max:9',
            'chapter' => 'nullable|string|max:10',
            'include_glossary' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = ComprehensiveRule::query();

        $query->byContentSearch($validated['query']);

        if (isset($validated['section'])) {
            $query->bySection($validated['section']);
        }

        if (! empty($validated['chapter'])) {
            $query->byChapter($validated['chapter']);
        }

        $includeGlossary = $validated['include_glossary'] ?? true;

        if (! $includeGlossary) {
            $query->rules();
        }

        $limit = $validated['limit'] ?? 20;

        $rules = $query
            ->orderBy('is_glossary')
            ->orderBy('rule_number')
            ->limit($limit)
            ->get();

        return Response::text(json_encode([
            'count' => $rules->count(),
            'rules' => $rules->map(fn (ComprehensiveRule $rule) => [
                'rule_number' => $rule->rule_number,
                'content' => $rule->content,
            ])->values()->all(),
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Text to search for in rule content. Searches both rules and glossary entries.')
                ->required(),
            'section' => $schema->integer()
                ->description('Filter to a specific section (1-9). 1=Game Concepts, 2=Parts of a Card, 3=Card Types, 4=Zones, 5=Turn Structure, 6=Spells/Abilities/Effects, 7=Additional Rules, 8=Multiplayer, 9=Casual Variants.'),
            'chapter' => $schema->string()
                ->description('Filter to a specific chapter. E.g. "704" for state-based actions, "702" for keyword abilities.'),
            'include_glossary' => $schema->boolean()
                ->description('Whether to include glossary entries in results. Defaults to true.'),
            'limit' => $schema->integer()
                ->description('Maximum number of results to return. Default 20, max 50.'),
        ];
    }
}
