<?php

namespace App\Enums;

enum EventType: string
{
    case WEDDING = 'wedding';
    case ENGAGEMENT = 'engagement';
    case BIRTHDAY = 'birthday';
    case AQIQAH = 'aqiqah';
    case GRADUATION = 'graduation';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::WEDDING => 'Pernikahan',
            self::ENGAGEMENT => 'Lamaran / Tunangan',
            self::BIRTHDAY => 'Ulang Tahun',
            self::AQIQAH => 'Aqiqah',
            self::GRADUATION => 'Wisuda',
            self::GENERAL => 'Acara Umum',
        };
    }
}
