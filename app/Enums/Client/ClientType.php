<?php
namespace App\Enums\Client;

enum ClientType: int{

case VISITOR = 0;
case CLIENT = 1;

public static function values(): array
{
    return array_column(self::cases(), 'value');
}
}
