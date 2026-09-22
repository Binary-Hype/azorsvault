<?php

namespace App\Mcp\Tools;

use App\Models\ComprehensiveRule;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Get a specific rule, chapter, or section from the official Magic: The Gathering Comprehensive Rules. Use a full rule number like "704.5m" for a single rule, or "702.19" to get that rule together with its lettered subrules. Use a chapter number like "704" for all state-based action rules, or a section number like "7" for all Additional Rules. Use "glossary:trample" to look up a specific glossary term.')]
#[IsReadOnly]
#[IsIdempotent]
class GetRule extends Tool
{
    private const MAX_RESULTS = 100;

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'rule_number' => 'required|string|max:50',
        ]);

        $input = trim($validated['rule_number']);

        // Glossary lookup: "glossary:trample"
        if (str_starts_with(mb_strtolower($input), 'glossary:')) {
            $rule = ComprehensiveRule::glossary()->byRuleNumber(mb_strtolower($input))->first();

            if (! $rule) {
                return Response::error("Glossary entry not found: \"{$input}\". Try using search-rules to search for the term.");
            }

            return Response::text(json_encode([
                'rule_number' => $rule->rule_number,
                'content' => $rule->content,
            ], JSON_PRETTY_PRINT));
        }

        // Section lookup: single digit "1" through "9"
        if (preg_match('/^\d$/', $input)) {
            return $this->fetchBySection((int) $input);
        }

        // Chapter lookup: three digits like "704"
        if (preg_match('/^\d{3}$/', $input)) {
            return $this->fetchByChapter($input);
        }

        // Exact rule lookup: "704.5" or "704.5m"
        $rule = ComprehensiveRule::byRuleNumber($input)->first();

        if (! $rule) {
            return Response::error("Rule not found: \"{$input}\". Try using search-rules to search by keyword.");
        }

        $result = [
            'rule_number' => $rule->rule_number,
            'content' => $rule->content,
        ];

        $subrules = $this->fetchSubrules($rule->rule_number);

        if ($subrules !== []) {
            $result['subrules'] = $subrules;
        }

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * Fetch the lettered subrules of a rule, so that "702.19" also returns
     * "702.19a" through "702.19z".
     *
     * A LIKE prefix alone would also match sibling rules such as "702.190",
     * so matches are narrowed to numbers that continue with letters only.
     *
     * @return array<int, array{rule_number: string, content: string}>
     */
    private function fetchSubrules(string $ruleNumber): array
    {
        $pattern = '^'.str_replace('.', '\\.', $ruleNumber).'[a-z]+$';

        return ComprehensiveRule::query()
            ->rules()
            ->where('rule_number', 'LIKE', $ruleNumber.'%')
            ->whereRaw('rule_number REGEXP ?', [$pattern])
            ->orderBy('rule_number')
            ->limit(self::MAX_RESULTS)
            ->get()
            ->map(fn (ComprehensiveRule $subrule) => [
                'rule_number' => $subrule->rule_number,
                'content' => $subrule->content,
            ])
            ->values()
            ->all();
    }

    private function fetchBySection(int $section): Response
    {
        return $this->respondWithRules(
            ComprehensiveRule::bySection($section)->rules(),
            "No rules found for section {$section}.",
        );
    }

    private function fetchByChapter(string $chapter): Response
    {
        return $this->respondWithRules(
            ComprehensiveRule::byChapter($chapter)->rules(),
            "No rules found for chapter {$chapter}.",
        );
    }

    /**
     * Return a capped, ordered list of rules, flagging when results were cut off.
     *
     * @param  Builder<ComprehensiveRule>  $query
     */
    private function respondWithRules(Builder $query, string $emptyMessage): Response
    {
        $total = (clone $query)->count();

        $rules = $query
            ->orderBy('rule_number')
            ->limit(self::MAX_RESULTS)
            ->get();

        if ($rules->isEmpty()) {
            return Response::error($emptyMessage);
        }

        $result = [
            'count' => $rules->count(),
            'total' => $total,
            'rules' => $rules->map(fn (ComprehensiveRule $rule) => [
                'rule_number' => $rule->rule_number,
                'content' => $rule->content,
            ])->all(),
        ];

        if ($total > self::MAX_RESULTS) {
            $result['note'] = 'Results truncated. Use a chapter number (e.g. "704") for more targeted results.';
        }

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'rule_number' => $schema->string()
                ->description('A rule number ("704.5m" or "702.19", which also returns its subrules), chapter ("704"), section ("7"), or glossary term ("glossary:trample").')
                ->required(),
        ];
    }
}
