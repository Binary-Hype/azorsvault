<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\SearchCards;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it finds multiple cards by name', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);
    Card::factory()->create(['name' => 'Dark Ritual']);

    $response = MtgServer::tool(SearchCards::class, [
        'names' => ['Lightning Bolt', 'Dark Ritual'],
    ]);

    $response->assertOk()
        ->assertSee('Lightning Bolt')
        ->assertSee('Dark Ritual');
});

test('it returns null for cards not found in batch', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);

    $response = MtgServer::tool(SearchCards::class, [
        'names' => ['Lightning Bolt', 'Nonexistent Card'],
    ]);

    $response->assertOk()
        ->assertSee('Lightning Bolt')
        ->assertSee('null');
});

test('it validates names array is required', function () {
    $response = MtgServer::tool(SearchCards::class, []);

    $response->assertHasErrors();
});
