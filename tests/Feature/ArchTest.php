<?php

use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

arch('MCP tools extend the Laravel MCP Tool base class')
    ->expect('App\Mcp\Tools')
    ->toExtend(Tool::class);

arch('MCP tools are declared read-only')
    ->expect('App\Mcp\Tools')
    ->toHaveAttribute(IsReadOnly::class);

arch('MCP tools are declared idempotent')
    ->expect('App\Mcp\Tools')
    ->toHaveAttribute(IsIdempotent::class);

arch('application code does not leave behind debug helpers')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'die'])
    ->not->toBeUsed();
