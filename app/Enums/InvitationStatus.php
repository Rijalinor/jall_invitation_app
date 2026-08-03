<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case DRAFT = 'draft';
    case PREVIEW = 'preview';
    case PUBLISHED = 'published';
    case EXPIRED = 'expired';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PREVIEW => 'Preview',
            self::PUBLISHED => 'Diterbitkan',
            self::EXPIRED => 'Kadaluarsa',
            self::ARCHIVED => 'Diarsipkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PREVIEW => 'warning',
            self::PUBLISHED => 'success',
            self::EXPIRED => 'danger',
            self::ARCHIVED => 'secondary',
        };
    }
}
