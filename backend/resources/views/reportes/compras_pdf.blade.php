<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras - NovaRider</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1F2937; }
        .page { padding: 30px 40px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #042D29; padding-bottom: 16px; margin-bottom: 20px; }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .header-left img { width: 56px; height: 56px; }
        .header-left .brand h1 { font-size: 18px; font-weight: 700; color: #042D29; margin-bottom: 2px; }
        .header-left .brand p { font-size: 11px; color: #929079; }
        .header-right { text-align: right; }
        .header-right .report-title { font-size: 15px; font-weight: 700; color: #042D29; margin-bottom: 4px; }
        .header-right .report-date { font-size: 10px; color: #929079; }

        .filters-bar { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 16px; margin-bottom: 18px; display: flex; gap: 24px; align-items: center; flex-wrap: wrap; }
        .filters-bar .filter-item { font-size: 10px; color: #929079; }
        .filters-bar .filter-item strong { color: #042D29; font-weight: 600; }

        .summary { font-size: 11px; color: #5C5B4E; margin-bottom: 14px; padding: 0 2px; display: flex; justify-content: space-between; }
        .summary strong { color: #042D29; }

        .total-general { text-align: right; font-size: 14px; font-weight: 700; color: #042D29; margin-bottom: 18px; padding: 10px 16px; background: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #042D29; color: #FFFFFF; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; }
        thead th:first-child { border-radius: 6px 0 0 0; }
        thead th:last-child { border-radius: 0 6px 0 0; }
        thead th.num { text-align: right; }
        tbody td { padding: 9px 12px; font-size: 11px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        tbody tr:hover { background: #F0EFEA; }
        .col-id { width: 40px; text-align: center; color: #929079; font-weight: 600; }
        .col-num { text-align: right; font-family: monospace; }

        .no-data { text-align: center; padding: 30px; color: #929079; font-style: italic; }

        .footer { border-top: 2px solid #E5E7EB; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; }
        .footer-left { font-size: 9px; color: #929079; }
        .footer-right { font-size: 9px; color: #929079; }
        .footer-right strong { color: #042D29; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="header-left">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="NovaRider">
                @endif
                <div class="brand">
                    <h1>NovaRider</h1>
                    <p>Sistema de Gestión de Taller Mecánico</p>
                </div>
            </div>
            <div class="header-right">
                <div class="report-title">Reporte de Compras</div>
                <div class="report-date">Generado: {{ $fechaGeneracion }}</div>
            </div>
        </div>

        <div class="filters-bar">
            @if($filtros['proveedor'])
                <div class="filter-item"><strong>Proveedor:</strong> {{ $filtros['proveedor'] }}</div>
            @endif
            @if($filtros['fecha_desde'] !== '—')
                <div class="filter-item"><strong>Desde:</strong> {{ $filtros['fecha_desde'] }}</div>
            @endif
            @if($filtros['fecha_hasta'] !== '—')
                <div class="filter-item"><strong>Hasta:</strong> {{ $filtros['fecha_hasta'] }}</div>
            @endif
            @if($filtros['busqueda'])
                <div class="filter-item"><strong>Búsqueda:</strong> "{{ $filtros['busqueda'] }}"</div>
            @endif
        </div>

        <div class="summary">
            <span>Mostrando <strong>{{ $totalRegistros }}</strong> compra{{ $totalRegistros !== 1 ? 's' : '' }}</span>
        </div>

        <div class="total-general">
            Total General: Bs {{ number_format($totalGeneral, 2) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Nro. Factura</th>
                    <th class="num">Productos</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $c)
                    <tr>
                        <td class="col-id">{{ $c['id_compra'] }}</td>
                        <td>{{ $c['proveedor']['nombre'] ?? '—' }}</td>
                        <td>{{ $c['fecha'] }}</td>
                        <td>{{ $c['nro_factura_proveedor'] ?? '—' }}</td>
                        <td class="col-num">{{ count($c['detalles']) }}</td>
                        <td class="col-num">Bs {{ number_format($c['total_compra'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">No se encontraron compras con los filtros aplicados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div class="footer-left">
                NovaRider &mdash; Sistema de Gestión de Taller Mecánico
            </div>
            <div class="footer-right">
                Generado por: <strong>{{ $usuarioGenera }}</strong> &mdash; {{ $fechaGeneracion }}
            </div>
        </div>
    </div>
</body>
</html>
