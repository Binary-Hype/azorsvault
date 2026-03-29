<?php

use App\Console\Commands\ImportScryfallCards;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeScryfallBulkDataResponse(string $downloadUri = 'https://data.scryfall.io/default-cards/default-cards.json.gz', ?string $gzContent = null): void
{
    Http::fake([
        'api.scryfall.com/bulk-data' => Http::response([
            'data' => [
                [
                    'type' => 'default_cards',
                    'download_uri' => $downloadUri,
                    'size' => 1024,
                ],
            ],
        ]),
        'data.scryfall.io/*' => Http::response($gzContent ?? '', 200),
    ]);
}

function makeGzippedCardJson(array $cards): string
{
    return gzencode(json_encode($cards));
}

test('it skips import when already imported today', function () {
    Cache::put('scryfall:last_import', now()->toDateString(), now()->addDay());

    $this->artisan('scryfall:import-cards')
        ->expectsOutputToContain('Already imported today')
        ->assertSuccessful();
});

test('it runs import when forced even if already imported today', function () {
    Cache::put('scryfall:last_import', now()->toDateString(), now()->addDay());

    $gzContent = makeGzippedCardJson([
        [
            'id' => fake()->uuid(),
            'oracle_id' => fake()->uuid(),
            'name' => 'Test Card',
            'mana_cost' => '{1}{R}',
            'cmc' => 2.0,
            'type_line' => 'Instant',
            'oracle_text' => 'Deal 3 damage.',
            'colors' => ['R'],
            'color_identity' => ['R'],
            'keywords' => [],
            'layout' => 'normal',
            'set' => 'tst',
            'set_name' => 'Test Set',
            'collector_number' => '1',
            'rarity' => 'common',
            'released_at' => '2024-01-01',
            'reprint' => false,
            'digital' => false,
            'reserved' => false,
            'games' => ['paper'],
            'finishes' => ['nonfoil'],
        ],
    ]);

    fakeScryfallBulkDataResponse(gzContent: $gzContent);

    $this->artisan('scryfall:import-cards --force --no-progress')
        ->assertSuccessful();

    expect(Card::where('name', 'Test Card')->exists())->toBeTrue();
});

test('it rejects download URIs from untrusted domains', function () {
    Http::fake([
        'api.scryfall.com/bulk-data' => Http::response([
            'data' => [
                [
                    'type' => 'default_cards',
                    'download_uri' => 'https://evil.example.com/malicious.json.gz',
                    'size' => 1024,
                ],
            ],
        ]),
    ]);

    $this->artisan('scryfall:import-cards --force --no-progress')
        ->expectsOutputToContain('Could not find default_cards bulk data')
        ->assertFailed();
});

test('it fails gracefully when scryfall api is unreachable', function () {
    Http::fake([
        'api.scryfall.com/bulk-data' => Http::response('', 500),
    ]);

    $this->artisan('scryfall:import-cards --force --no-progress')
        ->expectsOutputToContain('Could not find default_cards bulk data')
        ->assertFailed();
});

test('it extracts card data correctly from scryfall format', function () {
    $command = new ImportScryfallCards;

    $scryfallCard = [
        'id' => '550c74d4-1fcb-406a-b02a-639a760a4380',
        'oracle_id' => 'b7c01b1c-a8e2-4234-aec9-3a2d6c58e0bd',
        'name' => 'Lightning Bolt',
        'mana_cost' => '{R}',
        'cmc' => 1.0,
        'type_line' => 'Instant',
        'oracle_text' => 'Lightning Bolt deals 3 damage to any target.',
        'colors' => ['R'],
        'color_identity' => ['R'],
        'keywords' => [],
        'power' => null,
        'toughness' => null,
        'loyalty' => null,
        'layout' => 'normal',
        'set' => 'lea',
        'set_name' => 'Limited Edition Alpha',
        'collector_number' => '161',
        'rarity' => 'common',
        'released_at' => '1993-08-05',
        'reprint' => false,
        'digital' => false,
        'reserved' => false,
        'image_uris' => ['normal' => 'https://example.com/bolt.jpg'],
        'legalities' => ['modern' => 'legal', 'commander' => 'legal'],
        'prices' => ['usd' => '5.00'],
        'edhrec_rank' => 5,
        'flavor_text' => null,
        'games' => ['paper'],
        'finishes' => ['nonfoil'],
        'card_faces' => null,
        'all_parts' => null,
    ];

    $reflection = new ReflectionMethod($command, 'extractCardData');
    $result = $reflection->invoke($command, $scryfallCard);

    expect($result)
        ->toHaveKey('id', '550c74d4-1fcb-406a-b02a-639a760a4380')
        ->toHaveKey('name', 'Lightning Bolt')
        ->toHaveKey('mana_cost', '{R}')
        ->toHaveKey('oracle_text', 'Lightning Bolt deals 3 damage to any target.')
        ->toHaveKey('layout', 'normal')
        ->toHaveKey('set', 'lea');

    expect($result['colors'])->toBe(json_encode(['R']));
    expect($result['keywords'])->toBe(json_encode([]));
});

test('it extracts front face data for double-faced cards', function () {
    $command = new ImportScryfallCards;

    $scryfallCard = [
        'id' => 'abc-123',
        'oracle_id' => 'def-456',
        'name' => 'Delver of Secrets // Insectile Aberration',
        'mana_cost' => null,
        'cmc' => 1.0,
        'type_line' => 'Creature — Human Wizard // Creature — Human Insect',
        'oracle_text' => null,
        'colors' => null,
        'color_identity' => ['U'],
        'keywords' => ['Flying', 'Transform'],
        'power' => null,
        'toughness' => null,
        'loyalty' => null,
        'layout' => 'transform',
        'set' => 'isd',
        'set_name' => 'Innistrad',
        'collector_number' => '51',
        'rarity' => 'common',
        'released_at' => '2011-09-30',
        'reprint' => false,
        'digital' => false,
        'reserved' => false,
        'image_uris' => null,
        'legalities' => ['modern' => 'legal'],
        'prices' => ['usd' => '1.00'],
        'edhrec_rank' => 100,
        'flavor_text' => null,
        'games' => ['paper'],
        'finishes' => ['nonfoil'],
        'card_faces' => [
            [
                'name' => 'Delver of Secrets',
                'mana_cost' => '{U}',
                'oracle_text' => 'At the beginning of your upkeep, look at the top card of your library.',
                'colors' => ['U'],
                'power' => '1',
                'toughness' => '1',
            ],
            [
                'name' => 'Insectile Aberration',
                'mana_cost' => '',
                'oracle_text' => 'Flying',
                'colors' => ['U'],
                'power' => '3',
                'toughness' => '2',
            ],
        ],
        'all_parts' => null,
    ];

    $reflection = new ReflectionMethod($command, 'extractCardData');
    $result = $reflection->invoke($command, $scryfallCard);

    expect($result)
        ->toHaveKey('mana_cost', '{U}')
        ->toHaveKey('oracle_text', 'At the beginning of your upkeep, look at the top card of your library.')
        ->toHaveKey('power', '1')
        ->toHaveKey('toughness', '1');

    expect($result['colors'])->toBe(json_encode(['U']));
});

test('it removes stale cards not present in import', function () {
    $staleCard = Card::factory()->create([
        'name' => 'Stale Card',
        'updated_at' => now()->subDay(),
    ]);

    $freshId = fake()->uuid();
    $gzContent = makeGzippedCardJson([
        [
            'id' => $freshId,
            'oracle_id' => fake()->uuid(),
            'name' => 'Fresh Card',
            'mana_cost' => '{R}',
            'cmc' => 1.0,
            'type_line' => 'Instant',
            'oracle_text' => 'Deal 3 damage.',
            'colors' => ['R'],
            'color_identity' => ['R'],
            'keywords' => [],
            'layout' => 'normal',
            'set' => 'tst',
            'set_name' => 'Test Set',
            'collector_number' => '1',
            'rarity' => 'common',
            'released_at' => '2024-01-01',
            'reprint' => false,
            'digital' => false,
            'reserved' => false,
            'games' => ['paper'],
            'finishes' => ['nonfoil'],
        ],
    ]);

    fakeScryfallBulkDataResponse(gzContent: $gzContent);

    $this->artisan('scryfall:import-cards --force --no-progress')
        ->assertSuccessful();

    expect(Card::find($staleCard->id))->toBeNull();
    expect(Card::find($freshId))->not->toBeNull();
});

test('it sets cache key after successful import', function () {
    Cache::forget('scryfall:last_import');

    $gzContent = makeGzippedCardJson([
        [
            'id' => fake()->uuid(),
            'oracle_id' => fake()->uuid(),
            'name' => 'Cache Test Card',
            'mana_cost' => '{G}',
            'cmc' => 1.0,
            'type_line' => 'Creature — Elf',
            'oracle_text' => 'Tap: Add {G}.',
            'colors' => ['G'],
            'color_identity' => ['G'],
            'keywords' => [],
            'layout' => 'normal',
            'set' => 'tst',
            'set_name' => 'Test Set',
            'collector_number' => '2',
            'rarity' => 'common',
            'released_at' => '2024-01-01',
            'reprint' => false,
            'digital' => false,
            'reserved' => false,
            'games' => ['paper'],
            'finishes' => ['nonfoil'],
        ],
    ]);

    fakeScryfallBulkDataResponse(gzContent: $gzContent);

    $this->artisan('scryfall:import-cards --force --no-progress')
        ->assertSuccessful();

    expect(Cache::get('scryfall:last_import'))->toBe(now()->toDateString());
});
