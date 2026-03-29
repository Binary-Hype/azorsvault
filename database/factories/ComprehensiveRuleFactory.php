<?php

namespace Database\Factories;

use App\Models\ComprehensiveRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComprehensiveRule>
 */
class ComprehensiveRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chapter = (string) fake()->numberBetween(100, 905);
        $sub = fake()->numberBetween(1, 20);

        return [
            'rule_number' => $chapter.'.'.$sub,
            'section' => (int) $chapter[0],
            'chapter' => $chapter,
            'content' => fake()->sentence(),
            'is_glossary' => false,
            'effective_date' => '2026-02-27',
        ];
    }

    public function glossary(?string $term = null): static
    {
        $term ??= fake()->word();

        return $this->state([
            'rule_number' => 'glossary:'.mb_strtolower($term),
            'section' => null,
            'chapter' => null,
            'is_glossary' => true,
        ]);
    }
}
