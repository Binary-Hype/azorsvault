<?php

use App\Models\ComprehensiveRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('byRuleNumber scope finds rule by exact number', function () {
    ComprehensiveRule::factory()->create(['rule_number' => '704.5a']);
    ComprehensiveRule::factory()->create(['rule_number' => '704.5b']);

    expect(ComprehensiveRule::byRuleNumber('704.5a')->count())->toBe(1);
    expect(ComprehensiveRule::byRuleNumber('704.5a')->first()->rule_number)->toBe('704.5a');
    expect(ComprehensiveRule::byRuleNumber('999.99')->first())->toBeNull();
});

test('byChapter scope filters by chapter', function () {
    ComprehensiveRule::factory()->create(['chapter' => '704']);
    ComprehensiveRule::factory()->create(['chapter' => '704']);
    ComprehensiveRule::factory()->create(['chapter' => '100']);

    expect(ComprehensiveRule::byChapter('704')->count())->toBe(2);
    expect(ComprehensiveRule::byChapter('100')->count())->toBe(1);
});

test('bySection scope filters by section', function () {
    ComprehensiveRule::factory()->create(['section' => 7]);
    ComprehensiveRule::factory()->create(['section' => 7]);
    ComprehensiveRule::factory()->create(['section' => 1]);

    expect(ComprehensiveRule::bySection(7)->count())->toBe(2);
    expect(ComprehensiveRule::bySection(1)->count())->toBe(1);
});

test('byContentSearch scope uses LIKE matching on content', function () {
    ComprehensiveRule::factory()->create(['content' => 'A player loses the game if their life total is 0 or less.']);
    ComprehensiveRule::factory()->create(['content' => 'These rules apply to any Magic game.']);

    expect(ComprehensiveRule::byContentSearch('loses the game')->count())->toBe(1);
    expect(ComprehensiveRule::byContentSearch('Magic')->count())->toBe(1);
    expect(ComprehensiveRule::byContentSearch('nonexistent')->count())->toBe(0);
});

test('glossary scope returns only glossary entries', function () {
    ComprehensiveRule::factory()->glossary('Trample')->create();
    ComprehensiveRule::factory()->glossary('Flying')->create();
    ComprehensiveRule::factory()->create(['rule_number' => '704.5a']);

    expect(ComprehensiveRule::glossary()->count())->toBe(2);
    expect(ComprehensiveRule::glossary()->pluck('rule_number')->all())
        ->toContain('glossary:trample', 'glossary:flying');
});

test('rules scope excludes glossary entries', function () {
    ComprehensiveRule::factory()->glossary('Trample')->create();
    ComprehensiveRule::factory()->create(['rule_number' => '704.5a']);
    ComprehensiveRule::factory()->create(['rule_number' => '100.1']);

    expect(ComprehensiveRule::rules()->count())->toBe(2);
    expect(ComprehensiveRule::rules()->pluck('rule_number')->all())
        ->toContain('704.5a', '100.1')
        ->not->toContain('glossary:trample');
});

test('effective_date is cast to a date', function () {
    $rule = ComprehensiveRule::factory()->create(['effective_date' => '2026-02-27']);

    expect($rule->effective_date)->toBeInstanceOf(Carbon::class);
    expect($rule->effective_date->toDateString())->toBe('2026-02-27');
});

test('is_glossary is cast to a boolean', function () {
    $rule = ComprehensiveRule::factory()->glossary('Trample')->create();

    expect($rule->is_glossary)->toBeTrue();
});
