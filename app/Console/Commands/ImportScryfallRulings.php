<?php

namespace App\Console\Commands;

use App\Models\Ruling;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Items;

#[Signature('scryfall:import-rulings {--force : Force import even if already imported today} {--no-progress : Hide progress bars}')]
#[Description('Download and import Scryfall bulk rulings data')]
class ImportScryfallRulings extends Command
{
    private const BATCH_SIZE = 500;

    private const CACHE_KEY = 'scryfall:last_rulings_import';

    public function handle(): int
    {
        if (! $this->option('force') && Cache::get(self::CACHE_KEY) === now()->toDateString()) {
            $this->info('Already imported today. Use --force to re-import.');

            return self::SUCCESS;
        }

        $this->info('Fetching Scryfall bulk data metadata...');
        $bulkData = $this->getBulkDataInfo();

        if (! $bulkData) {
            $this->error('Could not find rulings bulk data from Scryfall.');

            return self::FAILURE;
        }

        $storagePath = 'scryfall/rulings.json.gz';
        $fullPath = storage_path('app/private/'.$storagePath);

        if (! $this->downloadFile($bulkData['download_uri'], $fullPath, $bulkData['size'])) {
            return self::FAILURE;
        }

        $this->info('Importing rulings into database...');
        $importStartedAt = now();
        $count = $this->importRulings($fullPath);

        $deleted = Ruling::where('updated_at', '<', $importStartedAt)->delete();

        Storage::disk('local')->delete($storagePath);

        Cache::put(self::CACHE_KEY, now()->toDateString(), now()->addDay());

        $this->info("Successfully imported {$count} rulings. Removed {$deleted} stale rulings.");

        return self::SUCCESS;
    }

    /**
     * @return array{download_uri: string, size: int}|null
     */
    private function getBulkDataInfo(): ?array
    {
        $response = Http::withUserAgent('MtgMCP/1.0')->accept('application/json')
            ->get('https://api.scryfall.com/bulk-data');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('data', []);

        foreach ($data as $entry) {
            if ($entry['type'] === 'rulings') {
                $uri = $entry['download_uri'];

                if (! str_starts_with($uri, 'https://data.scryfall.io/')) {
                    return null;
                }

                return [
                    'download_uri' => $uri,
                    'size' => $entry['size'],
                ];
            }
        }

        return null;
    }

    private function downloadFile(string $url, string $destination, int $totalBytes): bool
    {
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $showProgress = ! $this->option('no-progress');
        $totalMb = round($totalBytes / 1024 / 1024, 1);

        if ($showProgress) {
            $progressBar = $this->output->createProgressBar($totalBytes);
            $progressBar->setFormat(" Downloading %current_mb% / {$totalMb} MB [%bar%] %percent:3s%%");
            $progressBar->setMessage('0', 'current_mb');
            $progressBar->start();
        }

        $response = Http::withUserAgent('MtgMCP/1.0')->accept('application/json')
            ->withOptions([
                'sink' => $destination,
                'timeout' => 600,
                'progress' => $showProgress
                    ? function (int $downloadTotal, int $downloadedBytes) use (&$progressBar) {
                        if ($downloadedBytes > 0) {
                            $progressBar->setMessage((string) round($downloadedBytes / 1024 / 1024, 1), 'current_mb');
                            $progressBar->setProgress($downloadedBytes);
                        }
                    }
                    : null,
            ])
            ->get($url);

        if ($showProgress) {
            $progressBar->finish();
            $this->newLine();
        }

        if (! $response->successful() && ! file_exists($destination)) {
            $this->error('Failed to download bulk data.');

            return false;
        }

        return true;
    }

    private function importRulings(string $filePath): int
    {
        $stream = gzopen($filePath, 'rb');

        if (! $stream) {
            $this->error('Could not open gzipped file.');

            return 0;
        }

        $items = Items::fromStream($stream);

        $batch = [];
        $count = 0;
        $showProgress = ! $this->option('no-progress');

        if ($showProgress) {
            $progressBar = $this->output->createProgressBar();
            $progressBar->setFormat(' %current% rulings [%bar%] %elapsed:6s% %memory:6s%');
            $progressBar->start();
        }

        foreach ($items as $ruling) {
            $ruling = (array) $ruling;
            $batch[] = $this->extractRulingData($ruling);
            $count++;

            if (count($batch) >= self::BATCH_SIZE) {
                $this->upsertBatch($batch);
                $batch = [];

                if ($showProgress) {
                    $progressBar->setProgress($count);
                }
            }
        }

        if (count($batch) > 0) {
            $this->upsertBatch($batch);
        }

        if ($showProgress) {
            $progressBar->setProgress($count);
            $progressBar->finish();
            $this->newLine();
        }

        gzclose($stream);

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $batch
     */
    private function upsertBatch(array $batch): void
    {
        Ruling::upsert($batch, ['content_hash'], [
            'oracle_id', 'source', 'published_at', 'comment', 'updated_at',
        ]);
    }

    /**
     * @param  array<string, mixed>  $ruling
     * @return array<string, mixed>
     */
    private function extractRulingData(array $ruling): array
    {
        $oracleId = $ruling['oracle_id'];
        $publishedAt = $ruling['published_at'];
        $comment = $ruling['comment'];

        $now = now()->toDateTimeString();

        return [
            'oracle_id' => $oracleId,
            'source' => $ruling['source'],
            'published_at' => $publishedAt,
            'comment' => $comment,
            'content_hash' => hash('sha256', $oracleId.'|'.$publishedAt.'|'.$comment),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
