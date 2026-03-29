<?php

use App\Mcp\Servers\MtgServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('mtg', MtgServer::class);

Mcp::web('/mcp/mtg', MtgServer::class)
    ->middleware('throttle:300,1');
