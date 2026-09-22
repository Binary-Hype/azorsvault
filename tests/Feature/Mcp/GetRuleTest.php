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

/*
 * A chapter with more rules than the cap used to be truncated silently: only
 * the section branch reported `total` and a truncation note.
 */
test('it reports the total and a note when a chapter is truncated', function () {
    ComprehensiveRule::factory()
        ->count(105)
        ->sequence(fn ($sequence) => [
            'rule_number' => '704.'.($sequence->index + 1),
            'chapter' => '704',
            'section' => 7,
            'is_glossary' => false,
        ])
        ->create();

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '704']);

    $response->assertOk()
        ->assertSee('"count": 100')
        ->assertSee('"total": 105')
        ->assertSee('Results truncated');
});

/*
 * A bare rule number used to return only its own sentence, leaving the
 * lettered subrules that carry the actual detail behind.
 */
test('it returns a rule together with its lettered subrules', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19',
        'chapter' => '702',
        'section' => 7,
        'content' => 'Trample',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19a',
        'chapter' => '702',
        'section' => 7,
        'content' => 'Trample is a static ability.',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19b',
        'chapter' => '702',
        'section' => 7,
        'content' => 'The controller of an attacking creature with trample first assigns damage.',
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '702.19']);

    $response->assertOk()
        ->assertSee('"subrules"')
        ->assertSee('702.19a')
        ->assertSee('Trample is a static ability.')
        ->assertSee('702.19b');
});

test('it does not mistake a sibling rule for a subrule', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19',
        'chapter' => '702',
        'section' => 7,
        'content' => 'Trample',
    ]);
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.190',
        'chapter' => '702',
        'section' => 7,
        'content' => 'Squad',
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '702.19']);

    $response->assertOk()
        ->assertDontSee('702.190')
        ->assertDontSee('Squad');
});

test('it omits the subrules key for a rule that has none', function () {
    ComprehensiveRule::factory()->create([
        'rule_number' => '702.19a',
        'chapter' => '702',
        'section' => 7,
        'content' => 'Trample is a static ability.',
    ]);

    $response = MtgServer::tool(GetRule::class, ['rule_number' => '702.19a']);

    $response->assertOk()
        ->assertDontSee('subrules');
});
