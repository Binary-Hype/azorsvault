<?php

namespace App\Console\Commands;

use App\Models\Ruling;
use App\Services\Scryfall\BulkDataService;
use App\Services\Scryfall\BulkDataType;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Items;

#[Signature('scryfall:import-rulings {--force : Force import even if already imported today} {--no-progress : Hide progress bars}')]
#[Description('Download and import Scryfall bulk rulings data')]
class ImportScryfallRulings extends Command
{
    private const BATCH_SIZE = 500;

    private const CACHE_KEY = 'scryfall:last_rulings_import';

    private const UPSERT_COLUMNS = [
        'oracle_id', 'source', 'published_at', 'comment', 'updated_at',
    ];

    public function handle(BulkDataService $bulkData): int
    {
        if (! $this->option('force') && Cache::get(self::CACHE_KEY) === now()->toDateString()) {
            $this->info('Already imported today. Use --force to re-import.');

            return self::SUCCESS;
        }

        $this->info('Fetching Scryfall bulk data metadata...');
        $info = $bulkData->getBulkDataInfo(BulkDataType::Rulings);

        if (! $info) {
            $this->error('Could not find rulings bulk data from Scryfall.');

            return self::FAILURE;
        }

        $storagePath = 'scryfall/rulings.json.gz';
        $fullPath = storage_path('app/private/'.$storagePath);

        $output = $this->option('no-progress') ? null : $this->output;

        if (! $bulkData->downloadFile($info['download_uri'], $fullPath, $info['size'], $output)) {
            $this->error('Failed to download bulk data.');

            return self::FAILURE;
        }

        $this->info('Importing rulings into database...');
        $importStartedAt = now();
        $count = $this->importRulings($bulkData, $fullPath);

        $deleted = Ruling::where('updated_at', '<', $importStartedAt)->delete();

        Storage::disk('local')->delete($storagePath);

        Cache::put(self::CACHE_KEY, now()->toDateString(), now()->addDay());

        $this->info("Successfully imported {$count} rulings. Removed {$deleted} stale rulings.");

        return self::SUCCESS;
    }

    private function importRulings(BulkDataService $bulkData, string $filePath): int
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
        $progressBar = null;

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
                $bulkData->upsertBatch(Ruling::class, $batch, ['content_hash'], self::UPSERT_COLUMNS);
                $batch = [];

                if ($progressBar !== null) {
                    $progressBar->setProgress($count);
                }
            }
        }

        if (count($batch) > 0) {
            $bulkData->upsertBatch(Ruling::class, $batch, ['content_hash'], self::UPSERT_COLUMNS);
        }

        if ($progressBar !== null) {
            $progressBar->setProgress($count);
            $progressBar->finish();
            $this->newLine();
        }

        gzclose($stream);

        return $count;
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
