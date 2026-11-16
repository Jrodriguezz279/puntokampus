<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImagenService
{
    /**
     * Guarda y procesa una imagen. Si Cloudinary está configurado, la sube a Cloudinary
     * y retorna la URL HTTPS permanente. De lo contrario, la guarda en el disco local 'public'.
     */
    public function procesarYGuardar(UploadedFile $archivo, string $directorio = 'fotos'): string
    {
        $cloudName = config('services.cloudinary.cloud_name') ?: env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = config('services.cloudinary.api_key') ?: env('CLOUDINARY_API_KEY');
        $apiSecret = config('services.cloudinary.api_secret') ?: env('CLOUDINARY_API_SECRET');

        if (!empty($cloudName) && !empty($apiKey) && !empty($apiSecret)) {
            $urlCloudinary = $this->subirACloudinary($archivo, $directorio, $cloudName, $apiKey, $apiSecret);
            if ($urlCloudinary) {
                return $urlCloudinary;
            }
        }

        // Fallback a almacenamiento local 'public' si Cloudinary no está configurado o falla
        return $this->guardarLocalmente($archivo, $directorio);
    }

    /**
     * Sube un archivo a Cloudinary usando su API REST oficial firmada.
     */
    protected function subirACloudinary(
        UploadedFile $archivo,
        string $directorio,
        string $cloudName,
        string $apiKey,
        string $apiSecret
    ): ?string {
        try {
            $timestamp = time();
            $folder = 'puntokampus/' . trim($directorio, '/');

            // Generar firma SHA-1 requerida por Cloudinary:
            // Parámetros ordenados alfabéticamente concatenados con el api_secret
            $params = [
                'folder' => $folder,
                'timestamp' => $timestamp,
            ];
            ksort($params);

            $stringToSign = '';
            foreach ($params as $k => $v) {
                $stringToSign .= "{$k}={$v}&";
            }
            $stringToSign = rtrim($stringToSign, '&') . $apiSecret;
            $signature = sha1($stringToSign);

            $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

            $response = Http::timeout(30)
                ->attach('file', file_get_contents($archivo->getRealPath()), $archivo->getClientOriginalName())
                ->post($endpoint, [
                    'api_key' => $apiKey,
                    'timestamp' => $timestamp,
                    'folder' => $folder,
                    'signature' => $signature,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['secure_url'])) {
                    return $json['secure_url'];
                }
            }

            Log::error('Error al subir imagen a Cloudinary: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Excepción al conectar con Cloudinary: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Almacenamiento local con redimensionamiento a 1080px (respaldo offline).
     */
    protected function guardarLocalmente(UploadedFile $archivo, string $directorio): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreArchivo = Str::uuid() . '.' . ($extension === 'png' ? 'png' : 'jpg');
        $directorioLimpio = trim($directorio, '/');
        $rutaRelativa = "{$directorioLimpio}/{$nombreArchivo}";

        $disk = Storage::disk('public');

        // Obtener dimensiones originales
        $info = @getimagesize($archivo->getRealPath());
        if (!$info) {
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

        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        if ($tipo === IMAGETYPE_PNG) {
            imagealphablending($destino, false);
            imagesavealpha($destino, true);
            $transparente = imagecolorallocatealpha($destino, 255, 255, 255, 127);
            imagefilledrectangle($destino, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);
        }

        imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal);

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
