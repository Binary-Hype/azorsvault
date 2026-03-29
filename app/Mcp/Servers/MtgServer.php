<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetRule;
use App\Mcp\Tools\SearchCard;
use App\Mcp\Tools\SearchCards;
use App\Mcp\Tools\SearchCardsAdvanced;
use App\Mcp\Tools\SearchRules;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

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
}
