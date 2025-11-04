<?php

namespace App\Enums\User;

enum UserType: int{

    case CLIENT = 0;
    case ADMIN = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
