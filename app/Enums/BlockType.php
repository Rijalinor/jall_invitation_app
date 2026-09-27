<?php

namespace App\Enums;

enum BlockType: string
{
    case SECTION = 'section';
    case TEXT = 'text';
    case QUOTE = 'quote';
    case IMAGE = 'image';
    case NOTE = 'note';

    public function label(): string
    {
        return match ($this) {
            self::SECTION => 'Judul Seksi Baru',
            self::TEXT => 'Teks',
            self::QUOTE => 'Kutipan',
            self::IMAGE => 'Foto',
            self::NOTE => 'Catatan',
        };
    }

    /**
     * A section heading opens a new band; the content types land inside the band
     * that is currently open, which is how one flat, reorderable list still
     * produces several separate sections.
     */
    public function opensSection(): bool
    {
        return $this === self::SECTION;
    }
}
