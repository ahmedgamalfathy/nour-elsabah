<?php

namespace App\Enums\Product;

enum LimitedQuantity: int{

    case UNLIMITED = 1;
    case LIMITED = 0;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
