<?php

use App\Services\Scryfall\BulkDataService;
use App\Services\Scryfall\BulkDataType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function () {
    $this->destination = storage_path('app/private/testing/bulk-data-test.jsonl.gz');

    if (file_exists($this->destination)) {
        unlink($this->destination);
    }
});

afterEach(function () {
    if (file_exists($this->destination)) {
        unlink($this->destination);
    }
});

test('it reports a successful download', function () {
    Http::fake(['data.scryfall.io/*' => Http::response('payload', 200)]);

    $downloaded = app(BulkDataService::class)
        ->downloadFile('https://data.scryfall.io/cards.jsonl.gz', $this->destination, 1024);

    expect($downloaded)->toBeTrue();
    expect(file_exists($this->destination))->toBeTrue();
});

/*
 * Guzzle streams the response body to the sink even on a 4xx/5xx, so the file
 * exists after a failed request. Reporting that as success let the import run
 * against an error page.
 */
test('it reports a failed download even though the sink file exists', function () {
    Http::fake(['data.scryfall.io/*' => Http::response('Not Found', 404)]);

    $downloaded = app(BulkDataService::class)
        ->downloadFile('https://data.scryfall.io/cards.jsonl.gz', $this->destination, 1024);

    expect($downloaded)->toBeFalse();
});

test('it discards the error body so a later run cannot reuse it', function () {
    Http::fake(['data.scryfall.io/*' => Http::response('Not Found', 404)]);

    app(BulkDataService::class)
        ->downloadFile('https://data.scryfall.io/cards.jsonl.gz', $this->destination, 1024);

    expect(file_exists($this->destination))->toBeFalse();
});

test('it retries the metadata lookup after a connection timeout', function () {
    Sleep::fake();

    Http::fakeSequence('api.scryfall.com/bulk-data')
        ->pushFailedConnection()
        ->push([
            'data' => [
                [
                    'type' => 'default_cards',
                    'jsonl_download_uri' => 'https://data.scryfall.io/default-cards/default-cards.jsonl.gz',
                    'compressed_size' => 1024,
                ],
            ],
        ]);

    $info = app(BulkDataService::class)->getBulkDataInfo(BulkDataType::DefaultCards);

    expect($info)->toBe([
        'download_uri' => 'https://data.scryfall.io/default-cards/default-cards.jsonl.gz',
        'size' => 1024,
    ]);
});

/*
 * A Scryfall outage used to escape as an unhandled ConnectionException from the
 * nightly import. It is their problem, not ours, so the lookup gives up quietly.
 */
test('it returns null when scryfall stays unreachable', function () {
    Sleep::fake();

    Http::fake(['api.scryfall.com/bulk-data' => Http::failedConnection()]);

    $info = app(BulkDataService::class)->getBulkDataInfo(BulkDataType::DefaultCards);

    expect($info)->toBeNull();
    Http::assertSentCount(4);
});
