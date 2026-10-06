<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * Human labels for event timezones.
 *
 * The raw IANA identifier ("Asia/Makassar") is correct for storage and for
 * calendar files, but it reads badly on an invitation. Indonesian zones get
 * their familiar short name; anything else falls back to a UTC offset.
 */
final class TimezoneLabel
{
    /**
     * @var array<string, string>
     */
    private const KNOWN = [
        'asia/jakarta' => 'WIB',
        'asia/pontianak' => 'WIB',
        'asia/makassar' => 'WITA',
        'asia/ujung_pandang' => 'WITA',
        'asia/jayapura' => 'WIT',
    ];

    public static function for(?string $timezone): string
    {
        $timezone = trim((string) $timezone);

        if ($timezone === '') {
            return '';
        }

        $known = self::KNOWN[strtolower($timezone)] ?? null;

        if ($known !== null) {
            return $known;
        }

        try {
            $zone = new DateTimeZone($timezone);
            $offset = $zone->getOffset(new DateTimeImmutable('now', $zone));
        } catch (Throwable) {
            // An unrecognised zone is shown as-is rather than breaking the page.
            return $timezone;
        }

        $absolute = abs($offset);
        $hours = intdiv($absolute, 3600);
        $minutes = intdiv($absolute % 3600, 60);

        return 'UTC'.($offset < 0 ? '-' : '+').$hours
            .($minutes > 0 ? ':'.str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) : '');
    }
}
