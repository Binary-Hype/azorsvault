<?php

namespace App\Services\Decklist;

/**
 * Parses plain-text decklists in the formats exported by the common deck
 * builders (Moxfield, Archidekt, MTG Arena) into card names and quantities.
 */
class DecklistParser
{
    private const MAX_ENTRIES = 500;

    /**
     * Sections whose contents are not part of the deck proper.
     */
    private const IGNORED_SECTIONS = ['sideboard', 'maybeboard', 'considering', 'tokens', 'token'];

    /**
     * Sections that hold the deck's commander(s).
     */
    private const COMMANDER_SECTIONS = ['commander', 'commanders'];

    /**
     * @return array{
     *     entries: array<int, array{name: string, quantity: int}>,
     *     commanders: array<int, array{name: string, quantity: int}>,
     *     ignored_sections: array<int, string>,
     *     unparsed_lines: array<int, string>,
     *     truncated: bool,
     * }
     */
    public function parse(string $decklist): array
    {
        $entries = [];
        $commanders = [];
        $ignoredSections = [];
        $unparsedLines = [];
        $truncated = false;

        $section = 'deck';

        foreach (preg_split('/\R/', $decklist) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '//') || str_starts_with($line, '#')) {
                continue;
            }

            $header = $this->sectionFor($line);

            if ($header !== null) {
                $section = $header;

                if (in_array($section, self::IGNORED_SECTIONS, true) && ! in_array($section, $ignoredSections, true)) {
                    $ignoredSections[] = $section;
                }

                continue;
            }

            if (in_array($section, self::IGNORED_SECTIONS, true)) {
                continue;
            }

            $entry = $this->parseLine($line);

            if ($entry === null) {
                $unparsedLines[] = $line;

                continue;
            }

            if (count($entries) + count($commanders) >= self::MAX_ENTRIES) {
                $truncated = true;

                break;
            }

            if (in_array($section, self::COMMANDER_SECTIONS, true)) {
                $commanders[] = $entry;

                continue;
            }

            $entries[] = $entry;
        }

        return [
            'entries' => $this->merge($entries),
            'commanders' => $this->merge($commanders),
            'ignored_sections' => $ignoredSections,
            'unparsed_lines' => $unparsedLines,
            'truncated' => $truncated,
        ];
    }

    /**
     * Recognise a section header such as "Commander", "Deck (99)" or "SIDEBOARD:".
     */
    private function sectionFor(string $line): ?string
    {
        if (preg_match('/^(?<name>[A-Za-z ]+?)\s*(?:\(\d+\))?\s*:?$/', $line, $matches) !== 1) {
            return null;
        }

        $name = mb_strtolower(trim($matches['name']));

        $known = array_merge(
            self::COMMANDER_SECTIONS,
            self::IGNORED_SECTIONS,
            ['deck', 'mainboard', 'main', 'companion', 'planeswalker', 'signature spell'],
        );

        return in_array($name, $known, true) ? $name : null;
    }

    /**
     * Pull the quantity and card name out of a single decklist line.
     *
     * @return array{name: string, quantity: int}|null
     */
    private function parseLine(string $line): ?array
    {
        if (preg_match('/^(?<quantity>\d+)\s*[xX]?\s+(?<name>.+)$/', $line, $matches) === 1) {
            $quantity = (int) $matches['quantity'];
            $name = $matches['name'];
        } else {
            $quantity = 1;
            $name = $line;
        }

        $name = $this->cleanName($name);

        if ($name === '' || $quantity < 1) {
            return null;
        }

        return ['name' => $name, 'quantity' => $quantity];
    }

    /**
     * Strip the set codes, collector numbers, categories and foil markers that
     * deck builders append to the card name.
     */
    private function cleanName(string $name): string
    {
        $patterns = [
            '/\s*\[[^\]]*\]/',          // Archidekt categories: [Artifact{top}]
            '/\s*\*[^*]*\*/',           // Moxfield foil marker: *F*
            '/\s*\((?:[A-Za-z0-9]{2,6})\)\s*[A-Za-z0-9\-\x{2605}]*$/u', // (M21) 234
            '/\s*\^[^^]*\^/',           // Moxfield tags: ^Buy,#ff0000^
            '/\s*#.*$/',                // trailing comments
        ];

        foreach ($patterns as $pattern) {
            $name = preg_replace($pattern, '', $name) ?? $name;
        }

        return trim($name);
    }

    /**
     * Collapse repeated lines for the same card into a single quantity.
     *
     * @param  array<int, array{name: string, quantity: int}>  $entries
     * @return array<int, array{name: string, quantity: int}>
     */
    private function merge(array $entries): array
    {
        $merged = [];

        foreach ($entries as $entry) {
            $key = mb_strtolower($entry['name']);

            if (isset($merged[$key])) {
                $merged[$key]['quantity'] += $entry['quantity'];

                continue;
            }

            $merged[$key] = $entry;
        }

        return array_values($merged);
    }
}
