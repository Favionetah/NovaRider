<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #042D29; padding-bottom: 10px; }
        .header h1 { color: #042D29; margin: 0; font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #042D29; color: white; padding: 8px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #ddd; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NovaRider</h1>
        <p><strong>{{ $titulo }}</strong></p>
        <p>Fecha: {{ $fecha }}</p>
    </div>

    <table>
        <thead>
            @if($tipo === 'clientes')
                <tr><th>Cliente</th><th>CI</th><th>Teléfono</th><th class="text-center">Motos</th></tr>
            @elseif($tipo === 'motos')
                <tr><th>Placa</th><th>Marca/Modelo</th><th>Año</th><th>Cliente</th></tr>
            @elseif($tipo === 'usuarios')
                <tr><th>Usuario</th><th>Cargo</th><th>Roles</th></tr>
            @elseif($tipo === 'inventario')
                <tr><th>Producto</th><th>Stock</th><th>P. Venta</th></tr>
            @elseif($tipo === 'ventas')
                <tr><th>Fecha</th><th>Cliente</th><th>Metodo</th><th class="text-right">Total</th></tr>
            @elseif($tipo === 'taller')
                <tr><th>Orden</th><th>Fecha</th><th>Moto</th><th>Estado</th><th>Repuestos</th></tr>
            @elseif($tipo === 'caja')
                <tr><th>Fecha</th><th>Recibo</th><th>Concepto</th><th>Metodo</th><th class="text-right">Total</th></tr>
            @endif
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    @if($tipo === 'clientes')
                        <td>{{ $item->primer_nombre }} {{ $item->apellido_paterno }}</td>
                        <td>{{ $item->ci }}</td>
                        <td>{{ $item->telefono }}</td>
                        <td class="text-center">{{ $item->motocicletas_count }}</td>
                    @elseif($tipo === 'motos')
                        <td>{{ $item->placa }}</td>
                        <td>{{ $item->marca }} {{ $item->modelo }}</td>
                        <td>{{ $item->anio }}</td>
                        <td>{{ $item->cliente ? $item->cliente->primer_nombre . ' ' . $item->cliente->apellido_paterno : '—' }}</td>
                    @elseif($tipo === 'usuarios')
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->empleado->cargo ?? '—' }}</td>
                        <td>{{ $item->roles->pluck('nombre')->implode(', ') }}</td>
                    @elseif($tipo === 'inventario')
                        <td>{{ $item->nombre }}</td>
                        <td>{{ $item->stock_disponible }}</td>
                        <td>{{ number_format($item->precio_venta, 2) }}</td>
                    @elseif($tipo === 'ventas')
                        <td>{{ date('d/m/Y', strtotime($item->fecha_hora)) }}</td>
                        <td>{{ $item->cliente ? $item->cliente->primer_nombre : 'C. Final' }}</td>
                        <td>{{ $item->metodo_pago }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                    @elseif($tipo === 'taller')
                        <td>{{ $item->nro_orden }}</td>
                        <td>{{ $item->fecha_ingreso ? date('d/m/Y', strtotime($item->fecha_ingreso)) : '—' }}</td>
                        <td>{{ $item->motocicleta ? $item->motocicleta->marca . ' ' . $item->motocicleta->modelo . ' (' . $item->motocicleta->placa . ')' : '—' }}</td>
                        <td>{{ $item->estado }}</td>
                        <td>
                            @forelse($item->detalles->whereNotNull('id_producto') as $detalle)
                                {{ $detalle->producto->nombre ?? 'Repuesto' }} x{{ $detalle->cantidad }}<br>
                            @empty
                                Sin repuestos
                            @endforelse
                        </td>
                    @elseif($tipo === 'caja')
                        <td>{{ date('d/m/Y', strtotime($item->fecha_hora)) }}</td>
                        <td>{{ $item->nro_factura }}</td>
                        <td>{{ $item->concepto_resumen ?: 'Venta de caja' }}</td>
                        <td>{{ $item->metodo_pago }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        NovaRider - Sistema de Gestión de Taller de Motocicletas © {{ date('Y') }}
    </div>
</body>
</html>
