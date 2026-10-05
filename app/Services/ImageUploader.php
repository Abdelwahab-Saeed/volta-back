<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

/**
 * Every uploaded image is stored through here: converted to WebP and shrunk to fit MAX_DIMENSION,
 * so admins can upload large PNG/JPG files and the storefront still gets small ones.
 * GIF (may be animated) and SVG (vector) are stored unchanged.
 */
class ImageUploader
{
    public const MAX_DIMENSION = 1920;

    public const QUALITY = 80;

    private const KEEP_AS_IS = ['gif', 'svg', 'webp'];

    // GD holds the decoded image at ~4 bytes per pixel (a 24 MP photo is ~100 MB), more than PHP's default 128M.
    private const MEMORY_LIMIT = '512M';

    /**
     * Store the upload under $directory on $disk and return its relative path (what the image columns hold).
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        if (in_array(strtolower($file->guessExtension() ?? ''), ['gif', 'svg'], true)) {
            return $file->store($directory, $disk);
        }

        $path = trim($directory, '/').'/'.Str::random(40).'.webp';
        Storage::disk($disk)->put($path, $this->toWebp($file->getRealPath()));

        return $path;
    }

    /**
     * Convert a file already on $disk, writing "same/dir/same-name.webp" next to it (the original is kept).
     * Returns the new path, or null when there is nothing to convert: missing file, or already WebP/GIF/SVG.
     */
    public function convertFile(string $path, string $disk = 'public'): ?string
    {
        $storage = Storage::disk($disk);

        if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::KEEP_AS_IS, true) || ! $storage->exists($path)) {
            return null;
        }

        // Work on a local copy so this also works on remote disks.
        $source = tempnam(sys_get_temp_dir(), 'img');
        try {
            file_put_contents($source, $storage->get($path));
            $webp = $this->toWebp($source);
        } finally {
            @unlink($source);
        }

        $newPath = preg_replace('/\.[^.\/]*$/', '', $path).'.webp';
        $storage->put($newPath, $webp);

        return $newPath;
    }

    /**
     * Encode the image at $sourcePath as WebP (shrunk, never enlarged; EXIF rotation applied) and return the bytes.
     */
    private function toWebp(string $sourcePath): string
    {
        // The encoder picks the format from the extension, so write next to the temp file with ".webp" added.
        $base = tempnam(sys_get_temp_dir(), 'webp');
        $target = $base.'.webp';
        $this->raiseMemoryLimit();

        try {
            Image::load($sourcePath)
                ->fit(Fit::Max, self::MAX_DIMENSION, self::MAX_DIMENSION)
                ->quality(self::QUALITY)
                ->format('webp')
                ->save($target);

            return file_get_contents($target);
        } finally {
            @unlink($target);
            @unlink($base);
        }
    }

    private function raiseMemoryLimit(): void
    {
        $current = ini_get('memory_limit');

        if ($current !== '-1' && $this->bytes($current) < $this->bytes(self::MEMORY_LIMIT)) {
            ini_set('memory_limit', self::MEMORY_LIMIT);
        }
    }

    /** "128M" → bytes. */
    private function bytes(string $size): int
    {
        $value = (int) $size;

        return match (strtolower(substr(trim($size), -1))) {
            'g' => $value * 1024 ** 3,
            'm' => $value * 1024 ** 2,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
