<?php

namespace App\Services;

/**
 * Builds wa.me links from the contact number configured in config/invitation.php.
 *
 * Indonesian numbers are commonly written as 08xx, +62 8xx, or 62-8xx. wa.me
 * only accepts bare international digits, so every entry point goes through
 * this class instead of hand-rolling string replacement.
 */
class WhatsAppLink
{
    /**
     * Shortest plausible international number, used to reject empty or partial
     * configuration such as "0" or "62".
     */
    private const MIN_DIGITS = 8;

    public function to(?string $message = null, ?string $number = null): ?string
    {
        $number ??= config('invitation.whatsapp');

        $normalized = $this->normalize(is_string($number) ? $number : null);

        if ($normalized === null) {
            return null;
        }

        $link = 'https://wa.me/'.$normalized;

        if ($message !== null && $message !== '') {
            $link .= '?text='.rawurlencode($message);
        }

        return $link;
    }

    /**
     * Normalise a phone number to bare international digits, or null when the
     * value cannot plausibly be a contactable number.
     */
    public function normalize(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number) ?? '';

        if ($digits === '') {
            return null;
        }

        $digits = match (true) {
            str_starts_with($digits, '62') => '62'.ltrim(substr($digits, 2), '0'),
            str_starts_with($digits, '0') => '62'.ltrim($digits, '0'),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };

        return strlen($digits) >= self::MIN_DIGITS ? $digits : null;
    }
}
