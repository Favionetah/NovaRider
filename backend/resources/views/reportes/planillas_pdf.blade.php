<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Planillas - NovaRider</title>
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
        .summary { font-size: 11px; color: #5C5B4E; margin-bottom: 14px; padding: 0 2px; }
        .summary strong { color: #042D29; }
        .totals { text-align: right; font-size: 12px; margin-bottom: 18px; padding: 10px 16px; background: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; }
        .totals span { display: inline-block; margin-left: 20px; }
        .totals .label { color: #929079; font-weight: 500; }
        .totals .value { font-weight: 700; color: #042D29; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #042D29; color: #FFFFFF; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; }
        thead th:first-child { border-radius: 6px 0 0 0; }
        thead th:last-child { border-radius: 0 6px 0 0; }
        thead th.num { text-align: right; }
        tbody td { padding: 9px 12px; font-size: 11px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        .col-id { width: 40px; text-align: center; color: #929079; font-weight: 600; }
        .col-num { text-align: right; }
        .no-data { text-align: center; padding: 30px; color: #929079; font-style: italic; }
        .footer { border-top: 2px solid #E5E7EB; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; }
        .footer-left { font-size: 9px; color: #929079; }
        .footer-right { font-size: 9px; color: #929079; }
        .footer-right strong { color: #042D29; }
        .meses { font-size: 14px; }
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
                <div class="report-title">Reporte de Planillas</div>
                <div class="report-date">Generado: {{ $fechaGeneracion }}</div>
            </div>
        </div>

        @if($empleadoNombre)
        <div class="filters-bar">
            <div class="filter-item"><strong>Empleado:</strong> {{ $empleadoNombre }}</div>
        </div>
        @endif

        <div class="summary">
            <span>Mostrando <strong>{{ $totalRegistros }}</strong> planilla{{ $totalRegistros !== 1 ? 's' : '' }}</span>
        </div>

        <div class="totals">
            <span><span class="label">Bruto:</span> <span class="value">Bs {{ number_format($totalBruto, 2) }}</span></span>
            <span><span class="label">Bonos:</span> <span class="value">Bs {{ number_format($totalBonos, 2) }}</span></span>
            <span><span class="label">Deducciones:</span> <span class="value">Bs {{ number_format($totalDeducciones, 2) }}</span></span>
            <span><span class="label">Neto:</span> <span class="value">Bs {{ number_format($totalNeto, 2) }}</span></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th>Período</th>
                    <th class="num">Sueldo Bruto</th>
                    <th class="num">Bonos</th>
                    <th class="num">Deducciones</th>
                    <th class="num">Sueldo Neto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planillas as $p)
                    @php
                        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    @endphp
                    <tr>
                        <td class="col-id">{{ $p['id_planilla'] }}</td>
                        <td><strong>{{ $meses[$p['mes'] - 1] }}</strong> {{ $p['anio'] }}</td>
                        <td class="col-num">Bs {{ number_format($p['sueldo_bruto'], 2) }}</td>
                        <td class="col-num">Bs {{ number_format($p['bonos'], 2) }}</td>
                        <td class="col-num">Bs {{ number_format($p['deducciones'], 2) }}</td>
                        <td class="col-num"><strong>Bs {{ number_format($p['sueldo_neto'], 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">No se encontraron planillas con los filtros aplicados</td>
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