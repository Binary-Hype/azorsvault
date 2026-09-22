<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared FULLTEXT search behaviour for the models that carry a FULLTEXT index.
 *
 * A leading-wildcard LIKE can never use an index, so these searches used to
 * scan the whole table. Boolean-mode FULLTEXT uses the index and still allows
 * partial matches by appending a prefix wildcard to each term.
 */
trait SearchesFullText
{
    /**
     * Terms shorter than InnoDB's `innodb_ft_min_token_size` are not indexed,
     * so a search made up only of such terms has to fall back to LIKE.
     */
    private const MIN_FULLTEXT_TOKEN = 3;

    /**
     * Characters that carry meaning in FULLTEXT boolean mode and would
     * otherwise let user input change how the query is parsed.
     */
    private const BOOLEAN_OPERATORS = ['+', '-', '>', '<', '(', ')', '~', '*', '"', '@'];

    /**
     * InnoDB's default stopword list. These words are never indexed, so
     * requiring one with `+` would make the whole search match nothing.
     *
     * @see INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD
     */
    private const STOPWORDS = [
        'a', 'about', 'an', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en',
        'for', 'from', 'how', 'i', 'in', 'is', 'it', 'la', 'of', 'on', 'or',
        'that', 'the', 'this', 'to', 'und', 'was', 'what', 'when', 'where',
        'who', 'will', 'with', 'www',
    ];

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeFullTextSearch(Builder $query, string $column, string $search): void
    {
        $terms = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $indexable = array_values(array_filter(
            array_map(fn (string $term) => str_replace(self::BOOLEAN_OPERATORS, '', $term), $terms),
            fn (string $term) => mb_strlen($term) >= self::MIN_FULLTEXT_TOKEN
                && ! in_array(mb_strtolower($term), self::STOPWORDS, true),
        ));

        if ($indexable === []) {
            $query->where($column, 'LIKE', '%'.$search.'%');

            return;
        }

        // Every term is required, and each may match as a prefix.
        $expression = implode(' ', array_map(fn (string $term) => '+'.$term.'*', $indexable));

        $query->whereFullText($column, $expression, ['mode' => 'boolean']);
    }
}
