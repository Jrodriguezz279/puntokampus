<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class QrService
{
    /**
     * Genera un SVG de código QR 100% estándar y escaneable.
     */
    public static function generarSvg(string $texto, int $tamano = 280): string
    {
        $options = new QROptions([
            'outputBase64'  => false,
            'scale'         => 8,
            'addQuietzone'  => true,
            'quietzoneSize' => 4,
        ]);

        return (new QRCode($options))->render($texto);
    }

    /**
     * Genera una imagen PNG binaria de código QR para máxima compatibilidad con apps móviles y navegadores.
     */
    public static function generarPng(string $texto, int $tamano = 280): string
    {
        try {
            $options = new QROptions([
                'outputBase64'    => false,
                'outputInterface' => QRGdImagePNG::class,
                'scale'           => 10,
                'addQuietzone'    => true,
                'quietzoneSize'   => 4,
            ]);

            return (new QRCode($options))->render($texto);
        } catch (\Throwable $e) {
            // Si la extensión GD no está presente o falla, devolver SVG
            return self::generarSvg($texto, $tamano);
        }
    }
}
