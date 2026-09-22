<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\GetBannedList;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it lists the cards banned in a format', function () {
    Card::factory()->create([
        'name' => 'Primeval Titan',
        'legalities' => ['commander' => 'banned'],
    ]);
    Card::factory()->create([
        'name' => 'Sol Ring',
        'legalities' => ['commander' => 'legal'],
    ]);

    $response = MtgServer::tool(GetBannedList::class, ['format' => 'commander']);

    $response->assertOk()
        ->assertSee('Primeval Titan')
        ->assertSee('"count": 1')
        ->assertDontSee('Sol Ring');
});

test('it lists restricted cards when asked', function () {
    Card::factory()->create([
        'name' => 'Black Lotus',
        'legalities' => ['vintage' => 'restricted'],
    ]);
    Card::factory()->create([
        'name' => 'Chaos Orb',
        'legalities' => ['vintage' => 'banned'],
    ]);

    $response = MtgServer::tool(GetBannedList::class, [
        'format' => 'vintage',
        'status' => 'restricted',
    ]);

    $response->assertOk()
        ->assertSee('Black Lotus')
        ->assertDontSee('Chaos Orb');
});

test('it lists each banned card once regardless of printings', function () {
    Card::factory()->count(3)->create([
        'name' => 'Primeval Titan',
        'legalities' => ['commander' => 'banned'],
    ]);

    $response = MtgServer::tool(GetBannedList::class, ['format' => 'commander']);

    $response->assertOk()
        ->assertSee('"count": 1');
});

test('it returns error when nothing is banned in the format', function () {
    Card::factory()->create(['legalities' => ['modern' => 'legal']]);

    $response = MtgServer::tool(GetBannedList::class, ['format' => 'modern']);

    $response->assertHasErrors();
});

test('it rejects an unknown format', function () {
    $response = MtgServer::tool(GetBannedList::class, ['format' => 'kitchen-table']);

    $response->assertHasErrors();
});
