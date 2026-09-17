<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Asistencia de {{ $child->name }} — {{ ucfirst($mes->locale('es')->isoFormat('MMMM YYYY')) }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.subtitulo { font-size: 12px; color: #4b5563; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .resumen td:first-child { font-weight: bold; width: 65%; }
        .feriado { color: #6b7280; font-style: italic; }
        .presente { color: #047857; }
        .atraso { color: #b45309; }
        .falta_justificada { color: #1d4ed8; }
        .falta_injustificada { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte mensual de asistencia</h1>
    <p class="subtitulo">
        {{ $child->name }} — {{ ucfirst($mes->locale('es')->isoFormat('MMMM YYYY')) }}
        · Ambiente: {{ $child->environment?->name ?? '—' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dias as $dia)
                <tr>
                    <td>{{ $dia['fecha']->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($dia['fecha']->locale('es')->isoFormat('dddd')) }}</td>
                    @if ($dia['feriado'])
                        <td class="feriado">Feriado: {{ $dia['feriado'] }}</td>
                    @elseif ($dia['finDeSemana'])
                        <td class="feriado">Fin de semana</td>
                    @elseif ($dia['status'])
                        <td class="{{ $dia['status'] }}">
                            {{ match ($dia['status']) {
                                'presente' => 'Presente',
                                'atraso' => 'Atraso',
                                'falta_justificada' => 'Falta justificada',
                                'falta_injustificada' => 'Falta injustificada',
                                default => $dia['status'],
                            } }}
                        </td>
                    @else
                        <td>Sin registro</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="resumen">
        <tr><td colspan="2">Resumen del mes</td></tr>
        <tr><td>Presentes</td><td>{{ $resumen['presente'] }}</td></tr>
        <tr><td>Atrasos</td><td>{{ $resumen['atraso'] }}</td></tr>
        <tr><td>Faltas justificadas</td><td>{{ $resumen['falta_justificada'] }}</td></tr>
        <tr><td>Faltas injustificadas</td><td>{{ $resumen['falta_injustificada'] }}</td></tr>
        <tr><td>Feriados</td><td>{{ $resumen['feriado'] }}</td></tr>
        <tr><td>Sin registro</td><td>{{ $resumen['sin_registro'] }}</td></tr>
    </table>
</body>
</html>
