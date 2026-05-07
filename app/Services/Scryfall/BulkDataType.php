<?php

namespace App\Services\Scryfall;

enum BulkDataType: string
{
    case DefaultCards = 'default_cards';
    case Rulings = 'rulings';
}
