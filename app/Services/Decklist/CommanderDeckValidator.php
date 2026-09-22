<?php

namespace App\Services\Decklist;

use App\Models\Card;
use Illuminate\Support\Collection;

/**
 * Checks a parsed decklist against the Commander deck construction rules:
 * deck size, the singleton rule, format legality and colour identity.
 */
class CommanderDeckValidator
{
    private const DECK_SIZE = 100;

    /**
     * Game changer allowances per Commander bracket, as published by Wizards.
     * Bracket 1 is omitted: it carries restrictions this validator cannot see,
     * so bracket 2 is the lowest bracket it will claim.
     */
    private const BRACKET_GAME_CHANGERS = [
        2 => 0,
        3 => 3,
    ];

    /**
     * @param  array{
     *     entries: array<int, array{name: string, quantity: int}>,
     *     commanders: array<int, array{name: string, quantity: int}>,
     *     ignored_sections: array<int, string>,
     *     unparsed_lines: array<int, string>,
     *     truncated: bool,
     * }  $decklist
     * @param  array<int, string>  $commanderNames  Overrides the commanders found in the decklist.
     * @return array<string, mixed>
     */
    public function validate(array $decklist, array $commanderNames = []): array
    {
        $commanderEntries = $commanderNames === []
            ? $decklist['commanders']
            : array_map(fn (string $name) => ['name' => $name, 'quantity' => 1], $commanderNames);

        /*
         * An explicit commander may also appear in the deck body (Moxfield lists
         * it in both places), so the deck entries are keyed to allow removal.
         */
        $deckEntries = $this->withoutCommanders($decklist['entries'], $commanderEntries);

        $allEntries = array_merge($commanderEntries, $deckEntries);
        $cards = $this->resolveCards(array_column($allEntries, 'name'));

        $errors = [];
        $warnings = [];
        $unknownCards = [];

        foreach ($allEntries as $entry) {
            if (! $cards->has(mb_strtolower($entry['name']))) {
                $unknownCards[] = $entry['name'];
            }
        }

        if ($unknownCards !== []) {
            $warnings[] = 'Could not resolve '.count($unknownCards).' card name(s); they were skipped by every other check.';
        }

        if ($decklist['unparsed_lines'] !== []) {
            $warnings[] = 'Ignored '.count($decklist['unparsed_lines']).' line(s) that did not look like decklist entries.';
        }

        if ($decklist['truncated']) {
            $warnings[] = 'Decklist was truncated; only the first 500 entries were checked.';
        }

        $cardCount = array_sum(array_column($allEntries, 'quantity'));

        if ($cardCount !== self::DECK_SIZE) {
            $errors[] = "Deck has {$cardCount} cards; Commander requires exactly ".self::DECK_SIZE.' (including the commander).';
        }

        if ($commanderEntries === []) {
            $errors[] = 'No commander given. Pass the commander in a "Commander" section of the decklist or via the commanders argument.';
        }

        $errors = array_merge(
            $errors,
            $this->commanderErrors($commanderEntries, $cards),
            $this->singletonErrors($deckEntries, $cards),
            $this->legalityErrors($allEntries, $cards),
        );

        $colorIdentity = $this->commanderColorIdentity($commanderEntries, $cards);

        if ($commanderEntries !== []) {
            $errors = array_merge($errors, $this->colorIdentityErrors($deckEntries, $cards, $colorIdentity));
        }

        $gameChangers = $this->gameChangers($allEntries, $cards);

        return [
            'format' => 'commander',
            'valid' => $errors === [],
            'commanders' => array_column($commanderEntries, 'name'),
            'color_identity' => $colorIdentity,
            'card_count' => $cardCount,
            'unique_cards' => count($allEntries),
            'errors' => $errors,
            'warnings' => $warnings,
            'unknown_cards' => $unknownCards,
            'ignored_sections' => $decklist['ignored_sections'],
            'game_changers' => [
                'count' => count($gameChangers),
                'cards' => $gameChangers,
                'minimum_bracket' => $this->minimumBracket(count($gameChangers)),
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $entries
     * @param  array<int, array{name: string, quantity: int}>  $commanderEntries
     * @return array<int, array{name: string, quantity: int}>
     */
    private function withoutCommanders(array $entries, array $commanderEntries): array
    {
        $commanderKeys = array_map(fn (array $entry) => mb_strtolower($entry['name']), $commanderEntries);

        $remaining = [];

        foreach ($entries as $entry) {
            if (! in_array(mb_strtolower($entry['name']), $commanderKeys, true)) {
                $remaining[] = $entry;

                continue;
            }

            if ($entry['quantity'] > 1) {
                $remaining[] = ['name' => $entry['name'], 'quantity' => $entry['quantity'] - 1];
            }
        }

        return $remaining;
    }

    /**
     * Resolve card names to the most recent printing, accepting either face of
     * a multi-faced card as well as its full "Front // Back" name.
     *
     * @param  array<int, string>  $names
     * @return Collection<string, Card>
     */
    private function resolveCards(array $names): Collection
    {
        $names = array_values(array_unique(array_map('mb_strtolower', $names)));

        if ($names === []) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, count($names), '?'));

        $query = Card::query()
            ->whereRaw("LOWER(name) IN ({$placeholders})", $names);

        foreach ($names as $name) {
            $query->orWhereRaw('LOWER(name) LIKE ?', [$name.' // %']);
        }

        $cards = $query->orderByDesc('released_at')->get();

        $resolved = collect();

        foreach ($names as $name) {
            $match = $cards->first(fn (Card $card) => mb_strtolower($card->name) === $name)
                ?? $cards->first(fn (Card $card) => str_starts_with(mb_strtolower($card->name), $name.' // '));

            if ($match !== null) {
                $resolved->put($name, $match);
            }
        }

        return $resolved;
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $commanderEntries
     * @param  Collection<string, Card>  $cards
     * @return array<int, string>
     */
    private function commanderErrors(array $commanderEntries, Collection $cards): array
    {
        $errors = [];

        if (count($commanderEntries) > 2) {
            $errors[] = 'A deck may have at most two commanders (via Partner or a Background).';
        }

        foreach ($commanderEntries as $entry) {
            $card = $cards->get(mb_strtolower($entry['name']));

            if ($card === null) {
                continue;
            }

            if (! $this->canBeCommander($card)) {
                $errors[] = "{$card->name} cannot be a commander; it is not a legendary creature and does not say it can be your commander.";
            }
        }

        return $errors;
    }

    private function canBeCommander(Card $card): bool
    {
        $typeLine = (string) $card->type_line;

        if (str_contains($typeLine, 'Legendary') && str_contains($typeLine, 'Creature')) {
            return true;
        }

        return str_contains(mb_strtolower((string) $card->oracle_text), 'can be your commander');
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $deckEntries
     * @param  Collection<string, Card>  $cards
     * @return array<int, string>
     */
    private function singletonErrors(array $deckEntries, Collection $cards): array
    {
        $errors = [];

        foreach ($deckEntries as $entry) {
            if ($entry['quantity'] <= 1) {
                continue;
            }

            $card = $cards->get(mb_strtolower($entry['name']));

            if ($card === null || $this->isSingletonExempt($card)) {
                continue;
            }

            $errors[] = "{$card->name} appears {$entry['quantity']} times; Commander allows only one copy of each nonbasic card.";
        }

        return $errors;
    }

    /**
     * Basic lands, and cards that grant their own deck limit, are exempt from
     * the singleton rule.
     */
    private function isSingletonExempt(Card $card): bool
    {
        if (str_contains((string) $card->type_line, 'Basic')) {
            return true;
        }

        return str_contains((string) $card->oracle_text, 'A deck can have');
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $entries
     * @param  Collection<string, Card>  $cards
     * @return array<int, string>
     */
    private function legalityErrors(array $entries, Collection $cards): array
    {
        $errors = [];

        foreach ($entries as $entry) {
            $card = $cards->get(mb_strtolower($entry['name']));

            if ($card === null) {
                continue;
            }

            $legality = $card->legalities['commander'] ?? 'not_legal';

            if ($legality === 'legal') {
                continue;
            }

            $errors[] = $legality === 'banned'
                ? "{$card->name} is banned in Commander."
                : "{$card->name} is not legal in Commander.";
        }

        return $errors;
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $commanderEntries
     * @param  Collection<string, Card>  $cards
     * @return array<int, string>
     */
    private function commanderColorIdentity(array $commanderEntries, Collection $cards): array
    {
        $identity = [];

        foreach ($commanderEntries as $entry) {
            $card = $cards->get(mb_strtolower($entry['name']));

            $identity = array_merge($identity, $card?->color_identity ?? []);
        }

        return array_values(array_intersect(['W', 'U', 'B', 'R', 'G'], array_unique($identity)));
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $deckEntries
     * @param  Collection<string, Card>  $cards
     * @param  array<int, string>  $colorIdentity
     * @return array<int, string>
     */
    private function colorIdentityErrors(array $deckEntries, Collection $cards, array $colorIdentity): array
    {
        $errors = [];

        foreach ($deckEntries as $entry) {
            $card = $cards->get(mb_strtolower($entry['name']));

            if ($card === null) {
                continue;
            }

            $outside = array_diff($card->color_identity ?? [], $colorIdentity);

            if ($outside === []) {
                continue;
            }

            $errors[] = "{$card->name} has color identity {".implode('', $card->color_identity ?? []).'} which is outside the commander\'s {'.implode('', $colorIdentity).'}.';
        }

        return $errors;
    }

    /**
     * @param  array<int, array{name: string, quantity: int}>  $entries
     * @param  Collection<string, Card>  $cards
     * @return array<int, string>
     */
    private function gameChangers(array $entries, Collection $cards): array
    {
        $gameChangers = [];

        foreach ($entries as $entry) {
            $card = $cards->get(mb_strtolower($entry['name']));

            if ($card?->game_changer === true) {
                $gameChangers[] = $card->name;
            }
        }

        sort($gameChangers);

        return $gameChangers;
    }

    /**
     * The lowest Commander bracket whose game changer allowance this deck fits.
     */
    private function minimumBracket(int $gameChangerCount): int
    {
        foreach (self::BRACKET_GAME_CHANGERS as $bracket => $allowance) {
            if ($gameChangerCount <= $allowance) {
                return $bracket;
            }
        }

        return 4;
    }
}
