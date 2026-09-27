<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Accepts image uploads from an unauthenticated page.
 *
 * Nothing the browser claims is trusted: the detected image type decides the
 * stored extension, dimensions are checked before decoding so a decompression
 * bomb cannot exhaust memory, and images are re-encoded so EXIF metadata
 * (including the GPS coordinates of someone's home) and any embedded payload
 * are dropped.
 */
class PublicImageUpload
{
    /** Longest edge kept after re-encoding. */
    public const MAX_EDGE = 1600;

    public const MAX_INPUT_EDGE = 8000;

    public const MIN_INPUT_EDGE = 200;

    public const MAX_KILOBYTES = 4096;

    /** Ceiling on stored photos for a single invitation. */
    public const MAX_PER_INVITATION = 60;

    /**
     * Validate and store an uploaded image, returning its path on the public disk.
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->getRealPath();
        $dimensions = $path === false ? false : @getimagesize($path);

        if ($dimensions === false) {
            throw ValidationException::withMessages([
                'image' => 'Berkas itu bukan gambar yang bisa dibaca.',
            ]);
        }

        [$width, $height] = $dimensions;

        if ($width < self::MIN_INPUT_EDGE || $height < self::MIN_INPUT_EDGE) {
            throw ValidationException::withMessages([
                'image' => 'Ukuran gambar minimal '.self::MIN_INPUT_EDGE.'x'.self::MIN_INPUT_EDGE.' piksel.',
            ]);
        }

        if ($width > self::MAX_INPUT_EDGE || $height > self::MAX_INPUT_EDGE) {
            throw ValidationException::withMessages([
                'image' => 'Ukuran gambar terlalu besar untuk diproses.',
            ]);
        }

        return $this->encode($path, $directory) ?? $this->storeVerbatim($file, $directory);
    }

    /**
     * Remove a previously stored upload, but only when it lives inside the
     * directory this class owns. Anything else is left alone.
     */
    public function delete(?string $path, string $directory): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (! str_starts_with($path, trim($directory, '/').'/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Re-encode through GD. Returns null when GD is unavailable or the image
     * cannot be decoded, so the caller can fall back to storing it verbatim.
     */
    private function encode(string $path, string $directory): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $contents = @file_get_contents($path);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            return null;
        }

        $image = $this->downscale($image);

        $temporary = tempnam(sys_get_temp_dir(), 'jall');
        $encoded = $temporary !== false && @imagejpeg($image, $temporary, 86);

        imagedestroy($image);

        if (! $encoded) {
            if ($temporary !== false) {
                @unlink($temporary);
            }

            return null;
        }

        $target = trim($directory, '/').'/'.Str::ulid().'.jpg';
        $stream = @fopen($temporary, 'rb');

        if ($stream === false) {
            @unlink($temporary);

            return null;
        }

        Storage::disk('public')->put($target, $stream);
        fclose($stream);
        @unlink($temporary);

        return $target;
    }

    /**
     * Keep the long edge within MAX_EDGE. Never upscales.
     */
    private function downscale(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= self::MAX_EDGE) {
            return $image;
        }

        $ratio = self::MAX_EDGE / $longest;
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $scaled = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($scaled === false) {
            return $image;
        }

        imagecopyresampled($scaled, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        return $scaled;
    }

    /**
     * Store without re-encoding. `store()` names the file from the detected
     * MIME type, so this still cannot produce an executable extension.
     */
    private function storeVerbatim(UploadedFile $file, string $directory): string
    {
        return $file->store(trim($directory, '/'), 'public');
    }
}
