<?php

namespace App\Http\Controllers;

use App\Services\SitePages;
use Illuminate\Http\Response;

/**
 * The XML sitemap, built from the same page list that feeds llms.txt.
 */
class SitemapController
{
    public function __construct(private SitePages $pages) {}

    public function __invoke(): Response
    {
        return response()
            ->view('sitemap', ['pages' => $this->pages->all()])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
