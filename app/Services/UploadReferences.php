<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Collects every file path that is still referenced somewhere in the database.
 *
 * The scan is schema driven on purpose. A hand written list of columns would be
 * smaller, but the day someone adds a new upload field and forgets to register
 * it here, the pruner would happily delete files that are still in use. Instead
 * we walk every text-ish column of every table and treat anything that points
 * inside the upload root as referenced, so a new column protects itself.
 */
class UploadReferences
{
    /**
     * The only folder tree this application owns on the public disk.
     */
    public const ROOT = 'invitations';

    /**
     * @return array<string, true> Referenced paths, keyed for fast lookup.
     */
    public function paths(): array
    {
        $referenced = [];

        foreach (Schema::getTableListing() as $listing) {
            $table = $this->unqualified($listing);
            $columns = $this->textColumns($table);

            if ($columns === []) {
                continue;
            }

            foreach (DB::table($table)->select($columns)->cursor() as $row) {
                foreach ((array) $row as $value) {
                    $this->collect($value, $referenced);
                }
            }
        }

        return $referenced;
    }

    private function unqualified(string $listing): string
    {
        $parts = explode('.', $listing);

        return (string) end($parts);
    }

    /**
     * Only columns that could hold a path are worth reading.
     *
     * @return list<string>
     */
    private function textColumns(string $table): array
    {
        $columns = [];

        foreach (Schema::getColumns($table) as $column) {
            $type = strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));

            if (str_contains($type, 'char') || str_contains($type, 'text') || str_contains($type, 'json')) {
                $columns[] = (string) $column['name'];
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, true>  $referenced
     */
    private function collect(mixed $value, array &$referenced): void
    {
        if (is_string($value)) {
            $this->collectString($value, $referenced);

            return;
        }

        if (is_array($value)) {
            foreach ($value as $nested) {
                $this->collect($nested, $referenced);
            }
        }
    }

    /**
     * @param  array<string, true>  $referenced
     */
    private function collectString(string $value, array &$referenced): void
    {
        // JSON columns store escaped slashes, so a value saved as
        // "invitations\/cover-videos/x.mp4" would never match a plain search.
        $value = str_replace('\\/', '/', $value);

        if (! str_contains($value, self::ROOT.'/')) {
            return;
        }

        // A column dedicated to a path holds exactly the path.
        if (str_starts_with($value, self::ROOT.'/')) {
            $referenced[trim($value)] = true;
        }

        // Anything else may still mention a path: a JSON blob, a prose field, a
        // future column nobody registered here. Pull out every path shaped run
        // of characters. Over-collecting only ever means keeping a file longer,
        // so a deliberately conservative character set is the safe direction.
        if (preg_match_all('#'.preg_quote(self::ROOT, '#').'/[A-Za-z0-9._/-]+#', $value, $matches) > 0) {
            foreach ($matches[0] as $match) {
                $referenced[$match] = true;
            }
        }
    }
}
