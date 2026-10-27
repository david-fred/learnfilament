<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case IN_STOCK = 'in_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case DISCONTINUED = 'discontinued';
    case COMING_SOON = 'coming_soon';

    public function getLabel(): string
    {
        return match ($this) {
            self::IN_STOCK => 'In stock',
            self::OUT_OF_STOCK => 'Out of stock',
            self::DISCONTINUED => 'Discontinued',
            self::COMING_SOON => 'Coming soon',
        };
    }
}
