<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compatibilidad de Producto - NovaRider</title>
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

        .producto-info { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 12px 16px; margin-bottom: 18px; }
        .producto-info h2 { font-size: 14px; font-weight: 700; color: #042D29; margin-bottom: 4px; }
        .producto-info p { font-size: 11px; color: #5C5B4E; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #042D29; color: #FFFFFF; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; }
        tbody td { padding: 8px 12px; border-bottom: 1px solid #E5E7EB; font-size: 10px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }

        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #E5E7EB; display: flex; justify-content: space-between; font-size: 9px; color: #929079; }
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
                    <p>Motocicletas compatibles</p>
                </div>
            </div>
            <div class="header-right">
                <div class="report-title">Compatibilidad de Producto</div>
                <div class="report-date">Generado: {{ $fechaGeneracion }}<br>Usuario: {{ $usuarioGenera }}</div>
            </div>
        </div>

        <div class="producto-info">
            <h2>{{ $producto->nombre }}</h2>
            <p>Precio de venta: S/ {{ number_format($producto->precio_venta ?? 0, 2) }} &mdash; Stock disponible: {{ $producto->stock_disponible ?? 0 }} unidades</p>
        </div>

        <p style="font-size: 11px; color: #5C5B4E; margin-bottom: 10px;">Total de motocicletas compatibles: <strong>{{ $total }}</strong></p>

        @if(count($motocicletas) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>A&ntilde;o</th>
                        <th>Color</th>
                        <th>Propietario</th>
                        <th>Tel&eacute;fono</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($motocicletas as $moto)
                        <tr>
                            <td><strong>{{ $moto['placa'] ?? '—' }}</strong></td>
                            <td>{{ $moto['marca'] ?? '—' }}</td>
                            <td>{{ $moto['modelo'] ?? '—' }}</td>
                            <td>{{ $moto['anio'] ?? '—' }}</td>
                            <td>{{ $moto['color'] ?? '—' }}</td>
                            <td>{{ $moto['cliente']['nombre_completo'] ?? '—' }}</td>
                            <td>{{ $moto['cliente']['telefono'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #929079; padding: 40px 0; font-style: italic;">No se encontraron motocicletas compatibles registradas.</p>
        @endif

        <div class="footer">
            <span>NovaRider - Sistema de Gesti&oacute;n de Taller</span>
            <span>P&aacute;gina 1 de 1</span>
        </div>
    </div>
</body>
</html>
