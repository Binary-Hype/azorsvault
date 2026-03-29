<?php

use App\Console\Commands\ImportComprehensiveRules;
use App\Models\ComprehensiveRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeRulesPageWithDownload(string $downloadUrl = 'https://media.wizards.com/2026/downloads/MagicCompRules%2020260227.txt', ?string $txtContent = null): void
{
    Http::fake([
        'magic.wizards.com/en/rules' => Http::response('<a href="'.$downloadUrl.'">TXT</a>'),
        'media.wizards.com/*' => Http::response($txtContent ?? ''),
    ]);
}

function sampleRulesTxt(): string
{
    return <<<'TXT'
Magic: The Gathering Comprehensive Rules

These rules are effective as of February 27, 2026.

Introduction

This document is the ultimate authority for Magic competitive game play.

Contents

1. Game Concepts
100. General

1. Game Concepts

100. General

100.1. These Magic rules apply to any Magic game with two or more players,
including two-player games and multiplayer games.

100.1a A two-player game is a game that begins with only two players.

100.1b A multiplayer game is a game that begins with more than two players.
See section 8, "Multiplayer Rules."

100.2. To play, each player needs their own deck of traditional Magic cards,
small items to represent any tokens and counters, and some way to clearly
track life totals.

704. State-Based Actions

704.1. State-based actions are game actions that happen automatically whenever
certain conditions are met.

704.5a If a player has 0 or less life, that player loses the game.

Glossary

Abandon
To turn a face-up ongoing scheme card face down and put it on the bottom of its owner's scheme deck.

Trample
A keyword ability that modifies how a creature assigns combat damage.
See rule 702.19, "Trample."

Credits

Some credits here.
TXT;
}

test('it skips import when already imported today', function () {
    Cache::put('rules:last_comprehensive_import', now()->toDateString(), now()->addDay());

    $this->artisan('rules:import-comprehensive')
        ->expectsOutputToContain('Already imported today')
        ->assertSuccessful();
});

test('it runs import when forced even if already imported today', function () {
    Cache::put('rules:last_comprehensive_import', now()->toDateString(), now()->addDay());

    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    expect(ComprehensiveRule::count())->toBeGreaterThan(0);
});

test('it rejects download URIs from untrusted domains', function () {
    Http::fake([
        'magic.wizards.com/en/rules' => Http::response('<a href="https://evil.example.com/rules.txt">TXT</a>'),
    ]);

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->expectsOutputToContain('Could not find rules download URL')
        ->assertFailed();
});

test('it fails gracefully when wizards page is unreachable', function () {
    Http::fake([
        'magic.wizards.com/en/rules' => Http::response('', 500),
    ]);

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->expectsOutputToContain('Could not find rules download URL')
        ->assertFailed();
});

test('it fails gracefully when no download link found on page', function () {
    Http::fake([
        'magic.wizards.com/en/rules' => Http::response('<html><body>No links here</body></html>'),
    ]);

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->expectsOutputToContain('Could not find rules download URL')
        ->assertFailed();
});

test('it parses rules correctly from txt format', function () {
    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    $rule = ComprehensiveRule::byRuleNumber('100.1')->first();
    expect($rule)->not->toBeNull();
    expect($rule->content)->toContain('These Magic rules apply');
    expect($rule->section)->toBe(1);
    expect($rule->chapter)->toBe('100');
    expect($rule->is_glossary)->toBeFalse();
    expect($rule->effective_date->toDateString())->toBe('2026-02-27');
});

test('it parses chapter headers', function () {
    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    $chapter = ComprehensiveRule::byRuleNumber('704')->first();
    expect($chapter)->not->toBeNull();
    expect($chapter->content)->toBe('State-Based Actions');
});

test('it parses glossary entries correctly', function () {
    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    $entry = ComprehensiveRule::byRuleNumber('glossary:trample')->first();
    expect($entry)->not->toBeNull();
    expect($entry->content)->toContain('Trample');
    expect($entry->content)->toContain('keyword ability');
    expect($entry->is_glossary)->toBeTrue();
    expect($entry->section)->toBeNull();
    expect($entry->chapter)->toBeNull();
});

test('it handles multi-line rules correctly', function () {
    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    $rule = ComprehensiveRule::byRuleNumber('100.1b')->first();
    expect($rule)->not->toBeNull();
    expect($rule->content)->toContain('multiplayer game');
    expect($rule->content)->toContain('See section 8');
});

test('it removes stale rules not present in import', function () {
    $staleRule = ComprehensiveRule::factory()->create([
        'rule_number' => '999.99',
        'content' => 'This rule was removed.',
        'updated_at' => now()->subDay(),
    ]);

    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    expect(ComprehensiveRule::find($staleRule->id))->toBeNull();
    expect(ComprehensiveRule::byRuleNumber('100.1')->exists())->toBeTrue();
});

test('it sets cache key after successful import', function () {
    Cache::forget('rules:last_comprehensive_import');

    fakeRulesPageWithDownload(txtContent: sampleRulesTxt());

    $this->artisan('rules:import-comprehensive --force --no-progress')
        ->assertSuccessful();

    expect(Cache::get('rules:last_comprehensive_import'))->toBe(now()->toDateString());
});

test('it extracts effective date from header', function () {
    $command = new ImportComprehensiveRules;

    $reflection = new ReflectionMethod($command, 'extractEffectiveDate');

    expect($reflection->invoke($command, 'These rules are effective as of February 27, 2026.'))->toBe('2026-02-27');
    expect($reflection->invoke($command, 'No date here'))->toBe('2026-01-01');
});
