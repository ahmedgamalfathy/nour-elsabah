<?php
namespace App\Enums;
enum ActionStatus:string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case NO_ACTION = '';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
