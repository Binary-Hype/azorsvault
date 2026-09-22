<?php

return [

    /*
     * Fonts registered here are prefetched by the google-fonts:fetch command.
     * The @googlefonts directive is not used; App\Services\LatinFonts reads
     * the cached stylesheet and emits the parts these pages render.
     */
    'fonts' => [
        'default' => 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap',
    ],

    /*
     * The `fonts` disk is rooted at public/fonts, so the files nginx serves are
     * the files this writes — no storage symlink in the request path. The URLs
     * baked into the cached stylesheet come from this disk's `url`.
     */
    'disk' => 'fonts',

    /*
     * Empty: the `fonts` disk is already rooted at public/fonts, so a prefix
     * here would nest the cache at public/fonts/fonts.
     */
    'path' => '',

    /*
     * Irrelevant while the @googlefonts directive is unused: App\Services\
     * LatinFonts inlines the basic-latin faces itself, which is 14 of the
     * stylesheet's 82 rules. Kept at the package default.
     */
    'inline' => true,

    /*
     * This stays false because the package preloads every subset it fetched —
     * 23 files, most of which these pages never render. App\Services\
     * LatinFonts emits the four latin files instead, which is what the
     * layout links and what keeps the font swap from shifting the layout.
     */
    'preload' => false,

    /*
     * Fonts must never be requested from Google at render time, so the
     * fallback to Google's CDN stays disabled. Run google-fonts:fetch on
     * deploy; without the local files the pages render in fallback faces and
     * LatinFonts logs an error (it no longer throws, so a missed fetch cannot
     * take every page down).
     */
    'fallback' => false,

    /*
     * This user agent will be used to request the stylesheet from Google Fonts.
     * This is the Safari 14 user agent that only targets modern browsers. If
     * you want to target older browsers, use different user agent string.
     */
    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15',

];
