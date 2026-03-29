<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\SearchCardsAdvanced;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it searches by name', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);
    Card::factory()->create(['name' => 'Lightning Strike']);
    Card::factory()->create(['name' => 'Dark Ritual']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['name' => 'Lightning']);

    $response->assertOk()
        ->assertSee('Lightning Bolt')
        ->assertSee('Lightning Strike');
});

test('it searches by mana cost', function () {
    Card::factory()->create(['name' => 'Lightning Bolt', 'mana_cost' => '{R}']);
    Card::factory()->create(['name' => 'Shock', 'mana_cost' => '{R}']);
    Card::factory()->create(['name' => 'Fireball', 'mana_cost' => '{X}{R}']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['mana_cost' => '{R}']);

    $response->assertOk()
        ->assertSee('Lightning Bolt')
        ->assertSee('Shock');
});

test('it searches by oracle text', function () {
    Card::factory()->create(['name' => 'Lightning Bolt', 'oracle_text' => 'Lightning Bolt deals 3 damage to any target.']);
    Card::factory()->create(['name' => 'Divination', 'oracle_text' => 'Draw two cards.']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['oracle_text' => 'damage']);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it searches by type line', function () {
    Card::factory()->creature()->create(['name' => 'Goblin Guide', 'type_line' => 'Creature — Goblin Scout']);
    Card::factory()->instant()->create(['name' => 'Lightning Bolt', 'type_line' => 'Instant']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['type_line' => 'Creature']);

    $response->assertOk()
        ->assertSee('Goblin Guide');
});

test('it searches by subtype', function () {
    Card::factory()->create(['name' => 'Goblin Guide', 'type_line' => 'Creature — Goblin Scout']);
    Card::factory()->create(['name' => 'Snapcaster Mage', 'type_line' => 'Creature — Human Wizard']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['subtype' => 'Goblin']);

    $response->assertOk()
        ->assertSee('Goblin Guide');
});

test('it filters by colors', function () {
    Card::factory()->create(['name' => 'Lightning Bolt', 'colors' => ['R']]);
    Card::factory()->create(['name' => 'Counterspell', 'colors' => ['U']]);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['colors' => ['R']]);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it filters by rarity', function () {
    Card::factory()->create(['name' => 'Sol Ring', 'rarity' => 'uncommon']);
    Card::factory()->create(['name' => 'Lightning Bolt', 'rarity' => 'common']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['rarity' => 'mythic']);

    $response->assertOk()
        ->assertSee('"count": 0');
});

test('it filters by cmc with operator', function () {
    Card::factory()->create(['name' => 'Lightning Bolt', 'cmc' => 1]);
    Card::factory()->create(['name' => 'Wrath of God', 'cmc' => 4]);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['cmc' => 2, 'cmc_operator' => '<=']);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it filters by format legality', function () {
    Card::factory()->create([
        'name' => 'Sol Ring',
        'legalities' => ['commander' => 'legal', 'standard' => 'not_legal'],
    ]);
    Card::factory()->create([
        'name' => 'Lightning Bolt',
        'legalities' => ['commander' => 'legal', 'standard' => 'legal'],
    ]);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['format' => 'standard']);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it filters by keyword', function () {
    Card::factory()->create(['name' => 'Serra Angel', 'keywords' => ['Flying', 'Vigilance']]);
    Card::factory()->create(['name' => 'Grizzly Bears', 'keywords' => []]);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['keyword' => 'Flying']);

    $response->assertOk()
        ->assertSee('Serra Angel');
});

test('it respects limit parameter', function () {
    Card::factory()->count(5)->create(['rarity' => 'rare']);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['rarity' => 'rare', 'limit' => 2]);

    $response->assertOk();
});

test('it combines multiple filters', function () {
    Card::factory()->create(['name' => 'Lightning Bolt', 'colors' => ['R'], 'rarity' => 'common', 'cmc' => 1]);
    Card::factory()->create(['name' => 'Fireball', 'colors' => ['R'], 'rarity' => 'rare', 'cmc' => 1]);
    Card::factory()->create(['name' => 'Counterspell', 'colors' => ['U'], 'rarity' => 'common', 'cmc' => 2]);

    $response = MtgServer::tool(SearchCardsAdvanced::class, [
        'colors' => ['R'],
        'rarity' => 'common',
    ]);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it requires at least one filter', function () {
    $response = MtgServer::tool(SearchCardsAdvanced::class, []);

    $response->assertHasErrors();
});

test('it groups by oracle_id to show unique cards', function () {
    $oracleId = fake()->uuid();

    Card::factory()->create([
        'name' => 'Lightning Bolt',
        'oracle_id' => $oracleId,
        'set' => 'a25',
        'released_at' => '2018-03-16',
    ]);
    Card::factory()->create([
        'name' => 'Lightning Bolt',
        'oracle_id' => $oracleId,
        'set' => 'clu',
        'released_at' => '2024-02-23',
    ]);

    $response = MtgServer::tool(SearchCardsAdvanced::class, ['mana_cost' => Card::first()->mana_cost]);

    $response->assertOk()
        ->assertSee('"count": 1');
});
