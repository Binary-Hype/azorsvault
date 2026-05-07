<?php

namespace App\Services\Scryfall;

use Illuminate\Console\OutputStyle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class BulkDataService
{
    private const USER_AGENT = 'MtgMCP/1.0';

    private const BULK_DATA_ENDPOINT = 'https://api.scryfall.com/bulk-data';

    private const ALLOWED_DOWNLOAD_PREFIX = 'https://data.scryfall.io/';

    /**
     * Look up a Scryfall bulk-data entry by its `type` field.
     *
     * @return array{download_uri: string, size: int}|null
     */
    public function getBulkDataInfo(BulkDataType $type): ?array
    {
        $response = Http::withUserAgent(self::USER_AGENT)
            ->accept('application/json')
            ->get(self::BULK_DATA_ENDPOINT);

        if (! $response->successful()) {
            return null;
        }

        foreach ($response->json('data', []) as $entry) {
            if ($entry['type'] !== $type->value) {
                continue;
            }

            $uri = $entry['download_uri'];

            if (! str_starts_with($uri, self::ALLOWED_DOWNLOAD_PREFIX)) {
                return null;
            }

            return [
                'download_uri' => $uri,
                'size' => $entry['size'],
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

        return $response->successful() || file_exists($destination);
    }

    /**
     * Bulk upsert a batch of rows into the given model's table.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $batch
     * @param  array<int, string>  $uniqueBy
     * @param  array<int, string>  $updateColumns
     */
    public function upsertBatch(string $modelClass, array $batch, array $uniqueBy, array $updateColumns): void
    {
        $modelClass::upsert($batch, $uniqueBy, $updateColumns);
    }
}
