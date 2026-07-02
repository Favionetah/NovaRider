<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Caja</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #2F312A; font-size: 11px; }
        .header { border-bottom: 2px solid #042D29; margin-bottom: 16px; padding-bottom: 10px; }
        h1 { color: #042D29; font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6B7280; font-size: 10px; }
        .filtros { background: #F5F4F0; border: 1px solid #E2E4E3; padding: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #042D29; color: #FFFFFF; padding: 7px; text-align: left; }
        td { border-bottom: 1px solid #E2E4E3; padding: 7px; vertical-align: top; }
        .right { text-align: right; }
        .total { margin-top: 14px; text-align: right; font-weight: bold; color: #042D29; font-size: 13px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Caja</h1>
        <div class="meta">NovaRider - Generado el {{ $fecha }}</div>
    </div>

    <div class="filtros">
        <strong>Filtros:</strong>
        Fecha inicio: {{ $filtros['fecha_inicio'] ?: 'Todas' }} |
        Fecha fin: {{ $filtros['fecha_fin'] ?: 'Todas' }} |
        Concepto: {{ $filtros['concepto'] ?: 'Todos' }} |
        Solo efectivo: {{ $filtros['solo_efectivo'] ? 'Si' : 'No' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Recibo</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Concepto</th>
                <th>Metodo</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
                <tr>
                    <td>{{ $venta->nro_factura }}</td>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->cliente_nombre }}</td>
                    <td>{{ $venta->concepto_resumen ?: 'Venta de caja' }}</td>
                    <td>{{ $venta->metodo_pago }}</td>
                    <td class="right">Bs {{ number_format($venta->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#929079;">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">Total filtrado: Bs {{ number_format($total, 2) }}</div>
</body>
</html>
