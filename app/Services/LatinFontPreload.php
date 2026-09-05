<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

/**
 * Builds `<link rel="preload">` tags for the locally cached web fonts that the
 * pages actually render.
 *
 * The font swap was the whole of the site's remaining layout shift: text paints
 * in a fallback face, then reflows when the real face arrives. Preloading the
 * files fixes that by getting them in before first paint.
 *
 * Google splits each family into ~27 subsets but serves one variable-weight
 * file per family and style, so filtering the cached stylesheet down to the
 * faces carrying the basic-latin range and de-duplicating leaves exactly the
 * files this site needs — four, rather than the twenty-three the package's own
 * preload option would emit.
 */
class LatinFontPreload
{
    /**
     * Marker for Google's basic-latin subset. Every subset declares its own
     * unicode-range, and only this one covers the characters on these pages.
     */
    private const LATIN_RANGE = 'U+0000-00FF';

    public function toHtml(): HtmlString
    {
        $links = collect($this->files())
            ->map(fn (string $url): string => sprintf(
                '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>',
                e($url),
            ))
            ->join("\n    ");

        return new HtmlString($links);
    }

    /**
     * Font file URLs needed for the basic-latin subset, in stylesheet order.
     *
     * @return list<string>
     */
    public function files(): array
    {
        $css = $this->stylesheet();

        if ($css === null) {
            return [];
        }

        preg_match_all('/@font-face\s*\{[^}]*\}/', $css, $faces);

        return collect($faces[0])
            ->filter(fn (string $face): bool => str_contains($face, self::LATIN_RANGE))
            ->map(function (string $face): ?string {
                preg_match('/url\(\s*[\'"]?(?<url>[^\'")]+)/', $face, $matches);

                return $matches['url'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** The stylesheet google-fonts:fetch cached for the configured families. */
    private function stylesheet(): ?string
    {
        $url = config('google-fonts.fonts.default');

        if (! is_string($url)) {
            return null;
        }

        $path = sprintf(
            '%s/%s/fonts.css',
            trim((string) config('google-fonts.path', 'fonts'), '/'),
            substr(md5($url), 0, 10),
        );

        $disk = $this->disk();

        return $disk->exists($path) ? $disk->get($path) : null;
    }

    private function disk(): Filesystem
    {
        return Storage::disk(config('google-fonts.disk', 'public'));
    }
}
