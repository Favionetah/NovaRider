<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\DetalleOrdenTrabajo;
use App\Models\OrdenTrabajo;
use App\Models\Producto;
use App\Models\Servicio;
use App\Traits\AuditoriaTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrdenController extends Controller
{
    use AuditoriaTrait;

    public function index()
    {
        try {
            $ordenes = OrdenTrabajo::with(['motocicleta', 'empleado', 'detalles.producto'])
                ->leftJoin('TListasVerificacion as lv', 'TOrdenesTrabajo.id_orden', '=', 'lv.id_orden')
                ->where('TOrdenesTrabajo.estadoA', true)
                ->orderByDesc('TOrdenesTrabajo.id_orden')
                ->get([
                    'TOrdenesTrabajo.*',
                    DB::raw("
                        CASE
                            WHEN lv.id_lista IS NOT NULL
                                AND (
                                    lv.frenos_revisados = 1
                                    OR lv.luces_revisadas = 1
                                    OR lv.piezas_ajustadas = 1
                                    OR lv.prueba_ruta = 1
                                )
                                AND lv.kilometraje IS NOT NULL
                                AND lv.fecha_validacion IS NOT NULL
                            THEN 1
                            ELSE 0
                        END as validado
                    "),
                ])
                ->map(fn ($orden) => $this->formatearOrden($orden));
            return response()->json(['ordenes' => $ordenes]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al listar ordenes: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_motocicleta' => 'required|exists:TMotocicletas,id_motocicleta',
            'id_empleado' => 'required|exists:TEmpleados,id_empleado',
            'nro_orden' => 'required|string|max:50|unique:TOrdenesTrabajo,nro_orden',
            'fecha_ingreso' => 'required|date',
            'estado' => 'required|string|max:255',
            'condicion_entrada' => 'required|string',
        ]);
        $usuarioId = auth()->id() ?? 1;

        try {
            DB::beginTransaction();
            $orden = OrdenTrabajo::create([
                ...$validated,
                'codigo_seguimiento' => $this->generarCodigoSeguimiento(),
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarHistorialEstado($orden->id_orden, $orden->estado, 'Orden creada', $usuarioId);

            $this->registrarAuditoria('TOrdenesTrabajo', $orden->id_orden, 'I', null, null, $orden->nro_orden, 'Registro de orden');

            DB::commit();
            return response()->json([
                'message' => 'Orden de trabajo guardada exitosamente',
                'orden' => $this->formatearOrden($orden->load(['motocicleta', 'empleado'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar orden: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_motocicleta' => 'required|exists:TMotocicletas,id_motocicleta',
            'id_empleado' => 'required|exists:TEmpleados,id_empleado',
            'fecha_ingreso' => 'required|date',
            'estado' => 'required|string|max:255',
            'condicion_entrada' => 'required|string',
        ]);

        $orden = OrdenTrabajo::findOrFail($id);
        $anterior = $orden->replicate();
        try {
            DB::beginTransaction();
            $orden->update([
                ...$validated,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]);
            $this->registrarAuditoria('TOrdenesTrabajo', $orden->id_orden, 'U', json_encode($anterior), null, $orden->nro_orden, 'Modificacion de orden');

            DB::commit();
            return response()->json([
                'message' => 'Orden de trabajo actualizada exitosamente',
                'orden' => $this->formatearOrden($orden->fresh()->load(['motocicleta', 'empleado'])),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar orden: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $orden = OrdenTrabajo::findOrFail($id);
        $anterior = $orden->replicate();

        try {
            DB::beginTransaction();
            $orden->update([
                'estadoA' => false,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]);
            $this->registrarAuditoria('TOrdenesTrabajo', $orden->id_orden, 'D', json_encode($anterior), null, $orden->nro_orden, 'Eliminacion logica de orden');

            DB::commit();

            return response()->json(['message' => 'Orden de trabajo eliminada exitosamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar orden: ' . $e->getMessage()], 500);
        }
    }

    public function guardarListaVerificacion(Request $request)
    {
        $validated = $request->validate([
            'id_orden' => 'required|exists:TOrdenesTrabajo,id_orden',
            'frenos_revisados' => 'required|boolean',
            'luces_revisadas' => 'required|boolean',
            'piezas_ajustadas' => 'required|boolean',
            'prueba_ruta' => 'required|boolean',
            'kilometraje' => 'required|numeric|min:0',
            'fecha_validacion' => 'required|date',
        ]);
        if (!(
            $validated['frenos_revisados'] ||
            $validated['luces_revisadas'] ||
            $validated['piezas_ajustadas'] ||
            $validated['prueba_ruta']
        )) {
            return response()->json([
                'message' => 'Debe marcar al menos un punto de la lista de verificacion antes de validar la orden.',
            ], 422);
        }

        DB::table('TListasVerificacion')->updateOrInsert(
            ['id_orden' => $validated['id_orden']],
            [
                'frenos_revisados' => $validated['frenos_revisados'],
                'luces_revisadas' => $validated['luces_revisadas'],
                'piezas_ajustadas' => $validated['piezas_ajustadas'],
                'prueba_ruta' => $validated['prueba_ruta'],
                'kilometraje' => $validated['kilometraje'],
                'fecha_validacion' => $validated['fecha_validacion'],
                'fecha' => now(),
                'estadoA' => true,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]
        );
        OrdenTrabajo::where('id_orden', $validated['id_orden'])->update([
            'estado' => 'Listo para entrega',
            'usuarioA' => auth()->id() ?? 1,
            'fechahoraA' => now(),
        ]);
        $this->registrarHistorialEstado($validated['id_orden'], 'Listo para entrega', 'Validacion de calidad completada', auth()->id() ?? 1);

        return response()->json(['success' => true, 'message' => 'Certificacion registrada.']);
    }

    public function obtenerListaVerificacion($id)
    {
        $checklist = DB::table('TListasVerificacion')
            ->where('id_orden', $id)
            ->where('estadoA', true)
            ->first();
        return response()->json($checklist);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:Pendiente,En proceso,Esperando repuestos,Listo para entrega,Entregado',
        ]);
        $orden = OrdenTrabajo::findOrFail($id);
        $estadoAnterior = $orden->estado;
        $orden->update([
            'estado' => $validated['estado'],
            'usuarioA' => auth()->id() ?? 1,
            'fechahoraA' => now(),
        ]);
        if ($estadoAnterior !== $validated['estado']) {
            $this->registrarHistorialEstado($orden->id_orden, $validated['estado'], 'Cambio manual de estado', auth()->id() ?? 1);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'orden' => $this->formatearOrden($orden->fresh()->load(['motocicleta', 'empleado'])),
        ]);
    }

    public function seguimientoPublico($codigo)
    {
        $orden = OrdenTrabajo::with(['motocicleta.cliente', 'empleado', 'detalles.servicio', 'detalles.producto'])
            ->where('codigo_seguimiento', $codigo)
            ->where('estadoA', true)
            ->first();
        if (!$orden) {
            return response()->json(['message' => 'No se encontro una orden con ese codigo de seguimiento.'], 404);
        }

        $historial = DB::table('THistorialesEstado')
            ->where('id_orden', $orden->id_orden)
            ->where('estadoA', true)
            ->orderBy('fecha_hora_cambio')
            ->get(['fecha_hora_cambio', 'estado_asignado', 'observacion'])
            ->map(fn ($item) => [
                'fecha_hora_cambio' => $item->fecha_hora_cambio,
                'estado' => $this->normalizarEstado($item->estado_asignado),
                'observacion' => $item->observacion,
            ]);
        if ($historial->isEmpty()) {
            $historial->push([
                'fecha_hora_cambio' => $orden->fechahoraA,
                'estado' => $this->normalizarEstado($orden->estado),
                'observacion' => 'Orden registrada',
            ]);
        }

        $motocicleta = $orden->motocicleta;
        $cliente = $motocicleta?->cliente;
        return response()->json([
            'orden' => [
                'codigo_seguimiento' => $orden->codigo_seguimiento,
                'nro_orden' => $orden->nro_orden,
                'fecha_ingreso' => $orden->fecha_ingreso,
                'estado' => $this->normalizarEstado($orden->estado),
                'condicion_entrada' => $orden->condicion_entrada ?? '',
                'motocicleta' => $motocicleta ? [
                    'placa' => $motocicleta->placa,
                    'marca' => $motocicleta->marca,
                    'modelo' => $motocicleta->modelo,
                    'color' => $motocicleta->color,
                ] : null,
                'cliente' => $cliente ? [
                    'nombre' => trim($cliente->primer_nombre . ' ' . $cliente->apellido_paterno),
                ] : null,
                'mecanico' => $orden->empleado ? trim($orden->empleado->primer_nombre . ' ' . $orden->empleado->apellido_paterno) : null,
                'servicios' => $orden->detalles
                    ->where('estadoA', true)
                    ->whereNotNull('id_servicio')
                    ->map(fn ($detalle) => [
                        'descripcion' => $detalle->descripcion,
                        'cantidad' => $detalle->cantidad,
                        'servicio' => $detalle->servicio?->nombre,
                    ])
                    ->values(),
                'repuestos' => $orden->detalles
                    ->where('estadoA', true)
                    ->whereNotNull('id_producto')
                    ->map(fn ($detalle) => [
                        'producto' => $detalle->producto?->nombre,
                        'descripcion' => $detalle->descripcion,
                        'cantidad' => (int) $detalle->cantidad,
                        'costo_unitario' => (float) ($detalle->costo_unitario ?? 0),
                        'subtotal' => (float) ($detalle->subtotal ?? 0),
                    ])
                    ->values(),
                'historial' => $historial,
            ],
        ]);
    }

    public function servicios()
    {
        $servicios = Servicio::where('estadoA', true)
            ->orderBy('nombre')
            ->get(['id_servicio', 'nombre', 'descripcion', 'precio_estimado']);
        return response()->json($servicios);
    }

    public function repuestosDisponibles()
    {
        $productos = Producto::where('estadoA', true)
            ->where('stock_disponible', '>', 0)
            ->orderBy('nombre')
            ->get(['id_producto', 'nombre', 'descripcion', 'costo', 'precio_venta', 'stock_disponible']);
        return response()->json(['repuestos' => $productos]);
    }

    public function guardarServicio(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'precio_estimado' => 'required|numeric|min:0',
        ]);
        try {
            DB::beginTransaction();
            $servicio = Servicio::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? '',
                'precio_estimado' => $validated['precio_estimado'],
                'estadoA' => true,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]);
            $this->registrarAuditoria(
                'TServicios',
                $servicio->id_servicio,
                'I',
                null,
                null,
                $servicio->nombre,
                'Registro de servicio'
            );
            DB::commit();

            return response()->json([
                'message' => 'Servicio registered correctamente.',
                'servicio' => $servicio,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar servicio: ' . $e->getMessage()], 500);
        }
    }

    public function guardarServicioOrden(Request $request, $id)
    {
        $orden = OrdenTrabajo::where('id_orden', $id)
            ->where('estadoA', true)
            ->firstOrFail();
        $validated = $request->validate([
            'id_servicio' => 'required|exists:TServicios,id_servicio',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $servicio = Servicio::findOrFail($validated['id_servicio']);
        $precioLabor = (float) ($servicio->precio_estimado ?? 0);
        $subtotal = $precioLabor * (int) $validated['cantidad'];
        try {
            DB::beginTransaction();
            $detalle = DetalleOrdenTrabajo::create([
                'id_orden' => $id,
                'id_servicio' => $servicio->id_servicio,
                'descripcion' => $validated['descripcion'] ?? $servicio->descripcion,
                'cantidad' => $validated['cantidad'],
                'precio_labor' => $precioLabor,
                'subtotal' => $subtotal,
                'estadoA' => true,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]);
            $this->registrarAuditoria(
                'TDetallesOrdenTrabajo',
                $detalle->id_detalle_ot,
                'I',
                null,
                null,
                $servicio->nombre . '|' . $validated['cantidad'] . '|' . $subtotal,
                'Registro de servicio en orden #' . $id
            );
            DB::commit();

            return response()->json([
                'message' => 'Servicio agregado a la orden correctamente.',
                'detalle' => $detalle->load('servicio'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar servicio: ' . $e->getMessage()], 500);
        }
    }

    public function guardarRepuestoOrden(Request $request, $id)
    {
        // CORRECCIÓN AQUÍ: Se asigna el resultado de la consulta a la variable $orden
        $orden = OrdenTrabajo::where('id_orden', $id)
            ->where('estadoA', true)
            ->firstOrFail();
        $validated = $request->validate([
            'id_producto' => 'required|exists:TProductos,id_producto',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $producto = Producto::where('estadoA', true)->findOrFail($validated['id_producto']);
        $cantidad = (int) $validated['cantidad'];

        if ((int) $producto->stock_disponible < $cantidad) {
            return response()->json([
                'message' => 'Stock insuficiente para registrar este repuesto en la orden.',
            ], 422);
        }

        $costoUnitario = (float) ($producto->costo ?? $producto->precio_venta ?? 0);
        $subtotal = $costoUnitario * $cantidad;

        try {
            DB::beginTransaction();
            $detalle = DetalleOrdenTrabajo::create([
                'id_orden' => $id,
                'id_servicio' => null,
                'id_producto' => $producto->id_producto,
                'descripcion' => $validated['descripcion'] ?? $producto->nombre,
                'cantidad' => $cantidad,
                'precio_labor' => 0,
                'costo_unitario' => $costoUnitario,
                'subtotal' => $subtotal,
                'estadoA' => true,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]);
            $producto->update([
                'stock_disponible' => (int) $producto->stock_disponible - $cantidad,
                'usuarioA' => auth()->id() ?? 1,
                'fechahoraA' => now(),
            ]);
            
            // Ahora $orden->estado sí funcionará sin romper la ejecución
            $this->registrarHistorialEstado($id, $orden->estado, 'Repuesto agregado: ' . $producto->nombre . ' x' . $cantidad, auth()->id() ?? 1);

            DB::commit();
            return response()->json([
                'message' => 'Repuesto agregado a la orden correctamente.',
                'detalle' => $detalle->load('producto'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar repuesto: ' . $e->getMessage()], 500);
        }
    }

    public function reportePdf(Request $request)
    {
        $busqueda = $request->input('busqueda', '');
        $estado = $request->input('estado', '');
        $idMecanico = $request->input('id_mecanico', '');

        $query = OrdenTrabajo::with(['motocicleta.cliente', 'empleado'])
            ->where('estadoA', true)
            ->orderBy('fecha_ingreso', 'DESC');
        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($idMecanico) {
            $query->where('id_empleado', $idMecanico);
        }

        if ($busqueda) {
            $q = strtolower($busqueda);
            $query->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(nro_orden) LIKE ?', ["%{$q}%"])
                    ->orWhereHas('motocicleta', function ($sub) use ($q) {
                        $sub->whereRaw('LOWER(placa) LIKE ?', ["%{$q}%"])
                            ->orWhereHas('cliente', function ($clienteSub) use ($q) {
                                $clienteSub->whereRaw("LOWER(CONCAT(primer_nombre, ' ', apellido_paterno)) LIKE ?", ["%{$q}%"]);
                            });
                    });
            });
        }

        $ordenes = $query->get();
        $mecanicoNombre = 'Todos';
        if ($idMecanico) {
            $mecanico = Empleado::find($idMecanico);
            $mecanicoNombre = $mecanico ? $mecanico->primer_nombre . ' ' . $mecanico->apellido_paterno : 'Todos';
        }

        $logoPath = public_path('img/Logo3_NovaRider.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $pdf = Pdf::loadView('reportes.ordenes_pdf', [
            'ordenes' => $ordenes,
            'filtros' => [
                'busqueda' => $busqueda,
                'estado' => $estado ? ucfirst($estado) : 'Todos',
                'mecanico' => $mecanicoNombre,
            ],
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'usuarioGenera' => auth()->user()->username ?? 'Sistema',
            'logoBase64' => $logoBase64,
            'totalRegistros' => $ordenes->count(),
        ]);
        $filename = 'reporte_ordenes_' . now()->format('Y-m-d_His') . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    private function formatearOrden(OrdenTrabajo $orden)
    {
        return [
            'id_orden' => $orden->id_orden,
            'nro_orden' => $orden->nro_orden,
            'codigo_seguimiento' => $orden->codigo_seguimiento,
            'fecha_ingreso' => $orden->fecha_ingreso,
            'estado' => $this->normalizarEstado($orden->estado),
            'condicion_entrada' => $orden->condicion_entrada ?? '',
            'id_motocicleta' => $orden->id_motocicleta,
            'id_empleado' => $orden->id_empleado,
            'validado' => (int) ($orden->validado ?? 0),
            'motocicleta' => $orden->motocicleta ? [
                'id_motocicleta' => $orden->motocicleta->id_motocicleta,
                'placa' => $orden->motocicleta->placa,
                'marca' => $orden->motocicleta->marca,
                'modelo' => $orden->motocicleta->modelo,
            ] : null,
            'empleado' => $orden->empleado ? [
                'id_empleado' => $orden->empleado->id_empleado,
                'primer_nombre' => $orden->empleado->primer_nombre,
                'apellido_paterno' => $orden->empleado->apellido_paterno,
            ] : null,
            'repuestos' => $orden->detalles ? $orden->detalles
                    ->where('estadoA', true)
                    ->whereNotNull('id_producto')
                    ->map(fn ($detalle) => [
                        'id_detalle_ot' => $detalle->id_detalle_ot,
                        'producto' => $detalle->producto?->nombre,
                        'cantidad' => (int) $detalle->cantidad,
                        'costo_unitario' => (float) ($detalle->costo_unitario ?? 0),
                        'subtotal' => (float) ($detalle->subtotal ?? 0),
                    ])
                    ->values()
                : [],
        ];
    }

    private function generarCodigoSeguimiento(): string
    {
        do {
            $codigo = 'NR-' . strtoupper(Str::random(8));
        } while (OrdenTrabajo::where('codigo_seguimiento', $codigo)->exists());

        return $codigo;
    }

    private function registrarHistorialEstado(int $idOrden, ?string $estado, string $observacion, ?int $usuarioId): void
    {
        DB::table('THistorialesEstado')->insert([
            'id_orden' => $idOrden,
            'fecha_hora_cambio' => now(),
            'estado_asignado' => $estado,
            'observacion' => $observacion,
            'estadoA' => true,
            'usuarioA' => $usuarioId,
            'fechahoraA' => now(),
        ]);
    }

    private function normalizarEstado(?string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'completada' => 'Listo para entrega',
            default => $estado ?? 'Pendiente',
        };
    }
}