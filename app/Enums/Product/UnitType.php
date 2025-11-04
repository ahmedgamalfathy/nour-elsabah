<?php

namespace App\Enums\Product;

enum UnitType: int{

    case KILOGRAM = 0;
    case GRAM = 1;
    case UNIT = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
