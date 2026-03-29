<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\SearchCard;
use App\Models\Card;
use App\Models\Ruling;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it finds a card by exact name', function () {
    Card::factory()->create(['name' => 'Lightning Bolt', 'mana_cost' => '{R}']);

    $response = MtgServer::tool(SearchCard::class, ['name' => 'Lightning Bolt']);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it finds card case-insensitively', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);

    $response = MtgServer::tool(SearchCard::class, ['name' => 'lightning bolt']);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it returns most recent printing', function () {
    Card::factory()->create([
        'name' => 'Lightning Bolt',
        'set' => 'a25',
        'released_at' => '2018-03-16',
    ]);
    Card::factory()->create([
        'name' => 'Lightning Bolt',
        'set' => 'clu',
        'released_at' => '2024-02-23',
    ]);

    $response = MtgServer::tool(SearchCard::class, ['name' => 'Lightning Bolt']);

    $response->assertOk()
        ->assertSee('clu');
});

test('it includes rulings in response', function () {
    $card = Card::factory()->create(['name' => 'Lightning Bolt']);
    Ruling::factory()->forCard($card)->create([
        'source' => 'wotc',
        'published_at' => '2024-06-07',
        'comment' => 'Lightning Bolt deals 3 damage to any target.',
    ]);

    $response = MtgServer::tool(SearchCard::class, ['name' => 'Lightning Bolt']);

    $response->assertOk()
        ->assertSee('Lightning Bolt deals 3 damage to any target.')
        ->assertSee('wotc');
});

test('it returns error for unknown card', function () {
    $response = MtgServer::tool(SearchCard::class, ['name' => 'Nonexistent Card']);

    $response->assertHasErrors();
});
