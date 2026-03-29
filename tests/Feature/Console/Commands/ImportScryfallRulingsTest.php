<?php

use App\Console\Commands\ImportScryfallRulings;
use App\Models\Ruling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeScryfallBulkRulingsResponse(string $downloadUri = 'https://data.scryfall.io/rulings/rulings.json.gz', ?string $gzContent = null): void
{
    Http::fake([
        'api.scryfall.com/bulk-data' => Http::response([
            'data' => [
                [
                    'type' => 'rulings',
                    'download_uri' => $downloadUri,
                    'size' => 1024,
                ],
            ],
        ]),
        'data.scryfall.io/*' => Http::response($gzContent ?? '', 200),
    ]);
}

function makeGzippedRulingsJson(array $rulings): string
{
    return gzencode(json_encode($rulings));
}

test('it skips import when already imported today', function () {
    Cache::put('scryfall:last_rulings_import', now()->toDateString(), now()->addDay());

    $this->artisan('scryfall:import-rulings')
        ->expectsOutputToContain('Already imported today')
        ->assertSuccessful();
});

test('it runs import when forced even if already imported today', function () {
    Cache::put('scryfall:last_rulings_import', now()->toDateString(), now()->addDay());

    $oracleId = fake()->uuid();

    $gzContent = makeGzippedRulingsJson([
        [
            'oracle_id' => $oracleId,
            'source' => 'wotc',
            'published_at' => '2024-01-01',
            'comment' => 'This is a test ruling.',
        ],
    ]);

    fakeScryfallBulkRulingsResponse(gzContent: $gzContent);

    $this->artisan('scryfall:import-rulings --force --no-progress')
        ->assertSuccessful();

    expect(Ruling::where('oracle_id', $oracleId)->exists())->toBeTrue();
});

test('it rejects download URIs from untrusted domains', function () {
    Http::fake([
        'api.scryfall.com/bulk-data' => Http::response([
            'data' => [
                [
                    'type' => 'rulings',
                    'download_uri' => 'https://evil.example.com/malicious.json.gz',
                    'size' => 1024,
                ],
            ],
        ]),
    ]);

    $this->artisan('scryfall:import-rulings --force --no-progress')
        ->expectsOutputToContain('Could not find rulings bulk data')
        ->assertFailed();
});

test('it fails gracefully when scryfall api is unreachable', function () {
    Http::fake([
        'api.scryfall.com/bulk-data' => Http::response('', 500),
    ]);

    $this->artisan('scryfall:import-rulings --force --no-progress')
        ->expectsOutputToContain('Could not find rulings bulk data')
        ->assertFailed();
});

test('it extracts ruling data correctly from scryfall format', function () {
    $command = new ImportScryfallRulings;

    $scryfallRuling = [
        'oracle_id' => 'b7c01b1c-a8e2-4234-aec9-3a2d6c58e0bd',
        'source' => 'wotc',
        'published_at' => '2024-06-07',
        'comment' => 'Lightning Bolt deals 3 damage to any target.',
    ];

    $reflection = new ReflectionMethod($command, 'extractRulingData');
    $result = $reflection->invoke($command, $scryfallRuling);

    expect($result)
        ->toHaveKey('oracle_id', 'b7c01b1c-a8e2-4234-aec9-3a2d6c58e0bd')
        ->toHaveKey('source', 'wotc')
        ->toHaveKey('published_at', '2024-06-07')
        ->toHaveKey('comment', 'Lightning Bolt deals 3 damage to any target.');

    $expectedHash = hash('sha256', 'b7c01b1c-a8e2-4234-aec9-3a2d6c58e0bd|2024-06-07|Lightning Bolt deals 3 damage to any target.');
    expect($result['content_hash'])->toBe($expectedHash);
});

test('it removes stale rulings not present in import', function () {
    $staleRuling = Ruling::factory()->create([
        'comment' => 'This ruling no longer exists.',
        'updated_at' => now()->subDay(),
    ]);

    $oracleId = fake()->uuid();
    $gzContent = makeGzippedRulingsJson([
        [
            'oracle_id' => $oracleId,
            'source' => 'wotc',
            'published_at' => '2024-01-01',
            'comment' => 'This is the current ruling.',
        ],
    ]);

    fakeScryfallBulkRulingsResponse(gzContent: $gzContent);

    $this->artisan('scryfall:import-rulings --force --no-progress')
        ->assertSuccessful();

    expect(Ruling::find($staleRuling->id))->toBeNull();
    expect(Ruling::where('oracle_id', $oracleId)->exists())->toBeTrue();
});

test('it sets cache key after successful import', function () {
    Cache::forget('scryfall:last_rulings_import');

    $gzContent = makeGzippedRulingsJson([
        [
            'oracle_id' => fake()->uuid(),
            'source' => 'scryfall',
            'published_at' => '2024-03-15',
            'comment' => 'Cache test ruling.',
        ],
    ]);

    fakeScryfallBulkRulingsResponse(gzContent: $gzContent);

    $this->artisan('scryfall:import-rulings --force --no-progress')
        ->assertSuccessful();

    expect(Cache::get('scryfall:last_rulings_import'))->toBe(now()->toDateString());
});
