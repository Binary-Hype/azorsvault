<?php

namespace App\Http\Controllers;

use App\Services\SitePages;
use App\Services\VaultStatus;
use Illuminate\Http\Response;

/**
 * /llms.txt — a plain-Markdown index for assistants that read a site before
 * answering about it. Deliberately short: the point is the MCP endpoint.
 */
class LlmsTxtController
{
    public function __construct(
        private SitePages $pages,
        private VaultStatus $vaultStatus,
    ) {}

    public function __invoke(): Response
    {
        return response()
            ->view('llms', [
                'pages' => $this->pages->all(),
                'toolCount' => $this->vaultStatus->toolCount(),
                'rulesVersion' => $this->vaultStatus->rulesVersion(),
            ])
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
