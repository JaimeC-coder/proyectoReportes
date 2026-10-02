<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resuelve rutas de imágenes para la vista previa (URL) o el PDF (ruta absoluta en disco).
 */
class DocumentAsset
{
    public static function src(?string $path, bool $pdf): ?string
    {
        if (! $path) {
            return null;
        }

        if ($pdf) {
            $absolute = Storage::disk('public')->path($path);

            return is_file($absolute) ? $absolute : null;
        }

        return '/storage/'.ltrim($path, '/');
    }

    /** Reemplaza las URLs /storage/... del HTML enriquecido por rutas de disco para DomPDF. */
    public static function html(?string $html, bool $pdf): string
    {
        $html = (string) $html;
        if (! $pdf || $html === '') {
            return $html;
        }

        return preg_replace_callback('#src="(?:https?://[^"/]+)?/storage/([^"]+)"#i', function ($m) {
            $absolute = Storage::disk('public')->path(urldecode($m[1]));

            return is_file($absolute) ? 'src="'.$absolute.'"' : 'src=""';
        }, $html);
    }
}
