<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\CheckLegality;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns the full legality map for a card', function () {
    Card::factory()->create([
        'name' => 'Lightning Bolt',
        'legalities' => ['standard' => 'not_legal', 'modern' => 'legal', 'commander' => 'legal'],
    ]);

    $response = MtgServer::tool(CheckLegality::class, ['name' => 'Lightning Bolt']);

    $response->assertOk()
        ->assertSee('Lightning Bolt')
        ->assertSee('"modern": "legal"')
        ->assertSee('"standard": "not_legal"');
});

test('it lists the formats a card is banned or restricted in', function () {
    Card::factory()->create([
        'name' => 'Black Lotus',
        'legalities' => ['vintage' => 'restricted', 'legacy' => 'banned', 'commander' => 'banned'],
        'reserved' => true,
    ]);

    $response = MtgServer::tool(CheckLegality::class, ['name' => 'Black Lotus']);

    $response->assertOk()
        ->assertSee('"banned_in"')
        ->assertSee('legacy')
        ->assertSee('"restricted_in"')
        ->assertSee('vintage')
        ->assertSee('"reserved": true');
});

test('it reports whether a card is a game changer', function () {
    Card::factory()->create([
        'name' => 'Rhystic Study',
        'game_changer' => true,
    ]);

    $response = MtgServer::tool(CheckLegality::class, ['name' => 'Rhystic Study']);

    $response->assertOk()
        ->assertSee('"game_changer": true');
});

test('it finds a card case-insensitively', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);

    $response = MtgServer::tool(CheckLegality::class, ['name' => 'lightning bolt']);

    $response->assertOk()
        ->assertSee('Lightning Bolt');
});

test('it returns error for unknown card', function () {
    $response = MtgServer::tool(CheckLegality::class, ['name' => 'Nonexistent Card']);

    $response->assertHasErrors();
});
