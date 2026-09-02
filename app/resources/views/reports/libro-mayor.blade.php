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
        .account-info { background-color: #edf2f7; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 10pt; }
        .account-info strong { color: #1a365d; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background-color: #1a365d; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .total { font-weight: bold; border-top: 2px solid #1a365d; }
        .saldo-inicial { background-color: #ebf8ff; padding: 8px 10px; border-radius: 4px; margin-bottom: 10px; }
        .saldo-final { background-color: #1a365d; color: white; padding: 10px; border-radius: 4px; margin-top: 15px; font-size: 11pt; font-weight: bold; }
        .footer { text-align: center; font-size: 8pt; color: #a0aec0; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Constructora XYZ</h1>
        <div class="subtitle">{{ $titulo }}</div>
        <div class="date">Periodo: {{ $data['periodo']['inicio'] }} al {{ $data['periodo']['fin'] }}</div>
    </div>

    <div class="account-info">
        <strong>Cuenta:</strong> {{ $data['cuenta']['codigo'] }} - {{ $data['cuenta']['nombre'] }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Tipo:</strong> {{ ucfirst($data['cuenta']['tipo']) }}
    </div>

    <div class="saldo-inicial">
        <strong>Saldo Inicial:</strong> ${{ number_format($data['saldo_inicial'], 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>N Asiento</th>
                <th>Referencia</th>
                <th class="text-right">Debe</th>
                <th class="text-right">Haber</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['movimientos'] as $mov)
                <tr>
                    <td>{{ $mov['fecha'] }}</td>
                    <td>{{ $mov['numero_asiento'] }}</td>
                    <td>{{ $mov['referencia'] ?? '—' }}</td>
                    <td class="text-right">{{ $mov['debe'] > 0 ? '$' . number_format($mov['debe'], 2) : '' }}</td>
                    <td class="text-right">{{ $mov['haber'] > 0 ? '$' . number_format($mov['haber'], 2) : '' }}</td>
                    <td class="text-right" style="font-weight: bold;">${{ number_format($mov['saldo'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3">TOTALES</td>
                <td class="text-right">${{ number_format($data['total_debe'], 2) }}</td>
                <td class="text-right">${{ number_format($data['total_haber'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="saldo-final">
        <div style="display:flex; justify-content:space-between;">
            <span>Saldo Final</span>
            <span>${{ number_format($data['saldo_final'], 2) }}</span>
        </div>
    </div>

    <div class="footer">
        Reporte generado el {{ $generado_en }} | Sistema de Contabilidad para Construccion
    </div>
</body>
</html>
