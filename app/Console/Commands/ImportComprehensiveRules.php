<?php

namespace App\Console\Commands;

use App\Models\ComprehensiveRule;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

#[Signature('rules:import-comprehensive {--force : Force import even if already imported today} {--no-progress : Hide progress bars}')]
#[Description('Download and import the official Magic: The Gathering Comprehensive Rules')]
class ImportComprehensiveRules extends Command
{
    private const BATCH_SIZE = 500;

    private const CACHE_KEY = 'rules:last_comprehensive_import';

    private const RULES_PAGE_URL = 'https://magic.wizards.com/en/rules';

    public function handle(): int
    {
        if (! $this->option('force') && Cache::get(self::CACHE_KEY) === now()->toDateString()) {
            $this->info('Already imported today. Use --force to re-import.');

            return self::SUCCESS;
        }

        $this->info('Discovering rules download URL...');
        $downloadUrl = $this->discoverDownloadUrl();

        if (! $downloadUrl) {
            $this->error('Could not find rules download URL.');

            return self::FAILURE;
        }

        $storagePath = 'rules/comprehensive_rules.txt';
        $fullPath = storage_path('app/private/'.$storagePath);

        if (! $this->downloadFile($downloadUrl, $fullPath)) {
            return self::FAILURE;
        }

        $this->info('Parsing and importing rules...');
        $count = $this->importRules($fullPath);

        Storage::disk('local')->delete($storagePath);

        Cache::put(self::CACHE_KEY, now()->toDateString(), now()->addDay());

        $this->info("Successfully imported {$count} rules.");

        return self::SUCCESS;
    }

    private function discoverDownloadUrl(): ?string
    {
        $response = Http::withUserAgent('MtgMCP/1.0')->get(self::RULES_PAGE_URL);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();

        // Normalize unicode escapes (\u002F → /) that may appear in JSON-embedded HTML
        $normalized = preg_replace('/\\\\u002F/i', '/', $body);

        if (preg_match('/https:\/\/media\.wizards\.com\/\d{4}\/downloads\/MagicCompRules[^"\'\\\\>]+\.txt/', $normalized, $matches)) {
            $url = trim($matches[0]);

            if (! str_starts_with($url, 'https://media.wizards.com/')) {
                return null;
            }

            return str_replace(' ', '%20', $url);
        }

        return null;
    }

    private function downloadFile(string $url, string $destination): bool
    {
        $directory = dirname($destination);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $response = Http::withUserAgent('MtgMCP/1.0')
            ->withOptions([
                'sink' => $destination,
                'timeout' => 300,
            ])
            ->get($url);

        if (! $response->successful() && ! file_exists($destination)) {
            $this->error('Failed to download rules file.');

            return false;
        }

        return true;
    }

    private function importRules(string $filePath): int
    {
        $content = file_get_contents($filePath);

        if (! $content) {
            $this->error('Could not read rules file.');

            return 0;
        }

        $effectiveDate = $this->extractEffectiveDate($content);
        $parsed = $this->parseRulesFile($content, $effectiveDate);

        $showProgress = ! $this->option('no-progress');
        $count = 0;
        $batch = [];

        if ($showProgress) {
            $progressBar = $this->output->createProgressBar(count($parsed));
            $progressBar->setFormat(' %current%/%max% rules [%bar%] %percent:3s%%');
            $progressBar->start();
        }

        foreach ($parsed as $rule) {
            $batch[] = $rule;
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
            $progressBar->finish();
            $this->newLine();
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $batch
     */
    private function upsertBatch(array $batch): void
    {
        ComprehensiveRule::upsert($batch, ['rule_number'], [
            'section', 'chapter', 'content', 'is_glossary', 'effective_date', 'updated_at',
        ]);
    }

    private function extractEffectiveDate(string $content): string
    {
        if (preg_match('/These rules are effective as of (.+)\./', $content, $matches)) {
            return date('Y-m-d', strtotime(trim($matches[1]))) ?: '2026-01-01';
        }

        return '2026-01-01';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseRulesFile(string $content, string $effectiveDate): array
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $rules = [];
        $now = now()->toDateTimeString();

        $inGlossary = false;
        $inCredits = false;
        $pastToc = false;
        $currentRuleNumber = null;
        $currentContent = '';
        $glossaryTerm = null;
        $glossaryContent = '';

        foreach ($lines as $line) {
            $line = rtrim($line);

            if ($inCredits) {
                continue;
            }

            // Detect when we've moved past the table of contents by seeing a sub-rule
            if (! $pastToc && preg_match('/^\d{3}\.\d+/', $line)) {
                $pastToc = true;
            }

            // Only trigger section markers after passing the table of contents,
            // which also lists "Glossary" and "Credits" as TOC entries
            if ($pastToc && preg_match('/^Credits$/', $line)) {
                // Flush any pending glossary entry
                if ($inGlossary && $glossaryTerm !== null && $glossaryContent !== '') {
                    $rules[] = $this->buildGlossaryEntry($glossaryTerm, $glossaryContent, $effectiveDate, $now);
                }
                // Flush any pending rule
                if (! $inGlossary && $currentRuleNumber !== null) {
                    $rules[] = $this->buildRuleEntry($currentRuleNumber, $currentContent, $effectiveDate, $now);
                }
                $inCredits = true;

                continue;
            }

            if ($pastToc && preg_match('/^Glossary$/', $line)) {
                // Flush any pending rule before entering glossary
                if ($currentRuleNumber !== null) {
                    $rules[] = $this->buildRuleEntry($currentRuleNumber, $currentContent, $effectiveDate, $now);
                    $currentRuleNumber = null;
                    $currentContent = '';
                }
                $inGlossary = true;

                continue;
            }

            if ($inGlossary) {
                $this->parseGlossaryLine($line, $glossaryTerm, $glossaryContent, $rules, $effectiveDate, $now);

                continue;
            }

            $this->parseRuleLine($line, $currentRuleNumber, $currentContent, $rules, $effectiveDate, $now);
        }

        // Flush any remaining rule
        if ($currentRuleNumber !== null && ! $inGlossary) {
            $rules[] = $this->buildRuleEntry($currentRuleNumber, $currentContent, $effectiveDate, $now);
        }

        return $rules;
    }

    /**
     * @param  array<int, array<string, mixed>>  &$rules
     */
    private function parseRuleLine(
        string $line,
        ?string &$currentRuleNumber,
        string &$currentContent,
        array &$rules,
        string $effectiveDate,
        string $now,
    ): void {
        // Match chapter headers: "100. General"
        if (preg_match('/^(\d{3})\.\s+(.+)$/', $line, $matches)) {
            if ($currentRuleNumber !== null) {
                $rules[] = $this->buildRuleEntry($currentRuleNumber, $currentContent, $effectiveDate, $now);
            }
            $currentRuleNumber = $matches[1];
            $currentContent = $matches[2];

            return;
        }

        // Match individual rules: "100.1." or "100.1a" or "100.1a."
        if (preg_match('/^(\d{3}\.\d+[a-z]?)\.?\s+(.+)$/', $line, $matches)) {
            if ($currentRuleNumber !== null) {
                $rules[] = $this->buildRuleEntry($currentRuleNumber, $currentContent, $effectiveDate, $now);
            }
            $currentRuleNumber = $matches[1];
            $currentContent = $matches[2];

            return;
        }

        // Continuation of a multi-line rule
        if ($currentRuleNumber !== null && $line !== '') {
            $currentContent .= ' '.$line;

            return;
        }

        // Empty line - flush the current rule if one exists
        if ($line === '' && $currentRuleNumber !== null) {
            $rules[] = $this->buildRuleEntry($currentRuleNumber, $currentContent, $effectiveDate, $now);
            $currentRuleNumber = null;
            $currentContent = '';
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  &$rules
     */
    private function parseGlossaryLine(
        string $line,
        ?string &$glossaryTerm,
        string &$glossaryContent,
        array &$rules,
        string $effectiveDate,
        string $now,
    ): void {
        // Empty line separates glossary entries
        if ($line === '') {
            if ($glossaryTerm !== null && $glossaryContent !== '') {
                $rules[] = $this->buildGlossaryEntry($glossaryTerm, $glossaryContent, $effectiveDate, $now);
            }
            $glossaryTerm = null;
            $glossaryContent = '';

            return;
        }

        // If we don't have a term yet, this line is the term
        if ($glossaryTerm === null) {
            $glossaryTerm = $line;

            return;
        }

        // Otherwise it's a continuation of the definition
        if ($glossaryContent === '') {
            $glossaryContent = $line;
        } else {
            $glossaryContent .= ' '.$line;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRuleEntry(string $ruleNumber, string $content, string $effectiveDate, string $now): array
    {
        $chapter = preg_match('/^(\d{3})/', $ruleNumber, $m) ? $m[1] : $ruleNumber;
        $section = (int) $chapter[0];

        return [
            'rule_number' => $ruleNumber,
            'section' => $section,
            'chapter' => $chapter,
            'content' => trim($content),
            'is_glossary' => false,
            'effective_date' => $effectiveDate,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGlossaryEntry(string $term, string $content, string $effectiveDate, string $now): array
    {
        return [
            'rule_number' => 'glossary:'.mb_strtolower($term),
            'section' => null,
            'chapter' => null,
            'content' => $term."\n".trim($content),
            'is_glossary' => true,
            'effective_date' => $effectiveDate,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
