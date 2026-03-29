<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Ruling;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ruling>
 */
class RulingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oracleId = fake()->uuid();
        $publishedAt = fake()->date();
        $comment = fake()->sentence();

        return [
            'oracle_id' => $oracleId,
            'source' => fake()->randomElement(['wotc', 'scryfall']),
            'published_at' => $publishedAt,
            'comment' => $comment,
            'content_hash' => hash('sha256', $oracleId . '|' . $publishedAt . '|' . $comment),
        ];
    }

    public function forCard(Card $card): static
    {
        return $this->state(fn () => [
            'oracle_id' => $card->oracle_id,
        ]);
    }
}
