<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    /** @use HasFactory<\Database\Factories\CardFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cmc' => 'decimal:1',
            'colors' => 'array',
            'color_identity' => 'array',
            'keywords' => 'array',
            'image_uris' => 'array',
            'legalities' => 'array',
            'prices' => 'array',
            'card_faces' => 'array',
            'all_parts' => 'array',
            'games' => 'array',
            'finishes' => 'array',
            'released_at' => 'date',
            'reprint' => 'boolean',
            'digital' => 'boolean',
            'reserved' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Ruling, $this>
     */
    public function rulings(): HasMany
    {
        return $this->hasMany(Ruling::class, 'oracle_id', 'oracle_id');
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByExactName(Builder $query, string $name): void
    {
        $query->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByNameSearch(Builder $query, string $search): void
    {
        $query->where('name', 'LIKE', '%' . $search . '%');
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByManaCost(Builder $query, string $manaCost): void
    {
        $query->where('mana_cost', $manaCost);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByOracleText(Builder $query, string $search): void
    {
        $query->where('oracle_text', 'LIKE', '%' . $search . '%');
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByTypeLine(Builder $query, string $type): void
    {
        $query->where('type_line', 'LIKE', '%' . $type . '%');
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeBySubtype(Builder $query, string $subtype): void
    {
        $query->where('type_line', 'LIKE', '%— %' . $subtype . '%');
    }

    /**
     * @param  Builder<Card>  $query
     * @param  array<int, string>  $colors
     */
    public function scopeByColors(Builder $query, array $colors): void
    {
        foreach ($colors as $color) {
            $query->whereJsonContains('colors', $color);
        }
    }

    /**
     * @param  Builder<Card>  $query
     * @param  array<int, string>  $colors
     */
    public function scopeByColorIdentity(Builder $query, array $colors): void
    {
        foreach ($colors as $color) {
            $query->whereJsonContains('color_identity', $color);
        }
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByRarity(Builder $query, string $rarity): void
    {
        $query->where('rarity', $rarity);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeBySet(Builder $query, string $set): void
    {
        $query->where('set', $set);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByKeyword(Builder $query, string $keyword): void
    {
        $query->whereJsonContains('keywords', $keyword);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByPower(Builder $query, string $power): void
    {
        $query->where('power', $power);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByToughness(Builder $query, string $toughness): void
    {
        $query->where('toughness', $toughness);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByCmc(Builder $query, float $cmc, string $operator = '='): void
    {
        $allowed = ['=', '<', '>', '<=', '>='];
        $operator = in_array($operator, $allowed) ? $operator : '=';

        $query->where('cmc', $operator, $cmc);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByLegality(Builder $query, string $format, string $legality = 'legal'): void
    {
        $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(legalities, ?)) = ?', ['$.' . $format, $legality]);
    }

    /**
     * @param  Builder<Card>  $query
     */
    public function scopeByMaxEdhrecRank(Builder $query, int $maxRank): void
    {
        $query->where('edhrec_rank', '<=', $maxRank);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchResult(): array
    {
        $result = [
            'id' => $this->id,
            'oracle_id' => $this->oracle_id,
            'name' => $this->name,
            'mana_cost' => $this->mana_cost,
            'cmc' => $this->cmc,
            'type_line' => $this->type_line,
            'oracle_text' => $this->oracle_text,
            'colors' => $this->colors,
            'color_identity' => $this->color_identity,
            'keywords' => $this->keywords,
            'power' => $this->power,
            'toughness' => $this->toughness,
            'loyalty' => $this->loyalty,
            'layout' => $this->layout,
            'set' => $this->set,
            'set_name' => $this->set_name,
            'collector_number' => $this->collector_number,
            'rarity' => $this->rarity,
            'released_at' => $this->released_at?->toDateString(),
            'image_uris' => $this->image_uris,
            'legalities' => $this->legalities,
            'prices' => $this->prices,
            'edhrec_rank' => $this->edhrec_rank,
            'flavor_text' => $this->flavor_text,
        ];

        if ($this->relationLoaded('rulings')) {
            $result['rulings'] = $this->rulings
                ->sortByDesc('published_at')
                ->map(fn (Ruling $ruling) => [
                    'source' => $ruling->source,
                    'published_at' => $ruling->published_at->toDateString(),
                    'comment' => $ruling->comment,
                ])
                ->values()
                ->all();
        }

        return $result;
    }
}
