<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Traits\AuditoriaTrait;
<<<<<<< HEAD
=======
use Barryvdh\DomPDF\Facade\Pdf;
>>>>>>> respaldo-caja
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $compras = Compra::with('proveedor', 'detalles.producto')
            ->orderBy('fecha', 'desc')
            ->orderBy('id_compra', 'desc')
            ->get()
            ->map(function ($c) {
                return $this->formatearCompra($c);
            });

        return response()->json(['compras' => $compras]);
    }

    public function show($id)
    {
        $compra = Compra::with('proveedor', 'detalles.producto')->findOrFail($id);
        return response()->json(['compra' => $this->formatearCompra($compra)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_proveedor' => 'required|exists:TProveedores,id_proveedor',
            'fecha' => 'required|date',
            'nro_factura_proveedor' => 'nullable|string|max:255',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:TProductos,id_producto',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario_compra' => 'required|numeric|min:0',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $total = 0;
            foreach ($validated['detalles'] as $d) {
                $total += $d['cantidad'] * $d['precio_unitario_compra'];
            }

            $compra = Compra::create([
                'id_proveedor' => $validated['id_proveedor'],
                'fecha' => $validated['fecha'],
                'nro_factura_proveedor' => $validated['nro_factura_proveedor'],
                'total_compra' => $total,
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $detallesCreados = [];
            foreach ($validated['detalles'] as $d) {
                $subtotal = $d['cantidad'] * $d['precio_unitario_compra'];

                $detalle = DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $d['id_producto'],
                    'cantidad' => $d['cantidad'],
                    'precio_unitario_compra' => $d['precio_unitario_compra'],
                    'subtotal' => $subtotal,
                    'estadoA' => true,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);

                $producto = Producto::findOrFail($d['id_producto']);
                $producto->update([
                    'stock_fisico' => $producto->stock_fisico + $d['cantidad'],
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);

                $detallesCreados[] = $detalle;
            }

            $valores = implode('|', [
                'proveedor:' . $validated['id_proveedor'],
                'fecha:' . $validated['fecha'],
                'total:' . $total,
                'productos:' . count($validated['detalles']),
            ]);
            $this->registrarAuditoria('TCompras', $compra->id_compra, 'I', null, null, $valores, 'Creacion de compra con ' . count($validated['detalles']) . ' productos');

            DB::commit();

            $compra->load('proveedor', 'detalles.producto');

            return response()->json([
                'message' => 'Compra registrada exitosamente',
                'compra' => $this->formatearCompra($compra),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar compra: ' . $e->getMessage()], 500);
        }
    }

<<<<<<< HEAD
=======
    public function reportePdf(Request $request)
    {
        $busqueda = $request->input('busqueda', '');
        $proveedor = $request->input('proveedor', '');
        $fechaDesde = $request->input('fecha_desde', '');
        $fechaHasta = $request->input('fecha_hasta', '');

        $query = Compra::with('proveedor', 'detalles.producto')
            ->orderBy('fecha', 'desc')
            ->orderBy('id_compra', 'desc');

        if ($busqueda) {
            $q = strtolower($busqueda);
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('proveedor', function ($prov) use ($q) {
                    $prov->whereRaw("LOWER(nombre) LIKE ?", ["%{$q}%"]);
                })->orWhereRaw("LOWER(nro_factura_proveedor) LIKE ?", ["%{$q}%"]);
            });
        }

        if ($proveedor) {
            $query->where('id_proveedor', $proveedor);
        }

        if ($fechaDesde) {
            $query->where('fecha', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $compras = $query->get()->map(fn($c) => $this->formatearCompra($c));

        $totalGeneral = $compras->sum('total_compra');

        $proveedorNombre = '';
        if ($proveedor) {
            $prov = \App\Models\Proveedor::find($proveedor);
            $proveedorNombre = $prov ? $prov->nombre : '';
        }

        $filtros = [
            'busqueda' => $busqueda,
            'proveedor' => $proveedorNombre,
            'fecha_desde' => $fechaDesde ?: '—',
            'fecha_hasta' => $fechaHasta ?: '—',
        ];

        $logoPath = public_path('img/Logo3_NovaRider.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $pdf = Pdf::loadView('reportes.compras_pdf', [
            'compras' => $compras,
            'filtros' => $filtros,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'usuarioGenera' => auth()->user()->username ?? 'Sistema',
            'logoBase64' => $logoBase64,
            'totalRegistros' => $compras->count(),
            'totalGeneral' => $totalGeneral,
        ]);

        $filename = 'reporte_compras_' . now()->format('Y-m-d_His') . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

>>>>>>> respaldo-caja
    private function formatearCompra(Compra $compra)
    {
        return [
            'id_compra' => $compra->id_compra,
            'id_proveedor' => $compra->id_proveedor,
            'proveedor' => $compra->proveedor ? [
                'id_proveedor' => $compra->proveedor->id_proveedor,
                'nombre' => $compra->proveedor->nombre,
            ] : null,
            'fecha' => $compra->fecha,
            'nro_factura_proveedor' => $compra->nro_factura_proveedor,
            'total_compra' => (float) $compra->total_compra,
            'detalles' => $compra->detalles->map(function ($d) {
                return [
                    'id_detalle_compra' => $d->id_detalle_compra,
                    'id_producto' => $d->id_producto,
                    'producto' => $d->producto ? [
                        'id_producto' => $d->producto->id_producto,
                        'nombre' => $d->producto->nombre,
                    ] : null,
                    'cantidad' => $d->cantidad,
                    'precio_unitario_compra' => (float) $d->precio_unitario_compra,
                    'subtotal' => (float) $d->subtotal,
                ];
            }),
        ];
    }
}
