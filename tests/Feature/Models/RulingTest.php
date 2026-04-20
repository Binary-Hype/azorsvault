<?php

use App\Models\Card;
use App\Models\Ruling;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('Card::rulings() relationship resolves via oracle_id, not id', function () {
    $card = Card::factory()->create();
    Ruling::factory()->forCard($card)->create(['comment' => 'First ruling.']);
    Ruling::factory()->forCard($card)->create(['comment' => 'Second ruling.']);

    Ruling::factory()->create();

    $card->refresh()->load('rulings');

    expect($card->rulings)->toHaveCount(2);
    expect($card->rulings->pluck('comment')->all())
        ->toContain('First ruling.', 'Second ruling.');
});

test('rulings are linked to cards that share an oracle_id across printings', function () {
    $oracleId = fake()->uuid();

    $printingA = Card::factory()->create(['oracle_id' => $oracleId, 'set' => 'a25']);
    $printingB = Card::factory()->create(['oracle_id' => $oracleId, 'set' => 'clu']);

    Ruling::factory()->create([
        'oracle_id' => $oracleId,
        'comment' => 'Shared ruling.',
    ]);

    expect($printingA->load('rulings')->rulings)->toHaveCount(1);
    expect($printingB->load('rulings')->rulings)->toHaveCount(1);
});

test('content_hash uniqueness prevents duplicate rulings', function () {
    $hash = hash('sha256', 'duplicate');

    Ruling::factory()->create(['content_hash' => $hash]);

    expect(fn () => Ruling::factory()->create(['content_hash' => $hash]))
        ->toThrow(QueryException::class);
});

test('published_at is cast to a Carbon date', function () {
    $ruling = Ruling::factory()->create(['published_at' => '2024-06-07']);

    expect($ruling->published_at)->toBeInstanceOf(Carbon::class);
    expect($ruling->published_at->toDateString())->toBe('2024-06-07');
});
