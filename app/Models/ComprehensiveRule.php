<?php

namespace App\Models;

use Database\Factories\ComprehensiveRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprehensiveRule extends Model
{
    /** @use HasFactory<ComprehensiveRuleFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'is_glossary' => 'boolean',
        ];
    }

    /**
     * @param  Builder<ComprehensiveRule>  $query
     */
    public function scopeByRuleNumber(Builder $query, string $number): void
    {
        $query->where('rule_number', $number);
    }

    /**
     * @param  Builder<ComprehensiveRule>  $query
     */
    public function scopeByChapter(Builder $query, string $chapter): void
    {
        $query->where('chapter', $chapter);
    }

    /**
     * @param  Builder<ComprehensiveRule>  $query
     */
    public function scopeBySection(Builder $query, int $section): void
    {
        $query->where('section', $section);
    }

    /**
     * @param  Builder<ComprehensiveRule>  $query
     */
    public function scopeByContentSearch(Builder $query, string $search): void
    {
        $query->where('content', 'LIKE', '%'.$search.'%');
    }

    /**
     * @param  Builder<ComprehensiveRule>  $query
     */
    public function scopeGlossary(Builder $query): void
    {
        $query->where('is_glossary', true);
    }

    /**
     * @param  Builder<ComprehensiveRule>  $query
     */
    public function scopeRules(Builder $query): void
    {
        $query->where('is_glossary', false);
    }
}
