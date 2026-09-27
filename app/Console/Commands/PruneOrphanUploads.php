<?php

namespace App\Console\Commands;

use App\Services\UploadReferences;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Removes uploaded files that no database record points at any more.
 *
 * Deleting files the moment a record is deleted would be more immediate, but it
 * is not recoverable: a failed save rolls the database back and leaves the file
 * gone. An orphan only wastes space, while a missing file breaks a live
 * invitation. So files are swept later, and only when nothing references them.
 */
class PruneOrphanUploads extends Command
{
    protected $signature = 'jall:prune-orphans
        {--days=1 : Hanya pertimbangkan file yang terakhir diubah lebih dari sekian hari}
        {--disk=public : Disk filesystem yang disapu}
        {--force : Benar-benar hapus, tanpa ini hanya menampilkan daftar}';

    protected $description = 'Buang file upload yang sudah tidak dirujuk catatan database mana pun';

    public function handle(UploadReferences $references): int
    {
        $diskName = (string) $this->option('disk');
        $disk = Storage::disk($diskName);

        if (! $disk->directoryExists(UploadReferences::ROOT)) {
            $this->info('Folder '.UploadReferences::ROOT.' tidak ada di disk "'.$diskName.'". Tidak ada yang perlu disapu.');

            return self::SUCCESS;
        }

        $files = $disk->allFiles(UploadReferences::ROOT);

        if ($files === []) {
            $this->info('Tidak ada file di '.UploadReferences::ROOT.'.');

            return self::SUCCESS;
        }

        $referenced = $references->paths();

        // Files exist but nothing references any of them, which means the
        // reference scan is broken, not that the storage is full of junk.
        if ($referenced === []) {
            $this->error('Tidak ada rujukan yang ditemukan padahal ada '.count($files).' file. Dibatalkan demi keamanan.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays(max(0, (int) $this->option('days')))->getTimestamp();
        $orphans = [];
        $bytes = 0;
        $young = 0;

        foreach ($files as $file) {
            if (isset($referenced[$file])) {
                continue;
            }

            // A file that was just uploaded may not be saved into the database
            // yet, so give everything a grace period.
            if ($disk->lastModified($file) > $cutoff) {
                $young++;

                continue;
            }

            $size = $disk->size($file);
            $orphans[] = ['file' => $file, 'size' => $size];
            $bytes += $size;
        }

        $this->line('File diperiksa : '.count($files));
        $this->line('Masih dipakai  : '.(count($files) - count($orphans) - $young));
        $this->line('Terlalu baru   : '.$young);
        $this->line('Yatim          : '.count($orphans));

        if ($orphans === []) {
            $this->info('Bersih, tidak ada yang perlu dihapus.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->table(
            ['Ukuran', 'File'],
            array_map(fn (array $orphan): array => [$this->formatBytes($orphan['size']), $orphan['file']], $orphans),
        );

        if (! $this->option('force')) {
            $this->warn('Mode lihat saja: '.count($orphans).' file ('.$this->formatBytes($bytes).') siap dibuang. Jalankan ulang dengan --force untuk menghapus.');

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($orphans as $orphan) {
            if ($disk->delete($orphan['file'])) {
                $deleted++;
            }
        }

        Log::info('jall:prune-orphans dijalankan', [
            'disk' => $diskName,
            'deleted' => $deleted,
            'bytes' => $bytes,
            'files' => array_column($orphans, 'file'),
        ]);

        $this->info('Dihapus: '.$deleted.' file, '.$this->formatBytes($bytes).' dibebaskan.');

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 2).' MB'
            : number_format($bytes / 1024, 1).' KB';
    }
}
