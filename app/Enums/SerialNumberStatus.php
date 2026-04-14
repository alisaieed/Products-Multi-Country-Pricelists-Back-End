<?php

namespace App\Enums;

enum SerialNumberStatus: string
{
    case available = 'available';
    case allocated = 'allocated';
    case sold = 'sold';
    case scrapped = 'scrapped';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}
