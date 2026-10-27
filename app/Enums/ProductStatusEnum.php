<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatusEnum: string implements HasLabel, HasColor
{
    case IN_STOCK = 'in_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case DISCONTINUED = 'discontinued';
    case COMING_SOON = 'coming_soon';

    public function getLabel(): string
    {
        return  $this->value;
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
