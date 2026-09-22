<?php

use App\Models\ComprehensiveRule;
use App\Services\VaultStatus;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Number;

/*
 * Truncation rather than a transaction: the fulltext tests commit their rows,
 * so a transactional test here would still see them.
 */
uses(DatabaseTruncation::class);

/*
 * The landing page used to hardcode "5 tools · Comprehensive Rules v2026.04.10",
 * which nothing kept in step with the server or the imported rules.
 */
test('it counts the tools the mcp server actually exposes', function () {
    $registered = count(glob(app_path('Mcp/Tools/*.php')) ?: []);

    expect(app(VaultStatus::class)->toolCount())->toBe($registered);
});

test('it derives the rules version from the imported rules', function () {
    ComprehensiveRule::factory()->create(['effective_date' => '2026-02-27']);
    ComprehensiveRule::factory()->create(['effective_date' => '2026-01-05']);

    expect(app(VaultStatus::class)->rulesVersion())->toBe('v2026.02.27');
});

test('it omits the rules version when nothing has been imported', function () {
    expect(app(VaultStatus::class)->rulesVersion())->toBeNull();
    expect(app(VaultStatus::class)->segments())->not->toContain('rulings sworn to');
});

test('it builds the full status line once rules are present', function () {
    ComprehensiveRule::factory()->create(['effective_date' => '2026-02-27']);

    $tools = count(glob(app_path('Mcp/Tools/*.php')) ?: []);

    expect(app(VaultStatus::class)->segments())
        ->toBe(['The vault is awake', Number::spell($tools).' keys hung on the wall', 'rulings sworn to v2026.02.27']);
});

test('the landing page renders the derived status line', function () {
    ComprehensiveRule::factory()->create(['effective_date' => '2026-02-27']);

    $tools = count(glob(app_path('Mcp/Tools/*.php')) ?: []);

    $this->get('/')
        ->assertOk()
        ->assertSee('The vault is awake · '.Number::spell($tools).' keys hung on the wall · rulings sworn to v2026.02.27');
});
