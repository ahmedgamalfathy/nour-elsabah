<?php
namespace App\Enums\Order;
enum DiscountType:int{ 
    case FIXCED = 1 ;
    case NO_DISCOUNT = 0;
    case PERCENTAGE = 2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
