<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetRule;
use App\Mcp\Tools\SearchCard;
use App\Mcp\Tools\SearchCards;
use App\Mcp\Tools\SearchCardsAdvanced;
use App\Mcp\Tools\SearchRules;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\URL;
use Laravel\Mcp\Events\SessionInitialized;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Methods\Initialize;
use Laravel\Mcp\Server\ServerContext;
use Laravel\Mcp\Server\Transport\JsonRpcRequest;
use Laravel\Mcp\Server\Transport\JsonRpcResponse;

#[Name('mtg')]
#[Version('1.0.0')]
#[Instructions('Search Magic: The Gathering card data from the Scryfall database. Use search-card for finding a single card by exact name. Use search-cards for batch lookups of multiple cards by name. Use search-cards-advanced for complex multi-filter queries across all card attributes including colors, mana cost, type, oracle text, rarity, format legality, and more. Use search-rules to find official comprehensive rules about game mechanics, timing, interactions, layers, state-based actions, and other rules concepts. Use get-rule to fetch a specific rule by number, chapter, or section.')]
class MtgServer extends Server
{
    protected array $tools = [
        SearchCard::class,
        SearchCards::class,
        SearchCardsAdvanced::class,
        SearchRules::class,
        GetRule::class,
    ];

    /**
     * Inject server branding into the initialize response.
     *
     * laravel/mcp v0.6.7 does not expose `serverInfo.icons` yet, and the
     * base class instantiates Initialize with `new` (not via container),
     * so overriding this method is the narrowest available seam.
     */
    protected function handleInitializeMessage(JsonRpcRequest $request, ServerContext $context): void
    {
        $response = (new Initialize)->handle($request, $context);

        $payload = $response->toArray();
        $payload['result']['serverInfo']['icons'] = [[
            'src' => URL::asset('icon.jpeg'),
            'mimeType' => 'image/jpeg',
            'sizes' => ['any'],
        ]];

        $response = JsonRpcResponse::result($request->id, $payload['result']);

        $sessionId = $this->generateSessionId();

        Container::getInstance()->make('events')->dispatch(new SessionInitialized(
            sessionId: $sessionId,
            clientInfo: $request->params['clientInfo'] ?? null,
            protocolVersion: $request->params['protocolVersion'] ?? null,
            clientCapabilities: $request->params['capabilities'] ?? null,
        ));

        $this->transport->send($response->toJson(), $sessionId);
    }
}
