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
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        th { background-color: #1a365d; color: white; padding: 6px 8px; text-align: left; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .total { font-weight: bold; border-top: 2px solid #1a365d; }
        .negativo { color: #9b2c2c; }
        .positivo { color: #276749; }
        .badge-sobrecosto { background-color: #fed7d7; color: #9b2c2c; padding: 1px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .badge-subconsumo { background-color: #c6f6d5; color: #276749; padding: 1px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .badge-exacto { background-color: #e2e8f0; color: #4a5568; padding: 1px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .summary { background-color: #edf2f7; padding: 12px; border-radius: 4px; margin-top: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .summary-total { font-size: 12pt; font-weight: bold; border-top: 2px solid #1a365d; padding-top: 8px; margin-top: 8px; }
        .footer { text-align: center; font-size: 8pt; color: #a0aec0; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Constructora XYZ</h1>
        <div class="subtitle">{{ $titulo }}</div>
        <div class="date">Obra: {{ $data['obra']['codigo'] }} - {{ $data['obra']['nombre'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Descripcion</th>
                <th>UdM</th>
                <th class="text-right">Presupuestado</th>
                <th class="text-right">Ejecutado</th>
                <th class="text-right">Desviacion</th>
                <th class="text-right">%</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
                <tr>
                    <td>{{ $item['codigo'] }}</td>
                    <td>{{ $item['descripcion'] }}</td>
                    <td>{{ $item['unidad_medida'] }}</td>
                    <td class="text-right">${{ number_format($item['subtotal_presupuestado'], 2) }}</td>
                    <td class="text-right">${{ number_format($item['subtotal_ejecutado'], 2) }}</td>
                    <td class="text-right {{ $item['desviacion'] > 0 ? 'negativo' : ($item['desviacion'] < 0 ? 'positivo' : '') }}">
                        ${{ number_format($item['desviacion'], 2) }}
                    </td>
                    <td class="text-right">{{ number_format($item['porcentaje'], 1) }}%</td>
                    <td>
                        @if($item['clasificacion'] === 'sobrecosto')
                            <span class="badge-sobrecosto">Sobrecosto</span>
                        @elseif($item['clasificacion'] === 'subconsumo')
                            <span class="badge-subconsumo">Subconsumo</span>
                        @else
                            <span class="badge-exacto">Exacto</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Total Presupuestado:</span>
            <strong>${{ number_format($data['totales']['presupuestado'], 2) }}</strong>
        </div>
        <div class="summary-row">
            <span>Total Ejecutado:</span>
            <strong>${{ number_format($data['totales']['ejecutado'], 2) }}</strong>
        </div>
        <div class="summary-row">
            <span>Desviacion Total:</span>
            <strong class="{{ $data['totales']['desviacion'] > 0 ? 'negativo' : ($data['totales']['desviacion'] < 0 ? 'positivo' : '') }}">
                ${{ number_format($data['totales']['desviacion'], 2) }}
            </strong>
        </div>
        <div class="summary-row summary-total">
            <span>Porcentaje de Desviacion:</span>
            <span>
                {{ number_format($data['totales']['porcentaje'], 1) }}%
                @if($data['totales']['clasificacion'] === 'sobrecosto')
                    <span class="badge-sobrecosto">Sobrecosto</span>
                @elseif($data['totales']['clasificacion'] === 'subconsumo')
                    <span class="badge-subconsumo">Subconsumo</span>
                @else
                    <span class="badge-exacto">Exacto</span>
                @endif
            </span>
        </div>
    </div>

    <div class="footer">
        Reporte generado el {{ $generado_en }} | Sistema de Contabilidad para Construccion
    </div>
</body>
</html>
