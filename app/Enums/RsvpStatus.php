<?php

namespace App\Enums;

enum RsvpStatus: string
{
    case ATTENDING = 'attending';
    case NOT_ATTENDING = 'not_attending';
    case TENTATIVE = 'tentative';

    public function label(): string
    {
        return match ($this) {
            self::ATTENDING => 'Hadir',
            self::NOT_ATTENDING => 'Tidak Hadir',
            self::TENTATIVE => 'Ragu-ragu / Belum Pasti',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ATTENDING => 'success',
            self::NOT_ATTENDING => 'danger',
            self::TENTATIVE => 'warning',
        };
    }
}
