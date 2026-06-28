<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios - NovaRider</title>
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

        .filters-bar { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px 16px; margin-bottom: 18px; display: flex; gap: 24px; align-items: center; }
        .filters-bar .filter-item { font-size: 10px; color: #929079; }
        .filters-bar .filter-item strong { color: #042D29; font-weight: 600; }

        .summary { font-size: 11px; color: #5C5B4E; margin-bottom: 14px; padding: 0 2px; }
        .summary strong { color: #042D29; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #042D29; color: #FFFFFF; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; }
        thead th:first-child { border-radius: 6px 0 0 0; }
        thead th:last-child { border-radius: 0 6px 0 0; }
        tbody td { padding: 9px 12px; font-size: 11px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        tbody tr:hover { background: #F0EFEA; }
        .col-id { width: 40px; text-align: center; color: #929079; font-weight: 600; }
        .col-name { font-weight: 500; }
        .col-user { font-family: monospace; font-size: 10px; }
        .col-ci { text-align: center; }
        .col-role { text-align: center; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .badge-admin { background: rgba(116,17,2,0.1); color: #741102; }
        .badge-cajero { background: rgba(146,144,121,0.15); color: #5C5B4E; }
        .badge-mecanico { background: rgba(4,45,41,0.12); color: #042D29; }
        .badge-recepcionista { background: rgba(146,144,121,0.1); color: #5C5B4E; }
        .badge-default { background: #F3F4F6; color: #6B7280; }

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
                <div class="report-title">Reporte de Usuarios</div>
                <div class="report-date">Generado: {{ $fechaGeneracion }}</div>
            </div>
        </div>

        <div class="filters-bar">
            <div class="filter-item"><strong>Estado:</strong> {{ $filtros['estado'] }}</div>
            @if($filtros['rol'])
                <div class="filter-item"><strong>Rol:</strong> {{ $filtros['rol'] }}</div>
            @endif
            @if($filtros['busqueda'])
                <div class="filter-item"><strong>Búsqueda:</strong> "{{ $filtros['busqueda'] }}"</div>
            @endif
        </div>

        <div class="summary">
            Mostrando <strong>{{ $totalRegistros }}</strong> registro{{ $totalRegistros !== 1 ? 's' : '' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-id">#</th>
                    <th>Empleado</th>
                    <th>Usuario</th>
                    <th class="col-ci">CI</th>
                    <th class="col-role">Rol</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                    <tr>
                        <td class="col-id">{{ $u['id_usuario'] }}</td>
                        <td class="col-name">{{ $u['nombre_completo'] ?: '—' }}</td>
                        <td class="col-user">{{ $u['username'] }}</td>
                        <td class="col-ci">{{ $u['ci'] ?: '—' }}</td>
                        <td class="col-role">
                            @foreach($u['roles'] as $rol)
                                @php
                                    $rolLower = strtolower($rol['nombre'] ?? '');
                                    $badgeClass = 'badge-default';
                                    if ($rolLower === 'administrador') $badgeClass = 'badge-admin';
                                    elseif ($rolLower === 'cajero') $badgeClass = 'badge-cajero';
                                    elseif ($rolLower === 'mecánico' || $rolLower === 'mecanico') $badgeClass = 'badge-mecanico';
                                    elseif ($rolLower === 'recepcionista') $badgeClass = 'badge-recepcionista';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $rol['nombre'] }}</span>
                            @endforeach
                        </td>
                        <td style="text-align:center;">
                            @if($u['estadoA'])
                                <span class="badge badge-mecanico">Activo</span>
                            @else
                                <span class="badge badge-default">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">No se encontraron usuarios con los filtros aplicados</td>
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
