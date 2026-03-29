<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $color = fake()->randomElement(['W', 'U', 'B', 'R', 'G']);

        return [
            'id' => fake()->uuid(),
            'oracle_id' => fake()->uuid(),
            'name' => ucwords(fake()->words(rand(1, 3), true)),
            'mana_cost' => '{' . rand(0, 5) . '}{' . $color . '}',
            'cmc' => fake()->numberBetween(1, 8),
            'type_line' => 'Creature — ' . fake()->word(),
            'oracle_text' => fake()->sentence(),
            'colors' => [$color],
            'color_identity' => [$color],
            'keywords' => [],
            'power' => (string) fake()->numberBetween(1, 7),
            'toughness' => (string) fake()->numberBetween(1, 7),
            'loyalty' => null,
            'layout' => 'normal',
            'set' => fake()->lexify('???'),
            'set_name' => ucwords(fake()->words(2, true)),
            'collector_number' => (string) fake()->unique()->numberBetween(1, 999),
            'rarity' => fake()->randomElement(['common', 'uncommon', 'rare', 'mythic']),
            'released_at' => fake()->date(),
            'reprint' => false,
            'digital' => false,
            'reserved' => false,
            'image_uris' => ['normal' => 'https://example.com/card.jpg'],
            'legalities' => ['standard' => 'legal', 'commander' => 'legal', 'modern' => 'legal'],
            'prices' => ['usd' => (string) fake()->randomFloat(2, 0.10, 50.00)],
            'edhrec_rank' => fake()->numberBetween(1, 30000),
            'flavor_text' => fake()->sentence(),
            'games' => ['paper'],
            'finishes' => ['nonfoil'],
            'card_faces' => null,
            'all_parts' => null,
        ];
    }

    public function creature(): static
    {
        return $this->state(fn () => [
            'type_line' => 'Creature — ' . fake()->randomElement(['Human', 'Elf', 'Goblin', 'Dragon', 'Wizard']),
            'power' => (string) fake()->numberBetween(1, 10),
            'toughness' => (string) fake()->numberBetween(1, 10),
            'keywords' => fake()->randomElements(['Flying', 'Trample', 'Haste', 'Deathtouch', 'Lifelink'], rand(0, 2)),
        ]);
    }

    public function instant(): static
    {
        return $this->state(fn () => [
            'type_line' => 'Instant',
            'power' => null,
            'toughness' => null,
        ]);
    }

    public function dfc(): static
    {
        $frontName = ucwords(fake()->words(2, true));
        $backName = ucwords(fake()->words(2, true));
        $color = fake()->randomElement(['W', 'U', 'B', 'R', 'G']);

        return $this->state(fn () => [
            'name' => $frontName . ' // ' . $backName,
            'layout' => 'transform',
            'mana_cost' => '{1}{' . $color . '}',
            'oracle_text' => fake()->sentence(),
            'colors' => [$color],
            'card_faces' => [
                [
                    'name' => $frontName,
                    'mana_cost' => '{1}{' . $color . '}',
                    'type_line' => 'Creature — Human',
                    'oracle_text' => fake()->sentence(),
                    'colors' => [$color],
                    'power' => '1',
                    'toughness' => '1',
                ],
                [
                    'name' => $backName,
                    'mana_cost' => '',
                    'type_line' => 'Creature — Insect',
                    'oracle_text' => fake()->sentence(),
                    'colors' => [$color],
                    'power' => '3',
                    'toughness' => '2',
                ],
            ],
        ]);
    }

    public function commanderLegal(): static
    {
        return $this->state(fn () => [
            'legalities' => ['commander' => 'legal', 'standard' => 'not_legal', 'modern' => 'legal'],
        ]);
    }
}
