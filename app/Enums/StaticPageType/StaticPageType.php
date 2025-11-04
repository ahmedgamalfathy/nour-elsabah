<?php
namespace App\Enums\StaticPageType;

enum StaticPageType :string  {// terms, privacy, cookies
    case TERMS = 'terms';
    case PRIVACY = 'privacy';
    case COOKIES = 'cookies';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public static function isValid( string $self):bool
    {
        return in_array($self , self::values());
    }
}
