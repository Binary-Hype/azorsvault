<?php

use App\Services\Decklist\DecklistParser;

test('it reads quantities and card names', function () {
    $parsed = app(DecklistParser::class)->parse("2 Llanowar Elves\n1 Sol Ring\nCounterspell");

    expect($parsed['entries'])->toBe([
        ['name' => 'Llanowar Elves', 'quantity' => 2],
        ['name' => 'Sol Ring', 'quantity' => 1],
        ['name' => 'Counterspell', 'quantity' => 1],
    ]);
});

test('it accepts the "1x" quantity style', function () {
    $parsed = app(DecklistParser::class)->parse('4x Lightning Bolt');

    expect($parsed['entries'])->toBe([['name' => 'Lightning Bolt', 'quantity' => 4]]);
});

/*
 * Deck builders append set codes, collector numbers, categories and foil
 * markers to the card name; none of that is part of the name we look up.
 */
test('it strips set codes, collector numbers, categories and foil markers', function () {
    $parsed = app(DecklistParser::class)->parse(implode("\n", [
        '1 Sol Ring (m21) 234',
        '1x Arcane Signet (eld) 331 [Ramp{top}]',
        '1 Cultivate (m21) *F*',
        '1 Rampant Growth (m12)',
    ]));

    expect(array_column($parsed['entries'], 'name'))
        ->toBe(['Sol Ring', 'Arcane Signet', 'Cultivate', 'Rampant Growth']);
});

test('it separates the commander section from the deck', function () {
    $parsed = app(DecklistParser::class)->parse("Commander\n1 Azusa, Lost but Seeking\n\nDeck\n1 Sol Ring");

    expect($parsed['commanders'])->toBe([['name' => 'Azusa, Lost but Seeking', 'quantity' => 1]])
        ->and($parsed['entries'])->toBe([['name' => 'Sol Ring', 'quantity' => 1]]);
});

test('it reads section headers that carry a count or a colon', function () {
    $parsed = app(DecklistParser::class)->parse("Commander (1)\n1 Azusa, Lost but Seeking\n\nSIDEBOARD:\n1 Sol Ring");

    expect($parsed['commanders'])->toHaveCount(1)
        ->and($parsed['entries'])->toBe([])
        ->and($parsed['ignored_sections'])->toBe(['sideboard']);
});

test('it skips blank lines and comments', function () {
    $parsed = app(DecklistParser::class)->parse("// my deck\n\n1 Sol Ring\n# a note");

    expect($parsed['entries'])->toBe([['name' => 'Sol Ring', 'quantity' => 1]])
        ->and($parsed['unparsed_lines'])->toBe([]);
});

test('it merges repeated lines for the same card', function () {
    $parsed = app(DecklistParser::class)->parse("1 Forest\n1 forest\n2 Forest");

    expect($parsed['entries'])->toBe([['name' => 'Forest', 'quantity' => 4]]);
});

test('it reports truncation past the entry cap', function () {
    $lines = [];

    for ($i = 1; $i <= 520; $i++) {
        $lines[] = "1 Card Number {$i}";
    }

    $parsed = app(DecklistParser::class)->parse(implode("\n", $lines));

    expect($parsed['truncated'])->toBeTrue()
        ->and($parsed['entries'])->toHaveCount(500);
});
