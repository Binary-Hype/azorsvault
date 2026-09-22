<?php

namespace App\Services;

use App\Mcp\Servers\MtgServer;
use App\Models\ComprehensiveRule;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Throwable;

/**
 * Facts for the landing page's status line.
 *
 * These used to be hardcoded, so the tool count and the rules version went
 * stale silently whenever a tool or an import changed.
 */
class VaultStatus
{
    /** Number of tools the MCP server exposes. */
    public function toolCount(): int
    {
        /** @var array<int, class-string> $tools */
        $tools = (new ReflectionClass(MtgServer::class))->getDefaultProperties()['tools'] ?? [];

        return count($tools);
    }

    /**
     * Version label of the imported Comprehensive Rules, or null when nothing
     * has been imported yet.
     */
    public function rulesVersion(): ?string
    {
        try {
            $effectiveDate = ComprehensiveRule::max('effective_date');
        } catch (Throwable $e) {
            /*
             * The landing page is public and cached; a database hiccup should
             * drop this one label rather than take the page down.
             */
            Log::warning('Could not read the comprehensive rules version.', ['exception' => $e->getMessage()]);

            return null;
        }

        if (! $effectiveDate) {
            return null;
        }

        return 'v'.str_replace('-', '.', substr((string) $effectiveDate, 0, 10));
    }

    /**
     * The status line segments, ready to join.
     *
     * @return list<string>
     */
    public function segments(): array
    {
        $segments = ['Live', $this->toolCount().' tools'];

        if ($version = $this->rulesVersion()) {
            $segments[] = 'Comprehensive Rules '.$version;
        }

        return $segments;
    }
}
