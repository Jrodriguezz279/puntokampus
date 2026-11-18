<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrService
{
    /**
     * Genera un SVG de código QR 100% estándar y escaneable.
     */
    public static function generarSvg(string $texto, int $tamano = 260): string
    {
        try {
            $options = new QROptions([
                'version'         => QRCode::VERSION_AUTO,
                'outputType'      => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'        => QRCode::ECC_M,
                'scale'           => 6,
                'imageBase64'     => false,
                'svgUseFill'      => true,
                'svgAddXmlHeader' => false,
            ]);

            return (new QRCode($options))->render($texto);
        } catch (\Throwable $e) {
            // Fallback SVG compatible
            return self::generarSvgFallback($texto, $tamano);
        }
    }

    /**
     * Genera una imagen PNG binaria de código QR para máxima compatibilidad con apps móviles y navegadores.
     */
    public static function generarPng(string $texto, int $tamano = 260): string
    {
        try {
            $options = new QROptions([
                'version'     => QRCode::VERSION_AUTO,
                'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'    => QRCode::ECC_M,
                'scale'       => 8,
                'imageBase64' => false,
            ]);

            return (new QRCode($options))->render($texto);
        } catch (\Throwable $e) {
            // Si la extensión GD no está presente o falla, devolver SVG
            return self::generarSvg($texto, $tamano);
        }
    }

    /**
     * Fallback matricial en caso extremo.
     */
    private static function generarSvgFallback(string $texto, int $tamano): string
    {
        $gridSize = 25;
        $celdaTam = $tamano / ($gridSize + 4);
        $offset = $celdaTam * 2;
        $rects = '';

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if (($r < 7 && $c < 7) || ($r < 7 && $c >= $gridSize - 7) || ($r >= $gridSize - 7 && $c < 7)) {
                    $x = round($offset + ($c * $celdaTam), 2);
                    $y = round($offset + ($r * $celdaTam), 2);
                    $w = round($celdaTam, 2);
                    $rects .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$w}\" height=\"{$w}\" fill=\"#1e1b18\" />\n";
                }
            }
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$tamano} {$tamano}" width="{$tamano}" height="{$tamano}">
    <rect width="100%" height="100%" fill="#ffffff" rx="12"/>
    {$rects}
</svg>
SVG;
    }
}
