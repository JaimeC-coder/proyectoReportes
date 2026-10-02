<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Guarda imágenes en el disco público reduciendo su tamaño para que
 * la vista previa y el PDF sean livianos.
 */
class ImageService
{
    public const MAX_SIDE = 1600;

    public function store(UploadedFile $file, string $directory = 'uploads'): string
    {
        $directory = trim($directory, '/').'/'.now()->format('Y/m');
        $name = Str::uuid()->toString();
        $optimized = $this->optimize($file->getRealPath(), $file->getMimeType());

        if ($optimized !== null) {
            $path = "{$directory}/{$name}.jpg";
            Storage::disk('public')->put($path, $optimized);

            return $path;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');

        return $file->storeAs($directory, "{$name}.{$extension}", 'public');
    }

    /** Devuelve el binario JPG optimizado o null si no es posible (sin GD, PNG con transparencia, etc.). */
    private function optimize(string $path, ?string $mime): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        [$width, $height] = @getimagesize($path) ?: [0, 0];
        if ($width === 0 || $height === 0) {
            return null;
        }

        // Los PNG (logos, firmas) conservan transparencia: no se convierten.
        if ($mime === 'image/png') {
            return null;
        }

        $image = @imagecreatefromstring(file_get_contents($path));
        if ($image === false) {
            return null;
        }

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $orientation = @exif_read_data($path)['Orientation'] ?? 1;
            $image = match ((int) $orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
            $width = imagesx($image);
            $height = imagesy($image);
        }

        $scale = min(1, self::MAX_SIDE / max($width, $height));
        if ($scale < 1) {
            $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        imagejpeg($image, null, 82);
        imagedestroy($image);

        return ob_get_clean() ?: null;
    }
}
