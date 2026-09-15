<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaceholderImage
{
    private const PALETTE = [
        [37, 99, 235],
        [5, 150, 105],
        [217, 119, 6],
        [219, 39, 119],
        [124, 58, 237],
        [8, 145, 178],
        [220, 38, 38],
        [71, 85, 105],
    ];

    private const FONTS = [
        'C:/Windows/Fonts/arialbd.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
    ];

    /**
     * Generate a labelled JPEG on the public disk and return its relative path,
     * matching how the admin controllers store uploads.
     */
    public static function make(string $directory, string $label, int $width = 800, int $height = 800): string
    {
        $image = imagecreatetruecolor($width, $height);
        [$r, $g, $b] = self::PALETTE[crc32($label) % count(self::PALETTE)];
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
        $white = imagecolorallocate($image, 255, 255, 255);

        $font = collect(self::FONTS)->first(fn (string $path) => is_file($path));

        if ($font && function_exists('imagettftext')) {
            self::drawCenteredText($image, $label, $font, $white, $width, $height);
        } else {
            imagestring($image, 5, 20, intdiv($height, 2) - 8, $label, $white);
        }

        ob_start();
        imagejpeg($image, null, 85);
        $contents = ob_get_clean();

        $path = trim($directory, '/').'/'.Str::slug(Str::limit($label, 40, '')).'-'.Str::lower(Str::random(8)).'.jpg';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    private static function drawCenteredText(\GdImage $image, string $label, string $font, int $color, int $width, int $height): void
    {
        $size = intdiv(min($width, $height), 18);
        $lines = explode("\n", wordwrap($label, 18));
        $lineHeight = (int) ($size * 1.7);
        $y = intdiv($height - $lineHeight * count($lines), 2) + $size;

        foreach ($lines as $line) {
            $box = imagettfbbox($size, 0, $font, $line);
            $x = intdiv($width - ($box[2] - $box[0]), 2);
            imagettftext($image, $size, 0, $x, $y, $color, $font, $line);
            $y += $lineHeight;
        }
    }
}
