<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #333; margin: 20px; }
        h1 { font-size: 18pt; color: #1a365d; border-bottom: 2px solid #1a365d; padding-bottom: 8px; }
        h2 { font-size: 14pt; color: #2d3748; margin-top: 20px; }
        h3 { font-size: 12pt; color: #4a5568; margin-top: 15px; margin-bottom: 5px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #1a365d; padding-bottom: 10px; }
        .header h1 { border: none; margin-bottom: 5px; }
        .header .subtitle { font-size: 12pt; color: #718096; }
        .header .date { font-size: 10pt; color: #a0aec0; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background-color: #1a365d; color: white; padding: 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
        .total { font-weight: bold; border-top: 2px solid #1a365d; }
        .text-right { text-align: right; }
        .calc-row { padding: 10px; background-color: #edf2f7; border-radius: 4px; margin: 10px 0; font-weight: bold; }
        .final-row { padding: 12px; background-color: #1a365d; color: white; border-radius: 4px; margin: 15px 0; font-size: 12pt; }
        .section-title { color: #1a365d; font-weight: bold; font-size: 11pt; border-bottom: 1px solid #cbd5e0; padding-bottom: 4px; margin-top: 20px; }
        .footer { text-align: center; font-size: 8pt; color: #a0aec0; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Constructora XYZ</h1>
        <div class="subtitle">{{ $titulo }}</div>
        <div class="date">Periodo: {{ $data['periodo']['inicio'] }} al {{ $data['periodo']['fin'] }}</div>
    </div>

    {{-- INGRESOS --}}
    @if(!empty($data['grupos']['ingresos']['items']))
        <div class="section-title">INGRESOS</div>
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grupos']['ingresos']['items'] as $item)
                    <tr>
                        <td>{{ $item['codigo'] }}</td>
                        <td>{{ $item['nombre'] }}</td>
                        <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total Ingresos</td>
                    <td class="text-right">${{ number_format($data['grupos']['ingresos']['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- COSTOS --}}
    @if(!empty($data['grupos']['costos']['items']))
        <div class="section-title">COSTOS</div>
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grupos']['costos']['items'] as $item)
                    <tr>
                        <td>{{ $item['codigo'] }}</td>
                        <td>{{ $item['nombre'] }}</td>
                        <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total Costos</td>
                    <td class="text-right">${{ number_format($data['grupos']['costos']['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="calc-row">
        <div style="display:flex; justify-content:space-between;">
            <span>UTILIDAD BRUTA</span>
            <span>${{ number_format($data['calculado']['utilidad_bruta']['monto'], 2) }} ({{ number_format($data['calculado']['utilidad_bruta']['porcentaje'], 1) }}%)</span>
        </div>
    </div>

    {{-- GASTOS ADMINISTRATIVOS --}}
    @if(!empty($data['grupos']['gastos_administrativos']['items']))
        <div class="section-title">GASTOS ADMINISTRATIVOS</div>
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grupos']['gastos_administrativos']['items'] as $item)
                    <tr>
                        <td>{{ $item['codigo'] }}</td>
                        <td>{{ $item['nombre'] }}</td>
                        <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total Gastos Administrativos</td>
                    <td class="text-right">${{ number_format($data['grupos']['gastos_administrativos']['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- GASTOS DE VENTA --}}
    @if(!empty($data['grupos']['gastos_venta']['items']))
        <div class="section-title">GASTOS DE VENTA</div>
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grupos']['gastos_venta']['items'] as $item)
                    <tr>
                        <td>{{ $item['codigo'] }}</td>
                        <td>{{ $item['nombre'] }}</td>
                        <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total Gastos de Venta</td>
                    <td class="text-right">${{ number_format($data['grupos']['gastos_venta']['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- GASTOS FINANCIEROS --}}
    @if(!empty($data['grupos']['gastos_financieros']['items']))
        <div class="section-title">GASTOS FINANCIEROS</div>
        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grupos']['gastos_financieros']['items'] as $item)
                    <tr>
                        <td>{{ $item['codigo'] }}</td>
                        <td>{{ $item['nombre'] }}</td>
                        <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Total Gastos Financieros</td>
                    <td class="text-right">${{ number_format($data['grupos']['gastos_financieros']['total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="calc-row">
        <div style="display:flex; justify-content:space-between;">
            <span>UTILIDAD OPERATIVA</span>
            <span>${{ number_format($data['calculado']['utilidad_operativa']['monto'], 2) }} ({{ number_format($data['calculado']['utilidad_operativa']['porcentaje'], 1) }}%)</span>
        </div>
    </div>

    <div class="calc-row">
        <div style="display:flex; justify-content:space-between;">
            <span>UTILIDAD ANTES DE IMPUESTOS</span>
            <span>${{ number_format($data['calculado']['utilidad_antes_impuestos']['monto'], 2) }} ({{ number_format($data['calculado']['utilidad_antes_impuestos']['porcentaje'], 1) }}%)</span>
        </div>
    </div>

    <div class="final-row">
        <div style="display:flex; justify-content:space-between;">
            <span>UTILIDAD NETA</span>
            <span>${{ number_format($data['calculado']['utilidad_neta']['monto'], 2) }} ({{ number_format($data['calculado']['utilidad_neta']['porcentaje'], 1) }}%)</span>
        </div>
    </div>

    <div class="footer">
        Reporte generado el {{ $generado_en }} | Sistema de Contabilidad para Construccion
    </div>
</body>
</html>
