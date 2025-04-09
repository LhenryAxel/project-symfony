<?php

namespace App\Enum;

enum TypeStat: int
{
    case Vue = 1;
    case Telechargement = 2;
    case RequeteUrl = 3;

    public static function fromString(string $name): ?int
    {
        return self::tryFromName($name)?->value;
    }

    private static function tryFromName(string $name): ?self
    {
        return match (strtolower($name)) {
            'vue' => self::Vue,
            'telechargement' => self::Telechargement,
            'requeteurl' => self::RequeteUrl,
            default => null,
        };
    }
}
