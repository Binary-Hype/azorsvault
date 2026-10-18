<?php

namespace App\Services\Scryfall;

use Generator;
use Illuminate\Console\OutputStyle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BulkDataService
{
    /** Sent with every outbound request to Scryfall and Wizards of the Coast. */
    public const USER_AGENT = 'MtgMCP/1.0';

    private const BULK_DATA_ENDPOINT = 'https://api.scryfall.com/bulk-data';

    private const ALLOWED_DOWNLOAD_PREFIX = 'https://data.scryfall.io/';

    /**
     * Backoff between attempts at the metadata endpoint, so a brief Scryfall
     * outage does not cost the whole nightly import.
     *
     * @var list<int>
     */
    private const METADATA_RETRY_DELAYS_MS = [5_000, 30_000, 60_000];

    /**
     * Look up a Scryfall bulk-data entry by its `type` field.
     *
     * Returns null when Scryfall stays unreachable after every retry; that is
     * an outage on their side, not a bug here, so the caller just skips the run.
     *
     * @return array{download_uri: string, size: int}|null
     */
    public function getBulkDataInfo(BulkDataType $type): ?array
    {
        try {
            $response = Http::withUserAgent(self::USER_AGENT)
                ->accept('application/json')
                ->retry(self::METADATA_RETRY_DELAYS_MS, throw: false)
                ->get(self::BULK_DATA_ENDPOINT);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        foreach ($response->json('data', []) as $entry) {
            if ($entry['type'] !== $type->value) {
                continue;
            }

            $uri = $entry['jsonl_download_uri'];

            if (! str_starts_with($uri, self::ALLOWED_DOWNLOAD_PREFIX)) {
                return null;
            }

            return [
                'download_uri' => $uri,
                'size' => $entry['compressed_size'],
            ];
        }

        return null;
    }

    /**
     * Stream a bulk-data file to disk, optionally rendering a progress bar.
     *
     * Pass `null` for $output to suppress progress output entirely.
     */
    public function downloadFile(string $url, string $destination, int $totalBytes, ?OutputStyle $output = null): bool
    {
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $progressBar = null;

        if ($output !== null) {
            $totalMb = round($totalBytes / 1024 / 1024, 1);
            $progressBar = $output->createProgressBar($totalBytes);
            $progressBar->setFormat(" Downloading %current_mb% / {$totalMb} MB [%bar%] %percent:3s%%");
            $progressBar->setMessage('0', 'current_mb');
            $progressBar->start();
        }

        $response = Http::withUserAgent(self::USER_AGENT)
            ->accept('application/json')
            ->withOptions([
                'sink' => $destination,
                'timeout' => 600,
                'progress' => $progressBar !== null
                    ? function (int $downloadTotal, int $downloadedBytes) use ($progressBar) {
                        if ($downloadedBytes > 0) {
                            $progressBar->setMessage((string) round($downloadedBytes / 1024 / 1024, 1), 'current_mb');
                            $progressBar->setProgress($downloadedBytes);
                        }
                    }
                    : null,
            ])
            ->get($url);

        if ($progressBar !== null && $output !== null) {
            $progressBar->finish();
            $output->newLine();
        }

        if (! $response->successful()) {
            /*
             * Guzzle writes the response body to the sink even on a 4xx/5xx, so
             * the destination exists but holds an error page. Remove it so a
             * later run cannot mistake it for a usable download.
             */
            @unlink($destination);

            return false;
        }

        return file_exists($destination);
    }

    /**
     * Scryfall bulk data files are JSON Lines (one object per line), not a
     * single top-level JSON array.
     *
     * @param  resource  $stream  A stream opened with gzopen().
     * @return Generator<int, array<string, mixed>>
     *
     * @throws \JsonException When a line is not valid JSON.
     */
    public function readJsonLines($stream): Generator
    {
        while (($line = gzgets($stream)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            yield json_decode($line, true, flags: JSON_THROW_ON_ERROR);
        }
    }
}
