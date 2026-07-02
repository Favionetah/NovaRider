<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Motocicleta;
use App\Models\Producto;
use App\Models\ModelosCompatible;
use App\Traits\AuditoriaTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $inactivos = $request->boolean('inactivos');

        $query = Producto::with('ubicacion.seccion.estante')
            ->orderBy('nombre');

        if ($inactivos) {
            $query->where('estadoA', false);
        } else {
            $query->where('estadoA', true);
        }

        $productos = $query->get()->map(fn($p) => $this->formatearProducto($p));

        return response()->json(['productos' => $productos]);
    }

    public function show($id)
    {
        $producto = Producto::with('ubicacion.seccion.estante', 'modelosCompatibles')->findOrFail($id);
        return response()->json(['producto' => $this->formatearProducto($producto)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_ubicacion' => 'nullable|exists:TUbicaciones,id_ubicacion',
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:100',
            'precio_venta' => 'nullable|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'stock_fisico' => 'nullable|integer|min:0',
            'stock_disponible' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $producto = Producto::create([
                'id_ubicacion' => $validated['id_ubicacion'],
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'precio_venta' => $validated['precio_venta'] ?? 0,
                'costo' => $validated['costo'] ?? 0,
                'stock_fisico' => $validated['stock_fisico'] ?? 0,
                'stock_disponible' => $validated['stock_disponible'] ?? ($validated['stock_fisico'] ?? 0),
                'stock_minimo' => $validated['stock_minimo'] ?? 0,
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valores = implode('|', [
                $validated['nombre'],
                $validated['descripcion'] ?? '',
                (string)($validated['precio_venta'] ?? 0),
                (string)($validated['costo'] ?? 0),
                (string)($validated['stock_fisico'] ?? 0),
            ]);
            $this->registrarAuditoria('TProductos', $producto->id_producto, 'I', null, null, $valores, 'Creacion de producto');

            DB::commit();

            $producto->load('ubicacion.seccion.estante');

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'producto' => $this->formatearProducto($producto),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear producto: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'id_ubicacion' => 'nullable|exists:TUbicaciones,id_ubicacion',
            'nombre' => 'sometimes|string|max:50',
            'descripcion' => 'nullable|string|max:100',
            'precio_venta' => 'nullable|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'stock_fisico' => 'nullable|integer|min:0',
            'stock_disponible' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $camposAudit = [];
            $valoresAnt = [];
            $valoresNue = [];

            $camposEditables = [
                'id_ubicacion', 'nombre', 'descripcion', 'precio_venta',
                'costo', 'stock_fisico', 'stock_disponible', 'stock_minimo',
            ];

            $datosActualizar = [];

            foreach ($camposEditables as $campo) {
                if ($request->has($campo)) {
                    $nuevoValor = $request->$campo;
                    $valorActual = $producto->$campo;
                    if ((string)$nuevoValor !== (string)$valorActual) {
                        $datosActualizar[$campo] = $nuevoValor;
                        $camposAudit[] = $campo;
                        $valoresAnt[] = (string)($valorActual ?? '');
                        $valoresNue[] = (string)($nuevoValor ?? '');
                    }
                }
            }

            if (!empty($datosActualizar)) {
                $datosActualizar['usuarioA'] = $usuarioId;
                $datosActualizar['fechahoraA'] = now();
                $producto->update($datosActualizar);

                $this->registrarAuditoria(
                    'TProductos',
                    $producto->id_producto,
                    'U',
                    implode('|', $camposAudit),
                    implode('|', $valoresAnt),
                    implode('|', $valoresNue),
                    'Actualizacion de producto'
                );
            }

            DB::commit();

            $producto->fresh()->load('ubicacion.seccion.estante');

            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'producto' => $this->formatearProducto($producto),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar producto: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->update([
            'estadoA' => false,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TProductos', $producto->id_producto, 'U', 'estadoA', '1', '0', 'Desactivacion de producto');

        return response()->json(['message' => 'Producto desactivado exitosamente']);
    }

    public function reactivar($id)
    {
        $producto = Producto::where('estadoA', false)->findOrFail($id);

        $producto->update([
            'estadoA' => true,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TProductos', $producto->id_producto, 'U', 'estadoA', '0', '1', 'Reactivacion de producto');

        return response()->json([
            'message' => 'Producto reactivado exitosamente',
            'producto' => $this->formatearProducto($producto->fresh()->load('ubicacion.seccion.estante')),
        ]);
    }

    public function modelos($id)
    {
        $producto = Producto::with('modelosCompatibles')->findOrFail($id);
        return response()->json([
            'modelos' => $producto->modelosCompatibles->map(fn($m) => [
                'id_modelo' => $m->id_modelo,
                'marca_moto' => $m->marca_moto,
                'modelo_moto' => $m->modelo_moto,
                'anio_inicio' => $m->anio_inicio,
                'anio_fin' => $m->anio_fin,
            ]),
        ]);
    }

    public function modelosSync(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'modelos' => 'present|array',
            'modelos.*.marca' => 'required|string',
            'modelos.*.modelo' => 'required|string',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $modeloIds = [];
            foreach ($validated['modelos'] as $m) {
                $modelo = ModelosCompatible::firstOrCreate(
                    ['marca_moto' => $m['marca'], 'modelo_moto' => $m['modelo']],
                    ['anio_inicio' => null, 'anio_fin' => null, 'estadoA' => true, 'usuarioA' => $usuarioId, 'fechahoraA' => now()]
                );
                $modeloIds[] = $modelo->id_modelo;
            }

            $producto->modelosCompatibles()->sync($modeloIds);

            $producto->update([
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $modelosStr = implode(',', $validated['modelos']);
            $this->registrarAuditoria(
                'TProductosModelosCompatibles',
                $producto->id_producto,
                'U',
                'id_modelo',
                null,
                $modelosStr,
                'Sincronizacion de modelos compatibles'
            );

            DB::commit();

            return response()->json([
                'message' => 'Modelos compatibles actualizados exitosamente',
                'modelos' => $producto->modelosCompatibles()->get()->map(fn($m) => [
                    'id_modelo' => $m->id_modelo,
                    'marca_moto' => $m->marca_moto,
                    'modelo_moto' => $m->modelo_moto,
                    'anio_inicio' => $m->anio_inicio,
                    'anio_fin' => $m->anio_fin,
                ]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al sincronizar modelos: ' . $e->getMessage()], 500);
        }
    }

    public function motocicletasCompatibles($id)
    {
        $producto = Producto::with('modelosCompatibles')->findOrFail($id);

        $modelosCompatibles = $producto->modelosCompatibles;

        if ($modelosCompatibles->isEmpty()) {
            return response()->json(['motocicletas' => []]);
        }

        $motocicletas = collect();
        foreach ($modelosCompatibles as $modelo) {
            $results = Motocicleta::with('cliente')
                ->where('estadoA', true)
                ->where('marca', $modelo->marca_moto)
                ->where('modelo', $modelo->modelo_moto)
                ->get();
            $motocicletas = $motocicletas->concat($results);
        }

        $motocicletas = $motocicletas->unique('id_motocicleta')->values();

        return response()->json([
            'motocicletas' => $motocicletas->map(fn($m) => [
                'id_motocicleta' => $m->id_motocicleta,
                'placa' => $m->placa,
                'marca' => $m->marca,
                'modelo' => $m->modelo,
                'anio' => $m->anio,
                'color' => $m->color,
                'cliente' => $m->cliente ? [
                    'id_cliente' => $m->cliente->id_cliente,
                    'nombre_completo' => trim(
                        ($m->cliente->primer_nombre ?? '') . ' ' .
                        ($m->cliente->segundo_nombre ?? '') . ' ' .
                        ($m->cliente->apellido_paterno ?? '') . ' ' .
                        ($m->cliente->apellido_materno ?? '')
                    ),
                    'telefono' => $m->cliente->telefono,
                ] : null,
            ]),
            'total' => $motocicletas->count(),
        ]);
    }

    public function motocicletasCompatiblesPdf($id)
    {
        $producto = Producto::findOrFail($id);
        $response = $this->motocicletasCompatibles($id);
        $data = $response->getData(true);
        $motocicletas = $data['motocicletas'];
        $total = $data['total'];

        $logoPath = public_path('img/Logo3_NovaRider.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $pdf = Pdf::loadView('reportes.producto_compatibilidad_pdf', [
            'producto' => $producto,
            'motocicletas' => $motocicletas,
            'total' => $total,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'usuarioGenera' => auth()->user()->username ?? 'Sistema',
            'logoBase64' => $logoBase64,
        ]);

        $filename = 'compatibilidad_' . str_replace(' ', '_', $producto->nombre) . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function modelosDesdeMotocicletas()
    {
        $modelos = \DB::table('TMotocicletas')
            ->where('estadoA', true)
            ->select('marca', 'modelo')
            ->groupBy('marca', 'modelo')
            ->orderBy('marca')
            ->orderBy('modelo')
            ->get();

        return response()->json(['modelos' => $modelos]);
    }

    private function formatearProducto(Producto $producto)
    {
        $ubicacion = $producto->ubicacion;
        $dataUbicacion = null;

        if ($ubicacion) {
            $seccion = $ubicacion->seccion;
            $estante = $seccion ? $seccion->estante : null;

            $dataUbicacion = [
                'id_ubicacion' => $ubicacion->id_ubicacion,
                'nivel' => $ubicacion->nivel,
                'id_seccion' => $seccion ? $seccion->id_seccion : null,
                'codigo_seccion' => $seccion ? $seccion->codigo_seccion : null,
                'id_estante' => $estante ? $estante->id_estante : null,
                'numero_estante' => $estante ? $estante->numero_estante : null,
                'pasillo' => $estante ? $estante->pasillo : null,
            ];
        }

        $tieneAlerta = $producto->stock_disponible !== null
            && $producto->stock_minimo !== null
            && $producto->stock_disponible <= $producto->stock_minimo;

        $ultimoCostoCompra = DetalleCompra::where('id_producto', $producto->id_producto)
            ->orderBy('fechahoraA', 'desc')
            ->value('precio_unitario_compra');

        return [
            'id_producto' => $producto->id_producto,
            'id_ubicacion' => $producto->id_ubicacion,
            'ubicacion' => $dataUbicacion,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'precio_venta' => (float) ($producto->precio_venta ?? 0),
            'costo' => (float) ($producto->costo ?? 0),
            'ultimo_costo_compra' => $ultimoCostoCompra ? (float) $ultimoCostoCompra : null,
            'stock_fisico' => (int) ($producto->stock_fisico ?? 0),
            'stock_disponible' => (int) ($producto->stock_disponible ?? 0),
            'stock_minimo' => (int) ($producto->stock_minimo ?? 0),
            'alerta_stock' => $tieneAlerta,
            'estadoA' => $producto->estadoA,
        ];
    }
}
