<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Los tokens de color de la Biblioteca se leen tal cual del CSS (hex plano)
 * y se comprueba el contraste WCAG en los dos temas.
 */
class BibliotecaTokensContrastTest extends TestCase
{
    private const OBLIGATORIOS = [
        'background', 'surface', 'surface-secondary', 'text', 'text-secondary',
        'border', 'primary', 'primary-contrast', 'success', 'warning', 'danger',
    ];

    /** @return array<string, array{string}> */
    public static function temas(): array
    {
        return [
            'claro' => ['light'],
            'oscuro' => ['dark'],
            'oscuro del sistema' => ['system'],
        ];
    }

    /**
     * Bloque de declaraciones de cada tema dentro de biblioteca.css.
     */
    private static function bloque(string $tema): string
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/biblioteca.css');
        self::assertIsString($css);

        $patron = match ($tema) {
            'light' => '/^:root\s*\{([^}]*)\}/m',
            'dark' => '/^:root\[data-theme="dark"\]\s*\{([^}]*)\}/m',
            'system' => '/@media \(prefers-color-scheme: dark\)\s*\{\s*:root\[data-theme="system"\]\s*\{([^}]*)\}/s',
        };

        self::assertSame(1, preg_match($patron, $css, $m), "No se encontró el bloque del tema {$tema}.");

        return $m[1];
    }

    /** @return array<string, string> token (sin prefijo) => hex en mayúsculas */
    private static function tokens(string $tema): array
    {
        preg_match_all('/--bib-([a-z-]+):\s*(#[0-9A-Fa-f]{6})\s*;/', self::bloque($tema), $m, PREG_SET_ORDER);

        $tokens = [];
        foreach ($m as $par) {
            $tokens[$par[1]] = strtoupper($par[2]);
        }

        return $tokens;
    }

    /** Luminancia relativa WCAG 2.x de un color #RRGGBB. */
    private static function luminancia(string $hex): float
    {
        $canales = array_map(function (string $par): float {
            $c = hexdec($par) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        return 0.2126 * $canales[0] + 0.7152 * $canales[1] + 0.0722 * $canales[2];
    }

    private static function contraste(string $a, string $b): float
    {
        $la = self::luminancia($a);
        $lb = self::luminancia($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /** Mezcla en sRGB como color-mix(in srgb, $a $porcentaje%, $b). */
    private static function mezclar(string $a, string $b, float $porcentaje): string
    {
        $pa = array_map('hexdec', str_split(ltrim($a, '#'), 2));
        $pb = array_map('hexdec', str_split(ltrim($b, '#'), 2));

        $mezcla = array_map(
            fn (int $x, int $y): string => sprintf('%02X', (int) round($x * $porcentaje / 100 + $y * (100 - $porcentaje) / 100)),
            $pa,
            $pb,
        );

        return '#'.implode('', $mezcla);
    }

    private function assertContraste(float $minimo, string $primero, string $segundo, string $detalle): void
    {
        $ratio = self::contraste($primero, $segundo);

        $this->assertGreaterThanOrEqual($minimo, $ratio, sprintf('%s: %.2f:1 (mínimo %.1f:1)', $detalle, $ratio, $minimo));
    }

    public function test_el_ayudante_calcula_21_a_1_para_negro_sobre_blanco(): void
    {
        $this->assertEqualsWithDelta(21.0, self::contraste('#000000', '#FFFFFF'), 0.001);
        $this->assertEqualsWithDelta(1.0, self::contraste('#336699', '#336699'), 0.001);
    }

    #[DataProvider('temas')]
    public function test_cada_tema_define_todos_los_tokens_obligatorios_como_hex(string $tema): void
    {
        $this->assertEqualsCanonicalizing(self::OBLIGATORIOS, array_keys(self::tokens($tema)), "Faltan o sobran tokens en el tema {$tema}.");
    }

    #[DataProvider('temas')]
    public function test_el_texto_cumple_4_5_a_1_sobre_los_tres_fondos(string $tema): void
    {
        $t = self::tokens($tema);

        foreach (['text', 'text-secondary'] as $texto) {
            foreach (['background', 'surface', 'surface-secondary'] as $fondo) {
                $this->assertContraste(4.5, $t[$texto], $t[$fondo], "{$tema}: {$texto} sobre {$fondo}");
            }
        }
    }

    #[DataProvider('temas')]
    public function test_el_texto_del_boton_primario_cumple_4_5_a_1(string $tema): void
    {
        $t = self::tokens($tema);

        $this->assertContraste(4.5, $t['primary-contrast'], $t['primary'], "{$tema}: primary-contrast sobre primary");
    }

    #[DataProvider('temas')]
    public function test_el_primario_como_texto_cumple_4_5_a_1_sobre_superficie_y_fondo(string $tema): void
    {
        $t = self::tokens($tema);

        foreach (['surface', 'background'] as $fondo) {
            $this->assertContraste(4.5, $t['primary'], $t[$fondo], "{$tema}: primary sobre {$fondo}");
        }
    }

    #[DataProvider('temas')]
    public function test_los_colores_de_estado_como_texto_cumplen_4_5_a_1_sobre_la_superficie(string $tema): void
    {
        $t = self::tokens($tema);

        foreach (['success', 'warning', 'danger'] as $estado) {
            $this->assertContraste(4.5, $t[$estado], $t['surface'], "{$tema}: {$estado} sobre surface");
        }
    }

    #[DataProvider('temas')]
    public function test_el_borde_se_distingue_de_la_superficie_con_1_3_a_1(string $tema): void
    {
        $t = self::tokens($tema);

        $this->assertContraste(1.3, $t['border'], $t['surface'], "{$tema}: border contra surface");
    }

    #[DataProvider('temas')]
    public function test_el_primario_como_texto_cumple_4_5_a_1_sobre_su_tono_suave(string $tema): void
    {
        // El tono suave (fondo del enlace activo) se deriva con color-mix.
        $this->assertSame(1, preg_match(
            '/--bib-primary-soft:\s*color-mix\(in srgb, var\(--bib-primary\) (\d+)%, var\(--bib-surface\)\)/',
            self::bloque($tema),
            $m,
        ), "{$tema}: falta --bib-primary-soft derivado con color-mix.");

        $t = self::tokens($tema);
        $suave = self::mezclar($t['primary'], $t['surface'], (float) $m[1]);

        $this->assertContraste(4.5, $t['primary'], $suave, "{$tema}: primary sobre primary-soft");
        $this->assertContraste(4.5, $t['text'], $suave, "{$tema}: text sobre primary-soft");
    }

    #[DataProvider('temas')]
    public function test_el_borde_de_los_campos_cumple_3_a_1_sobre_la_superficie(string $tema): void
    {
        // Los campos de formulario necesitan un borde más marcado que el de las tarjetas.
        $this->assertSame(1, preg_match(
            '/--bib-border-strong:\s*color-mix\(in srgb, var\(--bib-text-secondary\) (\d+)%, var\(--bib-surface\)\)/',
            self::bloque($tema),
            $m,
        ), "{$tema}: falta --bib-border-strong derivado con color-mix.");

        $t = self::tokens($tema);
        $fuerte = self::mezclar($t['text-secondary'], $t['surface'], (float) $m[1]);

        $this->assertContraste(3.0, $fuerte, $t['surface'], "{$tema}: border-strong contra surface");
    }

    public function test_el_tema_oscuro_del_sistema_repite_el_oscuro_explicito(): void
    {
        $this->assertSame(self::tokens('dark'), self::tokens('system'));
    }

    public function test_el_primario_por_defecto_de_la_configuracion_coincide_con_el_css(): void
    {
        $this->assertSame(
            self::tokens('light')['primary'],
            strtoupper((string) config('biblioteca.brand.primary')),
        );
    }
}
