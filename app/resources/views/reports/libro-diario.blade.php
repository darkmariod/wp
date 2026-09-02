<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #333; margin: 20px; }
        h1 { font-size: 18pt; color: #1a365d; border-bottom: 2px solid #1a365d; padding-bottom: 8px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #1a365d; padding-bottom: 10px; }
        .header h1 { border: none; margin-bottom: 5px; }
        .header .subtitle { font-size: 12pt; color: #718096; }
        .header .date { font-size: 10pt; color: #a0aec0; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 5px 0; }
        th { background-color: #1a365d; color: white; padding: 6px 8px; text-align: left; font-size: 9pt; }
        td { padding: 4px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9pt; }
        .text-right { text-align: right; }
        .asiento-header { background-color: #edf2f7; padding: 6px 10px; margin-top: 12px; margin-bottom: 0; border-radius: 4px 4px 0 0; font-size: 9pt; }
        .asiento-header strong { color: #1a365d; }
        .asiento-desc { padding: 4px 10px; font-style: italic; color: #718096; font-size: 9pt; margin-bottom: 0; }
        .asiento-total { background-color: #edf2f7; padding: 4px 10px; text-align: right; font-weight: bold; font-size: 9pt; border-radius: 0 0 4px 4px; border-top: 1px solid #cbd5e0; }
        .badge-aprobado { background-color: #c6f6d5; color: #276749; padding: 1px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .badge-borrador { background-color: #fefcbf; color: #975a16; padding: 1px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .footer { text-align: center; font-size: 8pt; color: #a0aec0; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Constructora XYZ</h1>
        <div class="subtitle">{{ $titulo }}</div>
        <div class="date">Generado el {{ $generado_en }}</div>
    </div>

    @forelse($data['asientos'] as $asiento)
        <div class="asiento-header">
            <strong>Asiento #{{ $asiento['numero_asiento'] }}</strong> &mdash;
            {{ $asiento['fecha'] }} &mdash;
            <span class="{{ $asiento['estado'] === 'aprobado' ? 'badge-aprobado' : 'badge-borrador' }}">
                {{ ucfirst($asiento['estado']) }}
            </span>
            @if($asiento['obra'] !== '—')
                &mdash; Obra: {{ $asiento['obra'] }}
            @endif
        </div>
        <div class="asiento-desc">{{ $asiento['descripcion'] }}</div>

        <table>
            <thead>
                <tr>
                    <th>Cuenta</th>
                    <th>Nombre</th>
                    <th>Referencia</th>
                    <th class="text-right">Debe</th>
                    <th class="text-right">Haber</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asiento['detalles'] as $detalle)
                    <tr>
                        <td>{{ $detalle['cuenta_codigo'] }}</td>
                        <td>{{ $detalle['cuenta_nombre'] }}</td>
                        <td>{{ $detalle['referencia'] ?? '—' }}</td>
                        <td class="text-right">{{ $detalle['debe'] > 0 ? '$' . number_format($detalle['debe'], 2) : '' }}</td>
                        <td class="text-right">{{ $detalle['haber'] > 0 ? '$' . number_format($detalle['haber'], 2) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="asiento-total">
            Debe: ${{ number_format($asiento['total_debe'], 2) }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Haber: ${{ number_format($asiento['total_haber'], 2) }}
        </div>
    @empty
        <p style="text-align:center; color:#a0aec0; padding:40px;">No se encontraron asientos contables en el periodo seleccionado.</p>
    @endforelse

    <div class="footer">
        Reporte generado el {{ $generado_en }} | Sistema de Contabilidad para Construccion
    </div>
</body>
</html>
