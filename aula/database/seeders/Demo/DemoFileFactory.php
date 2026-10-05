<?php

namespace Database\Seeders\Demo;

/**
 * Genera archivos mínimos pero REALES para los recursos de demostración:
 * su contenido pasa la detección por finfo que exige ResourceFileService,
 * así que no hace falta saltarse ninguna validación.
 */
final class DemoFileFactory
{
    /** PNG de 1x1 píxel, solo para cuando GD no está disponible. */
    private const PNG_RESPALDO = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    /**
     * PDF de una página con el título como texto. Las posiciones de la
     * tabla xref se calculan, así que los visores lo abren sin reconstruirlo.
     */
    public static function pdf(string $title, string $subtitle = 'Documento de demostración de la Biblioteca'): string
    {
        $contenido = "BT\n/F1 22 Tf\n28 TL\n72 760 Td\n";

        $lineas = explode("\n", wordwrap($title, 36, "\n"));

        foreach ($lineas as $i => $linea) {
            $contenido .= ($i > 0 ? "T*\n" : '').'('.self::textoPdf($linea).") Tj\n";
        }

        $contenido .= "ET\nBT\n/F1 11 Tf\n0.4 g\n72 ".(760 - 28 * count($lineas) - 12)." Td\n(".self::textoPdf($subtitle).") Tj\nET";

        $objetos = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            5 => '<< /Length '.strlen($contenido)." >>\nstream\n{$contenido}\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $posiciones = [];

        foreach ($objetos as $numero => $cuerpo) {
            $posiciones[$numero] = strlen($pdf);
            $pdf .= "{$numero} 0 obj\n{$cuerpo}\nendobj\n";
        }

        $inicioXref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objetos) + 1)."\n0000000000 65535 f \n";

        foreach ($posiciones as $posicion) {
            $pdf .= sprintf("%010d 00000 n \n", $posicion);
        }

        return $pdf."trailer\n<< /Size ".(count($objetos) + 1)." /Root 1 0 R >>\nstartxref\n{$inicioXref}\n%%EOF\n";
    }

    /**
     * Imagen PNG con un degradado propio de cada semilla (el mismo texto da
     * siempre la misma imagen), para distinguir las miniaturas a simple vista.
     */
    public static function png(string $seed, int $width = 640, int $height = 400): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return (string) base64_decode(self::PNG_RESPALDO);
        }

        $hash = md5($seed);
        // Canales entre 70 y 200: ni tan oscuros ni tan claros para un fondo.
        $canal = fn (int $i): int => 70 + intdiv(hexdec(substr($hash, $i * 2, 2)) * 130, 255);

        $imagen = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            $t = $y / max(1, $height - 1);
            $color = imagecolorallocate(
                $imagen,
                (int) ($canal(0) + ($canal(3) - $canal(0)) * $t),
                (int) ($canal(1) + ($canal(4) - $canal(1)) * $t),
                (int) ($canal(2) + ($canal(5) - $canal(2)) * $t),
            );
            imageline($imagen, 0, $y, $width, $y, $color);
        }

        $claro = imagecolorallocatealpha($imagen, 255, 255, 255, 96);
        imagefilledellipse($imagen, intdiv($width, 2), intdiv($height, 2), intdiv($height, 2), intdiv($height, 2), $claro);

        ob_start();
        imagepng($imagen);

        return (string) ob_get_clean();
    }

    /**
     * WAV PCM de 8 bits, mono y en silencio (0x80 es el punto medio).
     */
    public static function wav(float $seconds = 1.0, int $sampleRate = 8000): string
    {
        $muestras = max(1, (int) ($seconds * $sampleRate));

        return 'RIFF'.pack('V', 36 + $muestras).'WAVE'
            .'fmt '.pack('VvvVVvv', 16, 1, 1, $sampleRate, $sampleRate, 1, 8)
            .'data'.pack('V', $muestras).str_repeat("\x80", $muestras);
    }

    /**
     * Texto apto para una cadena PDF: Windows-1252 (lo que entiende
     * WinAnsiEncoding) y con \ ( ) escapados.
     */
    private static function textoPdf(string $texto): string
    {
        $texto = mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
    }
}
