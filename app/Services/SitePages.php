<?php

namespace App\Services;

/**
 * The public, indexable pages of the site.
 *
 * The sitemap and the llms.txt index are both generated from this list so a
 * new page can never appear in one and be missing from the other.
 */
class SitePages
{
    /**
     * @return list<array{route: string, priority: string, changefreq: string, title: string, summary: string}>
     */
    public function all(): array
    {
        return [
            [
                'route' => 'home',
                'priority' => '1.0',
                'changefreq' => 'weekly',
                'title' => 'Azorsvault',
                'summary' => 'What the Magic: The Gathering MCP server does, how to connect it to Claude, and the eight tools it exposes.',
            ],
            [
                'route' => 'imprint',
                'priority' => '0.3',
                'changefreq' => 'yearly',
                'title' => 'Imprint',
                'summary' => 'Provider identification and legal notice for Azorsvault.',
            ],
            [
                'route' => 'privacy',
                'priority' => '0.3',
                'changefreq' => 'yearly',
                'title' => 'Privacy Policy',
                'summary' => 'What Azorsvault stores, what it does not, and which third parties are involved.',
            ],
        ];
    }
}
