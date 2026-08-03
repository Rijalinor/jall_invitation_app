<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Str;

class GuestCsvImporter
{
    /** @param resource $stream */
    public function import(Invitation $invitation, $stream): int
    {
        $headers = fgetcsv($stream);
        $headers = $headers ? array_map(fn ($value) => Str::lower(trim($value, " \t\n\r\0\x0B\xEF\xBB\xBF")), $headers) : [];

        if (! in_array('name', $headers, true)) {
            return -1;
        }

        $count = 0;
        while (($row = fgetcsv($stream)) !== false) {
            $values = array_pad($row, count($headers), null);
            $item = array_combine($headers, array_slice($values, 0, count($headers)));
            $name = Str::squish(strip_tags((string) ($item['name'] ?? '')));

            if ($name === '') {
                continue;
            }

            $invitation->guests()->create([
                'display_name' => Str::limit($name, 255, ''),
                'group' => Str::limit(Str::squish(strip_tags((string) ($item['group'] ?? ''))), 255, ''),
                'phone' => Str::limit(preg_replace('/[^0-9+]/', '', (string) ($item['phone'] ?? '')), 50, ''),
                'invitation_limit' => min(20, max(1, (int) ($item['invitation_limit'] ?? 2))),
            ]);
            $count++;
        }

        return $count;
    }
}
