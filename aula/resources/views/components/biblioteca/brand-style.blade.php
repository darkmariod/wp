@php
    // Verde del colegio: su versión oscura ya viene afinada en biblioteca.css.
    $verdeColegio = '#126333';
    $textoOscuro = '#0B1F14';

    $luminancia = function (string $hex): float {
        [$r, $g, $b] = array_map(function (string $par): float {
            $c = hexdec($par) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    };

    // Un valor que no sea #RRGGBB se ignora: nunca llega al CSS.
    $configurado = config('biblioteca.brand.primary');
    $primario = is_string($configurado) && preg_match('/^#[0-9a-f]{6}$/i', $configurado) === 1
        ? strtoupper($configurado)
        : null;

    if ($primario) {
        $lp = $luminancia($primario);
        $textoSobrePrimario = (1.05 / ($lp + 0.05)) >= (($lp + 0.05) / ($luminancia($textoOscuro) + 0.05))
            ? '#FFFFFF'
            : $textoOscuro;
        $oscuro = "color-mix(in srgb, {$primario} 55%, white)";
    }
@endphp

@if ($primario)
    {{-- html:root pesa más que :root, así gana al CSS aunque Vite lo inyecte después. --}}
    <style>
        html:root { --bib-primary: {{ $primario }}; --bib-primary-contrast: {{ $textoSobrePrimario }}; }
        @if ($primario !== $verdeColegio)
        html:root[data-theme="dark"] { --bib-primary: {{ $oscuro }}; --bib-primary-contrast: {{ $textoOscuro }}; }
        @@media (prefers-color-scheme: dark) {
            html:root[data-theme="system"] { --bib-primary: {{ $oscuro }}; --bib-primary-contrast: {{ $textoOscuro }}; }
        }
        @endif
    </style>
@endif
