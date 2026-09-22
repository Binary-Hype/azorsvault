<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

/**
 * Single source for the locally cached web fonts the pages actually render:
 * the `<link rel="preload">` tags and the `@font-face` stylesheet itself.
 *
 * The font swap was the whole of the site's remaining layout shift: text paints
 * in a fallback face, then reflows when the real face arrives. Preloading the
 * files fixes that by getting them in before first paint.
 *
 * Google splits each family into ~27 subsets but serves one variable-weight
 * file per family and style. Filtering the cached stylesheet to the faces
 * carrying the basic-latin range leaves 14 of its 82 rules — the stylesheet the
 * package would inline is 34 KB, of which these pages use about 6 KB.
 */
class LatinFonts
{
    /**
     * Marker for Google's basic-latin subset. Every subset declares its own
     * unicode-range, and only this one covers the characters on these pages.
     */
    private const LATIN_RANGE = 'U+0000-00FF';

    /**
     * The `@font-face` rules for the basic-latin subset, as an inline
     * `<style>` tag.
     *
     * This stays inline rather than becoming a linked stylesheet: an extra
     * render-blocking request ahead of first paint is exactly what the font
     * work was trying to remove.
     */
    public function toStyleTag(): HtmlString
    {
        $faces = $this->latinFaces();

        if ($faces === []) {
            return new HtmlString('');
        }

        $css = preg_replace('/\s+/', ' ', implode('', $faces)) ?? '';

        return new HtmlString('<style>'.trim($css).'</style>');
    }

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
        return collect($this->latinFaces())
            ->map(function (string $face): ?string {
                preg_match('/url\(\s*[\'"]?(?<url>[^\'")]+)/', $face, $matches);

                return $matches['url'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The `@font-face` blocks covering the basic-latin range.
     *
     * @return list<string>
     */
    private function latinFaces(): array
    {
        $css = $this->stylesheet();

        if ($css === null) {
            return [];
        }

        preg_match_all('/@font-face\s*\{[^}]*\}/', $css, $faces);

        return collect($faces[0])
            ->filter(fn (string $face): bool => str_contains($face, self::LATIN_RANGE))
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

        /*
         * Mirrors Spatie\GoogleFonts\GoogleFonts::path(): empty segments are
         * dropped, so the configured path may be blank when the disk is
         * already rooted at the cache directory.
         */
        $path = collect([
            trim((string) config('google-fonts.path', ''), '/'),
            substr(md5($url), 0, 10),
            'fonts.css',
        ])->filter()->join('/');

        $disk = $this->disk();

        if (! $disk->exists($path)) {
            /*
             * Rendering without fonts beats a 500 on every page, but a missing
             * cache means google-fonts:fetch was skipped on deploy, so say so.
             */
            Log::error('Cached Google Fonts stylesheet is missing; run google-fonts:fetch.', [
                'path' => $path,
            ]);

            return null;
        }

        return $disk->get($path);
    }

    private function disk(): Filesystem
    {
        return Storage::disk(config('google-fonts.disk', 'fonts'));
    }
}
