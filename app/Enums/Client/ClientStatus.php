<?php

namespace App\Enums\Client;

enum ClientStatus: int{

    case INACTIVE = 0;
    case ACTIVE = 1;
    case SUSPENDED =2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
