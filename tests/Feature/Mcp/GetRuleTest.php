<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\GetRule;
use App\Models\ComprehensiveRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it returns a specific rule by exact number', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.5a',
        'section' => 7,
        'chapter' => '704',
        'content' => 'If a player has 0 or less life, that player loses the game.',
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '704.5a']);

    $response->assertOk()
        ->assertSee('704.5a')
        ->assertSee('0 or less life');
});

test('it returns all rules in a chapter', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '704',
        'section' => 7,
        'chapter' => '704',
        'content' => 'State-Based Actions',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.1',
        'section' => 7,
        'chapter' => '704',
        'content' => 'State-based actions are game actions that happen automatically.',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.5a',
        'section' => 7,
        'chapter' => '704',
        'content' => 'If a player has 0 or less life, that player loses the game.',
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '704']);

    $response->assertOk()
        ->assertSee('State-Based Actions')
        ->assertSee('704.1')
        ->assertSee('704.5a');
});

test('it returns all rules in a section', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '700.1',
        'section' => 7,
        'chapter' => '700',
        'content' => 'General rules for additional rules.',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.1',
        'section' => 7,
        'chapter' => '704',
        'content' => 'State-based actions are game actions.',
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '7']);

    $response->assertOk()
        ->assertSee('700.1')
        ->assertSee('704.1');
});

test('it returns error for nonexistent rule', function () {
    $response = MtgServer::tool(GetRule::class, ['rule_number' => '999.99']);

    $response->assertHasErrors();
});

test('it looks up a glossary term', function () {
    ComprehensiveRule::factory()->glossary('Trample')->create([
        'content' => "Trample\nA keyword ability that modifies how a creature assigns combat damage.",
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => 'glossary:trample']);

    $response->assertOk()
        ->assertSee('Trample')
        ->assertSee('keyword ability');
});

test('it returns error for nonexistent glossary term', function () {
    $response = MtgServer::tool(GetRule::class, ['rule_number' => 'glossary:nonexistent']);

    $response->assertHasErrors();
});
