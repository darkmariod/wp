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
        .column-layout { display: flex; gap: 20px; }
        .column { flex: 1; }
        .grand-total { font-size: 12pt; font-weight: bold; padding: 10px; background-color: #edf2f7; border-radius: 4px; margin-top: 15px; }
        .balanced { color: #276749; font-weight: bold; text-align: center; margin-top: 10px; padding: 8px; background-color: #f0fff4; border: 1px solid #c6f6d5; border-radius: 4px; }
        .unbalanced { color: #9b2c2c; font-weight: bold; text-align: center; margin-top: 10px; padding: 8px; background-color: #fff5f5; border: 1px solid #fed7d7; border-radius: 4px; }
        .section-title { color: #1a365d; font-weight: bold; font-size: 11pt; border-bottom: 1px solid #cbd5e0; padding-bottom: 4px; margin-top: 15px; }
        .footer { text-align: center; font-size: 8pt; color: #a0aec0; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Constructora XYZ</h1>
        <div class="subtitle">{{ $titulo }}</div>
        <div class="date">Fecha: {{ $data['fecha'] ?? now()->format('d/m/Y') }}</div>
    </div>

    <div class="column-layout">
        {{-- ACTIVO --}}
        <div class="column">
            <h2>ACTIVO</h2>

            @if(!empty($data['activo']['activo_corriente']['items']))
                <h3>Activo Corriente</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['activo']['activo_corriente']['items'] as $item)
                            <tr>
                                <td>{{ $item['codigo'] }}</td>
                                <td>{{ $item['nombre'] }}</td>
                                <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2">Total Activo Corriente</td>
                            <td class="text-right">${{ number_format($data['activo']['activo_corriente']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            @if(!empty($data['activo']['activo_no_corriente']['items']))
                <h3>Activo No Corriente</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['activo']['activo_no_corriente']['items'] as $item)
                            <tr>
                                <td>{{ $item['codigo'] }}</td>
                                <td>{{ $item['nombre'] }}</td>
                                <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2">Total Activo No Corriente</td>
                            <td class="text-right">${{ number_format($data['activo']['activo_no_corriente']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <div class="grand-total">
                <div style="display:flex; justify-content:space-between;">
                    <span>TOTAL ACTIVO</span>
                    <span>${{ number_format($data['total_activo'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- PASIVO + PATRIMONIO --}}
        <div class="column">
            <h2>PASIVO</h2>

            @if(!empty($data['pasivo']['corriente']['items']))
                <h3>Pasivo Corriente</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['pasivo']['corriente']['items'] as $item)
                            <tr>
                                <td>{{ $item['codigo'] }}</td>
                                <td>{{ $item['nombre'] }}</td>
                                <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2">Total Pasivo Corriente</td>
                            <td class="text-right">${{ number_format($data['pasivo']['corriente']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            @if(!empty($data['pasivo']['no_corriente']['items']))
                <h3>Pasivo No Corriente</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['pasivo']['no_corriente']['items'] as $item)
                            <tr>
                                <td>{{ $item['codigo'] }}</td>
                                <td>{{ $item['nombre'] }}</td>
                                <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2">Total Pasivo No Corriente</td>
                            <td class="text-right">${{ number_format($data['pasivo']['no_corriente']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <h2>PATRIMONIO</h2>

            @if(!empty($data['patrimonio']['items']))
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['patrimonio']['items'] as $item)
                            <tr>
                                <td>{{ $item['codigo'] }}</td>
                                <td>{{ $item['nombre'] }}</td>
                                <td class="text-right">${{ number_format($item['saldo'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total">
                            <td colspan="2">Total Patrimonio</td>
                            <td class="text-right">${{ number_format($data['patrimonio']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            <div class="grand-total">
                <div style="display:flex; justify-content:space-between;">
                    <span>TOTAL PASIVO + PATRIMONIO</span>
                    <span>${{ number_format($data['total_pasivo_patrimonio'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(abs($data['total_activo'] - $data['total_pasivo_patrimonio']) < 0.01)
        <div class="balanced">Balance cuadrado: Total Activo = Total Pasivo + Patrimonio</div>
    @else
        <div class="unbalanced">ALERTA: El balance NO cuadrado. Diferencia: ${{ number_format(abs($data['total_activo'] - $data['total_pasivo_patrimonio']), 2) }}</div>
    @endif

    <div class="footer">
        Reporte generado el {{ $generado_en }} | Sistema de Contabilidad para Construccion
    </div>
</body>
</html>
