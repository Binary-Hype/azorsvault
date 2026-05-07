<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Services\Scryfall\BulkDataService;
use App\Services\Scryfall\BulkDataType;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Items;

#[Signature('scryfall:import-cards {--force : Force import even if already imported today} {--no-progress : Hide progress bars}')]
#[Description('Download and import Scryfall bulk card data')]
class ImportScryfallCards extends Command
{
    private const BATCH_SIZE = 500;

    private const CACHE_KEY = 'scryfall:last_import';

    private const UPSERT_COLUMNS = [
        'oracle_id', 'name', 'mana_cost', 'cmc', 'type_line', 'oracle_text',
        'colors', 'color_identity', 'keywords', 'power', 'toughness', 'loyalty',
        'layout', 'set', 'set_name', 'collector_number', 'rarity', 'released_at',
        'reprint', 'digital', 'reserved', 'image_uris', 'legalities', 'prices',
        'edhrec_rank', 'flavor_text', 'games', 'finishes', 'card_faces', 'all_parts',
        'updated_at',
    ];

    public function handle(BulkDataService $bulkData): int
    {
        if (! $this->option('force') && Cache::get(self::CACHE_KEY) === now()->toDateString()) {
            $this->info('Already imported today. Use --force to re-import.');

            return self::SUCCESS;
        }

        $this->info('Fetching Scryfall bulk data metadata...');
        $info = $bulkData->getBulkDataInfo(BulkDataType::DefaultCards);

        if (! $info) {
            $this->error('Could not find default_cards bulk data from Scryfall.');

            return self::FAILURE;
        }

        $storagePath = 'scryfall/default_cards.json.gz';
        $fullPath = storage_path('app/private/'.$storagePath);

        $output = $this->option('no-progress') ? null : $this->output;

        if (! $bulkData->downloadFile($info['download_uri'], $fullPath, $info['size'], $output)) {
            $this->error('Failed to download bulk data.');

            return self::FAILURE;
        }

        $this->info('Importing cards into database...');
        $importStartedAt = now();
        $count = $this->importCards($bulkData, $fullPath);

        $deleted = Card::where('updated_at', '<', $importStartedAt)->delete();

        Storage::disk('local')->delete($storagePath);

        Cache::put(self::CACHE_KEY, now()->toDateString(), now()->addDay());

        $this->info("Successfully imported {$count} cards. Removed {$deleted} stale cards.");

        return self::SUCCESS;
    }

    private function importCards(BulkDataService $bulkData, string $filePath): int
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
            $progressBar->setFormat(' %current% cards [%bar%] %elapsed:6s% %memory:6s%');
            $progressBar->start();
        }

        foreach ($items as $card) {
            $card = (array) $card;
            $batch[] = $this->extractCardData($card);
            $count++;

            if (count($batch) >= self::BATCH_SIZE) {
                $bulkData->upsertBatch(Card::class, $batch, ['id'], self::UPSERT_COLUMNS);
                $batch = [];

                if ($progressBar !== null) {
                    $progressBar->setProgress($count);
                }
            }
        }

        if (count($batch) > 0) {
            $bulkData->upsertBatch(Card::class, $batch, ['id'], self::UPSERT_COLUMNS);
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
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    private function extractCardData(array $card): array
    {
        $manaCost = $card['mana_cost'] ?? null;
        $oracleText = $card['oracle_text'] ?? null;
        $colors = $card['colors'] ?? null;
        $power = $card['power'] ?? null;
        $toughness = $card['toughness'] ?? null;

        $cardFaces = $card['card_faces'] ?? null;

        if ($manaCost === null && isset($cardFaces[0])) {
            $front = (array) $cardFaces[0];
            $manaCost = $front['mana_cost'] ?? null;
            $oracleText = $front['oracle_text'] ?? null;
            $colors = $front['colors'] ?? null;
            $power = $front['power'] ?? null;
            $toughness = $front['toughness'] ?? null;
        }

        $now = now()->toDateTimeString();

        return [
            'id' => $card['id'],
            'oracle_id' => $card['oracle_id'] ?? null,
            'name' => $card['name'],
            'mana_cost' => $manaCost,
            'cmc' => $card['cmc'] ?? null,
            'type_line' => $card['type_line'] ?? null,
            'oracle_text' => $oracleText,
            'colors' => json_encode($colors),
            'color_identity' => json_encode($card['color_identity'] ?? null),
            'keywords' => json_encode($card['keywords'] ?? []),
            'power' => $power,
            'toughness' => $toughness,
            'loyalty' => $card['loyalty'] ?? null,
            'layout' => $card['layout'],
            'set' => $card['set'],
            'set_name' => $card['set_name'],
            'collector_number' => $card['collector_number'],
            'rarity' => $card['rarity'],
            'released_at' => $card['released_at'] ?? null,
            'reprint' => $card['reprint'] ?? false,
            'digital' => $card['digital'] ?? false,
            'reserved' => $card['reserved'] ?? false,
            'image_uris' => json_encode($card['image_uris'] ?? null),
            'legalities' => json_encode($card['legalities'] ?? null),
            'prices' => json_encode($card['prices'] ?? null),
            'edhrec_rank' => $card['edhrec_rank'] ?? null,
            'flavor_text' => $card['flavor_text'] ?? null,
            'games' => json_encode($card['games'] ?? []),
            'finishes' => json_encode($card['finishes'] ?? []),
            'card_faces' => json_encode($cardFaces),
            'all_parts' => json_encode($card['all_parts'] ?? null),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
