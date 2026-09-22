<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\ValidateDeck;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Card::factory()->create([
        'name' => 'Azusa, Lost but Seeking',
        'type_line' => 'Legendary Creature — Human Monk',
        'colors' => ['G'],
        'color_identity' => ['G'],
        'legalities' => ['commander' => 'legal'],
    ]);

    Card::factory()->create([
        'name' => 'Forest',
        'type_line' => 'Basic Land — Forest',
        'colors' => [],
        'color_identity' => ['G'],
        'legalities' => ['commander' => 'legal'],
    ]);
});

/**
 * A valid 100-card list: the commander plus 99 basics.
 *
 * @param  array<int, string>  $deckLines  Replaces basics one-for-one.
 */
function decklist(array $deckLines = [], string $commander = '1 Azusa, Lost but Seeking'): string
{
    $basics = 99 - count($deckLines);

    return implode("\n", array_filter([
        'Commander',
        $commander,
        '',
        'Deck',
        $basics > 0 ? $basics.' Forest' : null,
        ...$deckLines,
    ]));
}

function greenCard(string $name, array $attributes = []): Card
{
    return Card::factory()->create(array_merge([
        'name' => $name,
        'type_line' => 'Creature — Elf Druid',
        'colors' => ['G'],
        'color_identity' => ['G'],
        'legalities' => ['commander' => 'legal'],
        'game_changer' => false,
    ], $attributes));
}

test('it accepts a legal commander deck', function () {
    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => decklist()]);

    $response->assertOk()
        ->assertSee('"valid": true')
        ->assertSee('"card_count": 100')
        ->assertSee('Azusa, Lost but Seeking');
});

test('it rejects a deck that is not exactly 100 cards', function () {
    $decklist = "Commander\n1 Azusa, Lost but Seeking\n\nDeck\n98 Forest";

    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => $decklist]);

    $response->assertOk()
        ->assertSee('"valid": false')
        ->assertSee('Deck has 99 cards');
});

test('it rejects duplicate nonbasic cards', function () {
    greenCard('Llanowar Elves');

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => "Commander\n1 Azusa, Lost but Seeking\n\nDeck\n97 Forest\n2 Llanowar Elves",
    ]);

    $response->assertOk()
        ->assertSee('"valid": false')
        ->assertSee('"card_count": 100')
        ->assertSee('Llanowar Elves appears 2 times');
});

test('it allows any number of basic lands', function () {
    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => decklist()]);

    $response->assertOk()
        ->assertSee('"valid": true');
});

test('it allows duplicates of cards that set their own deck limit', function () {
    greenCard('Relentless Rats', [
        'oracle_text' => 'A deck can have any number of cards named Relentless Rats.',
    ]);

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => "Commander\n1 Azusa, Lost but Seeking\n\nDeck\n95 Forest\n4 Relentless Rats",
    ]);

    $response->assertOk()
        ->assertSee('"valid": true');
});

test('it rejects a card banned in commander', function () {
    greenCard('Primeval Titan', ['legalities' => ['commander' => 'banned']]);

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist(['1 Primeval Titan']),
    ]);

    $response->assertOk()
        ->assertSee('"valid": false')
        ->assertSee('Primeval Titan is banned in Commander');
});

test('it rejects a card outside the commander color identity', function () {
    greenCard('Counterspell', [
        'type_line' => 'Instant',
        'colors' => ['U'],
        'color_identity' => ['U'],
    ]);

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist(['1 Counterspell']),
    ]);

    $response->assertOk()
        ->assertSee('"valid": false')
        ->assertSee('Counterspell has color identity {U}');
});

test('it rejects a commander that is not a legendary creature', function () {
    greenCard('Llanowar Elves');

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist([], '1 Llanowar Elves'),
    ]);

    $response->assertOk()
        ->assertSee('"valid": false')
        ->assertSee('Llanowar Elves cannot be a commander');
});

test('it accepts a commander whose text says it can be your commander', function () {
    greenCard('Sefris of the Hidden Ways', [
        'type_line' => 'Legendary Creature — Human Cleric',
        'oracle_text' => 'Sefris of the Hidden Ways can be your commander.',
    ]);

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist([], '1 Sefris of the Hidden Ways'),
    ]);

    $response->assertOk()
        ->assertSee('"valid": true');
});

test('it counts game changers and reports the lowest bracket that allows them', function () {
    greenCard('Survival of the Fittest', [
        'type_line' => 'Enchantment',
        'game_changer' => true,
    ]);

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist(['1 Survival of the Fittest']),
    ]);

    $response->assertOk()
        ->assertSee('"count": 1')
        ->assertSee('Survival of the Fittest')
        ->assertSee('"minimum_bracket": 3');
});

test('it reports bracket 2 for a deck with no game changers', function () {
    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => decklist()]);

    $response->assertOk()
        ->assertSee('"minimum_bracket": 2');
});

test('it takes the commander from the commanders argument', function () {
    $decklist = "1 Azusa, Lost but Seeking\n99 Forest";

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => $decklist,
        'commanders' => ['Azusa, Lost but Seeking'],
    ]);

    $response->assertOk()
        ->assertSee('"valid": true')
        ->assertSee('"card_count": 100');
});

test('it errors when no commander is given', function () {
    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => "1 Azusa, Lost but Seeking\n99 Forest",
    ]);

    $response->assertOk()
        ->assertSee('"valid": false')
        ->assertSee('No commander given');
});

test('it reports card names it cannot resolve', function () {
    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist(['1 Not A Real Card']),
    ]);

    $response->assertOk()
        ->assertSee('Not A Real Card')
        ->assertSee('Could not resolve 1 card name(s)');
});

test('it ignores the sideboard and maybeboard', function () {
    greenCard('Llanowar Elves');

    $decklist = decklist()."\n\nSideboard\n1 Llanowar Elves\n\nMaybeboard\n5 Llanowar Elves";

    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => $decklist]);

    $response->assertOk()
        ->assertSee('"valid": true')
        ->assertSee('sideboard')
        ->assertSee('maybeboard');
});

test('it does not double-count a commander listed in the deck body too', function () {
    $decklist = "Commander\n1 Azusa, Lost but Seeking\n\nDeck\n1 Azusa, Lost but Seeking\n99 Forest";

    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => $decklist]);

    $response->assertOk()
        ->assertSee('"valid": true')
        ->assertSee('"card_count": 100');
});

test('it resolves a decklist entry naming only the front face', function () {
    Card::factory()->create([
        'name' => 'Boseiju, Who Endures // Boseiju',
        'type_line' => 'Legendary Land',
        'layout' => 'normal',
        'colors' => [],
        'color_identity' => ['G'],
        'legalities' => ['commander' => 'legal'],
    ]);

    $response = MtgServer::tool(ValidateDeck::class, [
        'decklist' => decklist(['1 Boseiju, Who Endures']),
    ]);

    $response->assertOk()
        ->assertSee('"valid": true')
        ->assertSee('"unknown_cards": []');
});

test('it errors when the decklist holds no card entries', function () {
    $response = MtgServer::tool(ValidateDeck::class, ['decklist' => "Commander\n\nDeck\n"]);

    $response->assertHasErrors();
});
