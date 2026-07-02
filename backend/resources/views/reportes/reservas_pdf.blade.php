<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Reservas - NovaRider</title>
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

        .resumen-estados { display: flex; gap: 16px; margin-bottom: 18px; padding: 10px 16px; background: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; flex-wrap: wrap; }
        .resumen-estados .res-item { font-size: 10px; color: #5C5B4E; }
        .resumen-estados .res-item strong { color: #042D29; }
        .resumen-estados .res-item .total-adelantos { font-size: 13px; font-weight: 700; color: #042D29; }

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
        .col-ci { text-align: center; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .badge-pendiente { background: #FEF3C7; color: #92400E; }
        .badge-completada { background: #D1FAE5; color: #065F46; }
        .badge-enviado { background: #DBEAFE; color: #1E40AF; }
        .badge-cancelada { background: #FEE2E2; color: #991B1B; }

        .detalles-sublista { margin-top: 4px; font-size: 9px; color: #5C5B4E; padding-left: 8px; border-left: 2px solid #E5E7EB; }
        .detalles-sublista .det-row { display: flex; gap: 12px; padding: 2px 0; }

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
                <div class="report-title">Reporte de Reservas y Envíos</div>
                <div class="report-date">Generado: {{ $fechaGeneracion }}</div>
            </div>
        </div>

        <div class="filters-bar">
            <div class="filter-item"><strong>Estado:</strong> {{ $filtros['estado'] }}</div>
            @if($filtros['busqueda'])
                <div class="filter-item"><strong>Búsqueda:</strong> "{{ $filtros['busqueda'] }}"</div>
            @endif
        </div>

        <div class="summary">
            <span>Mostrando <strong>{{ $totalRegistros }}</strong> reserva{{ $totalRegistros !== 1 ? 's' : '' }}</span>
        </div>

        <div class="resumen-estados">
            <div class="res-item">Total Adelantos: <strong class="total-adelantos">Bs {{ number_format($totalAdelantos, 2) }}</strong></div>
            @foreach($reservasPorEstado as $estado => $cantidad)
                <div class="res-item">{{ ucfirst($estado) }}: <strong>{{ $cantidad }}</strong></div>
            @endforeach
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th>Cliente</th>
                    <th class="col-ci">CI</th>
                    <th>Solicitud</th>
                    <th>Expiración</th>
                    <th class="num">Adelanto</th>
                    <th>Método Pago</th>
                    <th>Estado</th>
                    <th>Envío</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $r)
                    <tr>
                        <td class="col-id">{{ $r['id_reserva'] }}</td>
                        <td>{{ $r['cliente']['nombre_completo'] ?? '—' }}</td>
                        <td class="col-ci">{{ $r['cliente']['ci'] ?? '—' }}</td>
                        <td>{{ $r['fecha_solicitud'] }}</td>
                        <td>{{ $r['fecha_expiracion'] }}</td>
                        <td class="col-num">{{ $r['monto_adelanto'] > 0 ? 'Bs ' . number_format($r['monto_adelanto'], 2) : '—' }}</td>
                        <td>{{ $r['adelanto_metodo_pago'] ?: '—' }}</td>
                        <td>
                            @php
                                $badgeClass = 'badge-' . ($r['estado'] ?? 'pendiente');
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $r['estado'] ?? 'pendiente' }}</span>
                        </td>
                        <td>
                            @if($r['envio'])
                                {{ $r['envio']['empresa_transporte'] ?? '—' }} / {{ $r['envio']['nro_guia'] ?? '—' }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9" style="padding: 0 12px 8px 52px; border-bottom: 1px solid #F3F4F6;">
                            @if(count($r['detalles']) > 0)
                                <div class="detalles-sublista">
                                    @foreach($r['detalles'] as $d)
                                        <div class="det-row">
                                            <span style="flex:1;">{{ $d['producto']['nombre'] ?? 'Producto' }}</span>
                                            <span style="width:80px;text-align:right;">Bs {{ number_format($d['producto']['precio_venta'] ?? 0, 2) }}</span>
                                            <span style="width:60px;text-align:center;">x{{ $d['cantidad_reservada'] }}</span>
                                            <span style="width:80px;text-align:right;font-weight:600;">Bs {{ number_format(($d['producto']['precio_venta'] ?? 0) * $d['cantidad_reservada'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:#929079;">Sin productos reservados</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="no-data">No se encontraron reservas con los filtros aplicados</td>
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
