<?php

namespace App\Http\Controllers;

use App\Models\DetalleReserva;
use App\Models\Envio;
use App\Models\Producto;
use App\Models\Reserva;
use App\Traits\AuditoriaTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $query = Reserva::with('cliente', 'detalles.producto', 'envio')
            ->where('estadoA', true)
            ->orderBy('fecha_solicitud', 'desc')
            ->orderBy('id_reserva', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('busqueda')) {
            $q = $request->busqueda;
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('cliente', function ($c) use ($q) {
                    $c->where('primer_nombre', 'like', "%{$q}%")
                      ->orWhere('apellido_paterno', 'like', "%{$q}%")
                      ->orWhere('ci', 'like', "%{$q}%");
                })->orWhere('id_reserva', $q);
            });
        }

        $reservas = $query->get()->map(fn($r) => $this->formatearReserva($r));

        return response()->json(['reservas' => $reservas]);
    }

    public function show($id)
    {
        $reserva = Reserva::with('cliente', 'detalles.producto', 'envio')->findOrFail($id);
        return response()->json(['reserva' => $this->formatearReserva($reserva)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:TClientes,id_cliente',
            'monto_adelanto' => 'required|numeric|min:1',
            'adelanto_metodo_pago' => 'required|in:QR,Efectivo',
            'fecha_expiracion' => 'nullable|date|after:today',
            'departamento_origen' => 'nullable|string|max:255',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:TProductos,id_producto',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $reserva = Reserva::create([
                'id_cliente' => $validated['id_cliente'],
                'monto_adelanto' => $validated['monto_adelanto'] ?? 0,
                'adelanto_metodo_pago' => $validated['adelanto_metodo_pago'] ?? null,
                'fecha_solicitud' => now()->toDateString(),
                'fecha_expiracion' => $validated['fecha_expiracion'],
                'estado' => 'pendiente',
                'departamento_origen' => $validated['departamento_origen'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            foreach ($validated['detalles'] as $d) {
                DetalleReserva::create([
                    'id_reserva' => $reserva->id_reserva,
                    'id_producto' => $d['id_producto'],
                    'cantidad_reservada' => $d['cantidad'],
                    'estadoA' => true,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);

                $producto = Producto::findOrFail($d['id_producto']);
                $producto->update([
                    'stock_disponible' => $producto->stock_disponible - $d['cantidad'],
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);
            }

            $valores = implode('|', [
                'cliente:' . $validated['id_cliente'],
                'productos:' . count($validated['detalles']),
                'adelanto:' . ($validated['monto_adelanto'] ?? 0),
            ]);
            $this->registrarAuditoria('TReservas', $reserva->id_reserva, 'I', null, null, $valores, 'Creacion de reserva');

            DB::commit();

            $reserva->load('cliente', 'detalles.producto', 'envio');

            return response()->json([
                'message' => 'Reserva creada exitosamente',
                'reserva' => $this->formatearReserva($reserva),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear reserva: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $validated = $request->validate([
            'fecha_expiracion' => 'nullable|date',
            'departamento_origen' => 'nullable|string|max:255',
            'estado' => 'nullable|in:pendiente,completada,cancelada',
        ]);

        $usuarioId = auth()->id();
        $campos = [];
        $valoresAnt = [];
        $valoresNue = [];

        if ($request->has('fecha_expiracion') && $reserva->fecha_expiracion != $validated['fecha_expiracion']) {
            $campos[] = 'fecha_expiracion';
            $valoresAnt[] = $reserva->fecha_expiracion;
            $valoresNue[] = $validated['fecha_expiracion'];
            $reserva->fecha_expiracion = $validated['fecha_expiracion'];
        }

        if ($request->has('departamento_origen') && $reserva->departamento_origen != $validated['departamento_origen']) {
            $campos[] = 'departamento_origen';
            $valoresAnt[] = $reserva->departamento_origen;
            $valoresNue[] = $validated['departamento_origen'];
            $reserva->departamento_origen = $validated['departamento_origen'];
        }

        if ($request->has('estado') && $reserva->estado != $validated['estado']) {
            $campos[] = 'estado';
            $valoresAnt[] = $reserva->estado;
            $valoresNue[] = $validated['estado'];
            $reserva->estado = $validated['estado'];
        }

        if (empty($campos)) {
            return response()->json(['message' => 'No hay campos para actualizar'], 422);
        }

        $reserva->usuarioA = $usuarioId;
        $reserva->fechahoraA = now();
        $reserva->save();

        $this->registrarAuditoria('TReservas', $id, 'U',
            implode('|', $campos),
            implode('|', $valoresAnt),
            implode('|', $valoresNue),
            'Actualizacion de reserva'
        );

        $reserva->load('cliente', 'detalles.producto', 'envio');

        return response()->json([
            'message' => 'Reserva actualizada',
            'reserva' => $this->formatearReserva($reserva),
        ]);
    }

    public function destroy($id)
    {
        $reserva = Reserva::with('detalles.producto')->findOrFail($id);

        if ($reserva->estado !== 'pendiente') {
            return response()->json(['message' => 'Solo se pueden cancelar reservas pendientes'], 422);
        }

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            foreach ($reserva->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto) {
                    $producto->update([
                        'stock_disponible' => $producto->stock_disponible + $detalle->cantidad_reservada,
                        'usuarioA' => $usuarioId,
                        'fechahoraA' => now(),
                    ]);
                }
            }

            $reserva->update([
                'estado' => 'cancelada',
                'estadoA' => false,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria('TReservas', $id, 'U', 'estadoA', '1', '0', 'Cancelacion de reserva');

            DB::commit();

            return response()->json(['message' => 'Reserva cancelada exitosamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al cancelar reserva: ' . $e->getMessage()], 500);
        }
    }

    public function convertirVenta(Request $request, $id)
    {
        $reserva = Reserva::with('detalles.producto', 'cliente')->findOrFail($id);

        if ($reserva->estado !== 'pendiente') {
            return response()->json(['message' => 'La reserva debe estar pendiente para convertirla a venta'], 422);
        }

        $validated = $request->validate([
            'metodo_pago' => 'required|in:QR,Efectivo',
            'descuento' => 'nullable|numeric|min:0',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $detallesVenta = [];

            foreach ($reserva->detalles as $detalle) {
                $producto = $detalle->producto;
                $precioUnitario = $producto->precio_venta ?? 0;
                $subtotalDetalle = $detalle->cantidad_reservada * $precioUnitario;
                $subtotal += $subtotalDetalle;

                $detallesVenta[] = [
                    'id_producto' => $detalle->id_producto,
                    'cantidad' => $detalle->cantidad_reservada,
                    'precio_unitario_historico' => $precioUnitario,
                    'subtotal' => $subtotalDetalle,
                    'estadoA' => true,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ];

                $producto->update([
                    'stock_fisico' => $producto->stock_fisico - $detalle->cantidad_reservada,
                    'stock_disponible' => $producto->stock_disponible + $detalle->cantidad_reservada,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);
            }

            $descuento = $validated['descuento'] ?? 0;
            $total = $subtotal - $descuento;

            $idVenta = DB::table('TVentas')->insertGetId([
                'id_cliente' => $reserva->id_cliente,
                'id_empleado' => null,
                'id_caja' => null,
                'nro_factura' => null,
                'fecha_hora' => now(),
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'metodo_pago' => $validated['metodo_pago'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            foreach ($detallesVenta as $dv) {
                $dv['id_venta'] = $idVenta;
                DB::table('TDetallesVenta')->insert($dv);
            }

            DB::table('TReservasVentas')->insert([
                'id_reserva' => $reserva->id_reserva,
                'id_venta' => $idVenta,
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $reserva->update([
                'estado' => 'completada',
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria('TReservas', $id, 'U', 'estado', 'pendiente', 'completada', 'Conversion de reserva a venta #' . $idVenta);

            DB::commit();

            return response()->json([
                'message' => 'Reserva convertida a venta exitosamente',
                'id_venta' => $idVenta,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al convertir reserva: ' . $e->getMessage()], 500);
        }
    }

    public function registrarEnvio(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        if ($reserva->estado !== 'completada' && $reserva->estado !== 'pendiente') {
            return response()->json(['message' => 'La reserva debe estar pendiente o completada para registrar envio'], 422);
        }

        if ($reserva->envio) {
            return response()->json(['message' => 'La reserva ya tiene un envio registrado'], 422);
        }

        $validated = $request->validate([
            'empresa_transporte' => 'required|string|max:255',
            'nro_guia' => 'required|string|max:255',
            'fecha_despacho' => 'required|date',
            'estado_envio' => 'nullable|string|max:255',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $envio = Envio::create([
                'id_reserva' => $reserva->id_reserva,
                'empresa_transporte' => $validated['empresa_transporte'],
                'nro_guia' => $validated['nro_guia'],
                'fecha_despacho' => $validated['fecha_despacho'],
                'estado_envio' => $validated['estado_envio'] ?? 'en_transito',
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $reserva->update([
                'estado' => 'enviado',
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria('TEnvios', $envio->id_envio, 'I', null, null,
                implode('|', [$validated['empresa_transporte'], $validated['nro_guia']]),
                'Registro de envio para reserva #' . $reserva->id_reserva
            );

            DB::commit();

            return response()->json([
                'message' => 'Envio registrado exitosamente',
                'envio' => $envio,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar envio: ' . $e->getMessage()], 500);
        }
    }

    public function reportePdf(Request $request)
    {
        $busqueda = $request->input('busqueda', '');
        $estado = $request->input('estado', '');

        $query = Reserva::with('cliente', 'detalles.producto', 'envio')
            ->where('estadoA', true)
            ->orderBy('fecha_solicitud', 'desc')
            ->orderBy('id_reserva', 'desc');

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($busqueda) {
            $q = $busqueda;
            $query->where(function ($sub) use ($q) {
                $sub->where('id_reserva', $q)
                    ->orWhereHas('cliente', function ($clientQ) use ($q) {
                        $clientQ->whereRaw("LOWER(CONCAT(primer_nombre, ' ', IFNULL(segundo_nombre,''), ' ', apellido_paterno, ' ', IFNULL(apellido_materno,''))) LIKE ?", ["%{$q}%"])
                            ->orWhereRaw("LOWER(ci) LIKE ?", ["%{$q}%"]);
                    });
            });
        }

        $reservas = $query->get()->map(fn($r) => $this->formatearReserva($r));

        $totalAdelantos = $reservas->sum('monto_adelanto');
        $reservasPorEstado = $reservas->groupBy('estado')->map(fn($g) => $g->count())->toArray();

        $filtros = [
            'busqueda' => $busqueda,
            'estado' => $estado ?: 'Todos',
        ];

        $logoPath = public_path('img/Logo3_NovaRider.png');
        $logoExists = file_exists($logoPath);
        $logoBase64 = $logoExists ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $pdf = Pdf::loadView('reportes.reservas_pdf', [
            'reservas' => $reservas,
            'filtros' => $filtros,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'usuarioGenera' => auth()->user()->username ?? 'Sistema',
            'logoBase64' => $logoBase64,
            'totalRegistros' => $reservas->count(),
            'totalAdelantos' => $totalAdelantos,
            'reservasPorEstado' => $reservasPorEstado,
        ]);

        $filename = 'reporte_reservas_' . now()->format('Y-m-d_His') . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    private function formatearReserva(Reserva $reserva)
    {
        return [
            'id_reserva' => $reserva->id_reserva,
            'id_cliente' => $reserva->id_cliente,
            'cliente' => $reserva->cliente ? [
                'id_cliente' => $reserva->cliente->id_cliente,
                'nombre_completo' => trim(implode(' ', array_filter([
                    $reserva->cliente->primer_nombre,
                    $reserva->cliente->segundo_nombre,
                    $reserva->cliente->apellido_paterno,
                    $reserva->cliente->apellido_materno,
                ]))),
                'ci' => $reserva->cliente->ci,
                'telefono' => $reserva->cliente->telefono,
            ] : null,
            'monto_adelanto' => (float) $reserva->monto_adelanto,
            'adelanto_metodo_pago' => $reserva->adelanto_metodo_pago,
            'fecha_solicitud' => $reserva->fecha_solicitud,
            'fecha_expiracion' => $reserva->fecha_expiracion,
            'estado' => $reserva->estado,
            'departamento_origen' => $reserva->departamento_origen,
            'detalles' => $reserva->detalles->map(fn($d) => [
                'id_detalle_reserva' => $d->id_detalle_reserva,
                'id_producto' => $d->id_producto,
                'producto' => $d->producto ? [
                    'id_producto' => $d->producto->id_producto,
                    'nombre' => $d->producto->nombre,
                    'precio_venta' => (float) $d->producto->precio_venta,
                ] : null,
                'cantidad_reservada' => $d->cantidad_reservada,
            ]),
            'envio' => $reserva->envio ? [
                'id_envio' => $reserva->envio->id_envio,
                'empresa_transporte' => $reserva->envio->empresa_transporte,
                'nro_guia' => $reserva->envio->nro_guia,
                'fecha_despacho' => $reserva->envio->fecha_despacho,
                'estado_envio' => $reserva->envio->estado_envio,
            ] : null,
        ];
    }
}
