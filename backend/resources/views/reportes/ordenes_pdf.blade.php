<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Órdenes de Trabajo</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; }
        .header { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #2d3748; padding-bottom: 10px; }
        .logo { width: 120px; float: left; }
        .title-container { float: right; text-align: right; width: 500px; }
        .title { font-size: 20px; font-weight: bold; color: #2d3748; margin: 0; }
        .info-meta { font-size: 10px; color: #718096; margin-top: 5px; }
        .clear { clear: both; }
        .filtros-box { background: #f7fafc; padding: 10px; border: 1px solid #e2e8f0; margin-bottom: 20px; border-radius: 4px; }
        .filtros-box table { width: 100%; font-size: 11px; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th { background-color: #2d3748; color: white; padding: 8px; text-align: left; font-size: 11px; }
        .table-data td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .table-data tr:nth-child(even) { background-color: #f7fafc; }
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-pendiente { background-color: #feebc8; color: #c05621; }
        .badge-progreso { background-color: #ebf8ff; color: #2b6cb0; }
        .badge-completado { background-color: #c6f6d5; color: #22543d; }
        .total { text-align: right; font-weight: bold; margin-top: 15px; font-size: 12px; }
    </style>
</head>
<body>

    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
        @endif
        <div class="title-container">
            <div class="title">REPORTE DE ÓRDENES DE TRABAJO</div>
            <div class="info-meta">
                Generado el: {{ $fechaGeneracion }} | Por: {{ $usuarioGenera }}
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="filtros-box">
        <table>
            <tr>
                <td><strong>Filtro Estado:</strong> {{ $filtros['estado'] }}</td>
                <td><strong>Mecánico:</strong> {{ $filtros['mecanico'] }}</td>
                @if($filtros['busqueda'])
                    <td><strong>Búsqueda:</strong> "{{ $filtros['busqueda'] }}"</td>
                @endif
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Nro Orden</th>
                <th>Fecha Ingreso</th>
                <th>Cliente / Celular</th>
                <th>Motocicleta (Placa)</th>
                <th>Mecánico Asignado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenes as $orden)
                <tr>
                    <td><strong>#{{ $orden->id_orden }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($orden->fecha_ingreso)->format('d/m/Y') }}</td>
                    <td>
                        {{ $orden->motocicleta->cliente->primer_nombre ?? 'Sin cliente' }} 
                        {{ $orden->motocicleta->cliente->apellido_paterno ?? '' }}
                    </td>
                    <td>
                        {{ $orden->motocicleta->marca }} {{ $orden->motocicleta->modelo }} 
                        (<strong>{{ $orden->motocicleta->placa }}</strong>)
                    </td>
                    <td>
                        {{ $orden->empleado->primer_nombre ?? 'No asignado' }} 
                        {{ $orden->empleado->apellido_paterno ?? '' }}
                    </td>
                    <td>
                    @php
        $estadoLower = strtolower($orden->estado);
        $claseBadge = 'badge-pendiente';
        if($estadoLower == 'en proceso') $claseBadge = 'badge-progreso';
        if($estadoLower == 'listo para entrega') $claseBadge = 'badge-completado';
        if($estadoLower == 'entregado') $claseBadge = 'badge-completado'; // O el color que prefieras
    @endphp
    <span class="badge {{ $claseBadge }}">{{ $orden->estado }}</span>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #a0aec0;">
                        No se encontraron órdenes de trabajo bajo los criterios seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        Total de Registros: {{ $totalRegistros }}
    </div>

</body>
</html>