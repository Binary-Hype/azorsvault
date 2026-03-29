<?php

use App\Mcp\Servers\MtgServer;
use App\Mcp\Tools\SearchRules;
use App\Models\ComprehensiveRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it finds rules by keyword search', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.5a',
        'content' => 'If a player has 0 or less life, that player loses the game.',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '100.1',
        'content' => 'These Magic rules apply to any Magic game.',
    ]);

    $response = MtgServer::tool(SearchRules::class, ['query' => 'loses the game']);

    $response->assertOk()
        ->assertSee('704.5a')
        ->assertSee('0 or less life');
});

test('it filters by section', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.5a',
        'section' => 7,
        'chapter' => '704',
        'content' => 'If a player has 0 or less life, that player loses the game.',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '104.3a',
        'section' => 1,
        'chapter' => '104',
        'content' => 'A player can concede the game at any time and loses the game.',
    ]);

    $response = MtgServer::tool(SearchRules::class, [
        'query' => 'loses the game',
        'section' => 7,
    ]);

    $response->assertOk()
        ->assertSee('704.5a')
        ->assertDontSee('104.3a');
});

test('it filters by chapter', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19',
        'section' => 7,
        'chapter' => '702',
        'content' => 'Trample is a keyword ability.',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '704.5a',
        'section' => 7,
        'chapter' => '704',
        'content' => 'A creature with trample and 0 toughness is put into a graveyard.',
    ]);

    $response = MtgServer::tool(SearchRules::class, [
        'query' => 'trample',
        'chapter' => '702',
    ]);

    $response->assertOk()
        ->assertSee('702.19')
        ->assertDontSee('704.5a');
});

test('it includes glossary entries by default', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19',
        'content' => 'Trample is a keyword ability.',
    ]);
    ComprehensiveRule::factory()->glossary('Trample')->create([
        'content' => "Trample\nA keyword ability that modifies how a creature assigns combat damage.",
    ]);

    $response = MtgServer::tool(SearchRules::class, ['query' => 'Trample']);

    $response->assertOk()
        ->assertSee('702.19')
        ->assertSee('glossary:trample');
});

test('it excludes glossary entries when requested', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19',
        'content' => 'Trample is a keyword ability.',
    ]);
    ComprehensiveRule::factory()->glossary('Trample')->create([
        'content' => "Trample\nA keyword ability that modifies how a creature assigns combat damage.",
    ]);

    $response = MtgServer::tool(SearchRules::class, [
        'query' => 'Trample',
        'include_glossary' => false,
    ]);

    $response->assertOk()
        ->assertSee('702.19')
        ->assertDontSee('glossary:trample');
});

test('it respects limit', function () {
    for ($i = 1; $i <= 5; $i++) {
        ComprehensiveRule::factory()->create([
            'rule_number' => "100.{$i}",
            'section' => 1,
            'chapter' => '100',
            'content' => "Rule about magic game number {$i}.",
        ]);
    }

    $response = MtgServer::tool(SearchRules::class, [
        'query' => 'magic game',
        'limit' => 2,
    ]);

    $response->assertOk()
        ->assertSee('"count": 2');
});
