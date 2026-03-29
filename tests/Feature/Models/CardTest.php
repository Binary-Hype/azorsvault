<?php

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('byExactName scope finds card case-insensitively', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);

    expect(Card::byExactName('lightning bolt')->first()->name)->toBe('Lightning Bolt');
    expect(Card::byExactName('LIGHTNING BOLT')->first()->name)->toBe('Lightning Bolt');
    expect(Card::byExactName('Nonexistent Card')->first())->toBeNull();
});

test('byNameSearch scope uses LIKE for short terms', function () {
    Card::factory()->create(['name' => 'Opt']);
    Card::factory()->create(['name' => 'Optimism']);

    expect(Card::byNameSearch('Opt')->count())->toBe(2);
});

test('byNameSearch scope uses fulltext for longer terms', function () {
    Card::factory()->create(['name' => 'Lightning Bolt']);
    Card::factory()->create(['name' => 'Lightning Strike']);
    Card::factory()->create(['name' => 'Dark Ritual']);

    expect(Card::byNameSearch('Lightning')->count())->toBe(2);
});

test('byManaCost scope filters by exact mana cost', function () {
    Card::factory()->create(['mana_cost' => '{R}']);
    Card::factory()->create(['mana_cost' => '{1}{R}']);

    expect(Card::byManaCost('{R}')->count())->toBe(1);
});

test('byOracleText scope searches rules text', function () {
    Card::factory()->create(['oracle_text' => 'Lightning Bolt deals 3 damage to any target.']);
    Card::factory()->create(['oracle_text' => 'Draw two cards.']);

    expect(Card::byOracleText('damage')->count())->toBe(1);
});

test('byTypeLine scope filters by type', function () {
    Card::factory()->create(['type_line' => 'Creature — Human Wizard']);
    Card::factory()->create(['type_line' => 'Instant']);

    expect(Card::byTypeLine('Creature')->count())->toBe(1);
});

test('bySubtype scope filters by subtype after em-dash', function () {
    Card::factory()->create(['type_line' => 'Creature — Human Wizard']);
    Card::factory()->create(['type_line' => 'Creature — Elf Druid']);

    expect(Card::bySubtype('Wizard')->count())->toBe(1);
});

test('byColors scope filters cards containing all specified colors', function () {
    Card::factory()->create(['colors' => ['R', 'G']]);
    Card::factory()->create(['colors' => ['R']]);
    Card::factory()->create(['colors' => ['U']]);

    expect(Card::byColors(['R'])->count())->toBe(2);
    expect(Card::byColors(['R', 'G'])->count())->toBe(1);
});

test('byColorIdentity scope filters by color identity', function () {
    Card::factory()->create(['color_identity' => ['W', 'U']]);
    Card::factory()->create(['color_identity' => ['B']]);

    expect(Card::byColorIdentity(['W'])->count())->toBe(1);
});

test('byRarity scope filters by rarity', function () {
    Card::factory()->create(['rarity' => 'mythic']);
    Card::factory()->create(['rarity' => 'common']);

    expect(Card::byRarity('mythic')->count())->toBe(1);
});

test('bySet scope filters by set code', function () {
    Card::factory()->create(['set' => 'm21']);
    Card::factory()->create(['set' => 'neo']);

    expect(Card::bySet('m21')->count())->toBe(1);
});

test('byKeyword scope filters by keyword ability', function () {
    Card::factory()->create(['keywords' => ['Flying', 'Vigilance']]);
    Card::factory()->create(['keywords' => ['Trample']]);

    expect(Card::byKeyword('Flying')->count())->toBe(1);
});

test('byPower and byToughness scopes filter creatures', function () {
    Card::factory()->create(['power' => '3', 'toughness' => '2']);
    Card::factory()->create(['power' => '1', 'toughness' => '1']);

    expect(Card::byPower('3')->count())->toBe(1);
    expect(Card::byToughness('2')->count())->toBe(1);
});

test('byCmc scope supports comparison operators', function () {
    Card::factory()->create(['cmc' => 2]);
    Card::factory()->create(['cmc' => 4]);
    Card::factory()->create(['cmc' => 6]);

    expect(Card::byCmc(4)->count())->toBe(1);
    expect(Card::byCmc(4, '<=')->count())->toBe(2);
    expect(Card::byCmc(4, '>')->count())->toBe(1);
});

test('byLegality scope filters by format legality', function () {
    Card::factory()->create(['legalities' => ['commander' => 'legal', 'standard' => 'not_legal']]);
    Card::factory()->create(['legalities' => ['commander' => 'banned', 'standard' => 'legal']]);

    expect(Card::byLegality('commander', 'legal')->count())->toBe(1);
    expect(Card::byLegality('standard', 'legal')->count())->toBe(1);
});

test('byMaxEdhrecRank scope filters by popularity', function () {
    Card::factory()->create(['edhrec_rank' => 50]);
    Card::factory()->create(['edhrec_rank' => 500]);

    expect(Card::byMaxEdhrecRank(100)->count())->toBe(1);
});

test('toSearchResult returns curated card data', function () {
    $card = Card::factory()->create(['name' => 'Test Card']);

    $result = $card->toSearchResult();

    expect($result)
        ->toHaveKeys(['id', 'oracle_id', 'name', 'mana_cost', 'cmc', 'type_line', 'oracle_text', 'colors', 'rarity', 'legalities', 'prices'])
        ->and($result['name'])->toBe('Test Card');
});
