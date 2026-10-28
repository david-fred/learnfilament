<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatusEnum: string implements HasLabel, HasColor
{
    case IN_STOCK = 'in stock';
    case OUT_OF_STOCK = 'out of stock';
    case DISCONTINUED = 'discontinued';
    case COMING_SOON = 'coming soon';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::IN_STOCK => 'success',
            self::OUT_OF_STOCK => 'danger',
            self::DISCONTINUED => 'warning',
            self::COMING_SOON => 'primary',
        };
    }
}
