<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Traits\AuditoriaTrait;
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
