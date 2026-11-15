<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImagenService
{
    /**
     * Guarda y procesa una imagen reduciéndola a un máximo de 1080px por lado si es necesario.
     * Retorna la URL pública accesible.
     */
    public function procesarYGuardar(UploadedFile $archivo, string $directorio = 'fotos'): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreArchivo = Str::uuid() . '.' . ($extension === 'png' ? 'png' : 'jpg');
        $directorioLimpio = trim($directorio, '/');
        $rutaRelativa = "{$directorioLimpio}/{$nombreArchivo}";

        $disk = Storage::disk('public');

        // Obtener dimensiones originales
        $info = @getimagesize($archivo->getRealPath());
        if (!$info) {
            // Si no se puede leer como imagen binaria directa, guardar directamente en disco public
            $disk->putFileAs($directorioLimpio, $archivo, $nombreArchivo);
            return asset("storage/{$rutaRelativa}");
        }

        [$anchoOriginal, $altoOriginal, $tipo] = $info;

        // Si ya mide <= 1080px por ambos lados, guardar directamente
        if ($anchoOriginal <= 1080 && $altoOriginal <= 1080) {
            $disk->putFileAs($directorioLimpio, $archivo, $nombreArchivo);
            return asset("storage/{$rutaRelativa}");
        }

        // Calcular nuevas dimensiones manteniendo aspecto
        if ($anchoOriginal > $altoOriginal) {
            $nuevoAncho = 1080;
            $nuevoAlto = (int) round(($altoOriginal / $anchoOriginal) * 1080);
        } else {
            $nuevoAlto = 1080;
            $nuevoAncho = (int) round(($anchoOriginal / $altoOriginal) * 1080);
        }

        // Crear imagen desde origen
        $origen = match ($tipo) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($archivo->getRealPath()),
            IMAGETYPE_PNG => imagecreatefrompng($archivo->getRealPath()),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($archivo->getRealPath()) : null,
            default => null,
        };

        if (!$origen) {
            $disk->putFileAs($directorioLimpio, $archivo, $nombreArchivo);
            return asset("storage/{$rutaRelativa}");
        }

        // Redimensionar
        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        // Preservar transparencia para PNG
        if ($tipo === IMAGETYPE_PNG) {
            imagealphablending($destino, false);
            imagesavealpha($destino, true);
            $transparente = imagecolorallocatealpha($destino, 255, 255, 255, 127);
            imagefilledrectangle($destino, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);
        }

        imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal);

        // Guardar temporalmente y volcar a Storage public
        $tempPath = tempnam(sys_get_temp_dir(), 'pk_img_');
        if ($tipo === IMAGETYPE_PNG) {
            imagepng($destino, $tempPath, 8);
        } else {
            imagejpeg($destino, $tempPath, 85);
        }

        imagedestroy($origen);
        imagedestroy($destino);

        $disk->put($rutaRelativa, file_get_contents($tempPath));
        @unlink($tempPath);

        return asset("storage/{$rutaRelativa}");
    }
}

