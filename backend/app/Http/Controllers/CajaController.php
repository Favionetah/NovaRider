<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function abrirCaja(Request $request)
    {
        $monto = $request->input('monto_inicial', 0);
        $usuarioId = auth()->id();

        try {
            $id = DB::table('TCajas')->insertGetId([
                'id_empleado' => auth()->user()->id_empleado,
                'fecha_apertura' => now(),
                'fecha_cierre' => null,
                'monto_apertura' => $monto,
                'monto_cierre_fisico' => null,
                'monto_sistema' => $monto,
                'observacion' => null,
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'id_caja' => $id, 'monto' => $monto]);
    }

    public function crearRecibo(Request $request)
    {
        $totalVenta = intval(round(floatval($request->input('total', 0))));
        $subtotalVenta = intval(round(floatval($request->input('subtotal', $totalVenta))));
        $descuentoVenta = intval(round(floatval($request->input('descuento', 0))));
        $usuarioId = auth()->id();

        try {
            $ventaId = DB::table('TVentas')->insertGetId([
                'id_cliente'   => $request->input('id_cliente', 1),
                'id_empleado'  => auth()->user()->id_empleado,
                'id_caja'      => $request->input('id_caja'),
                'nro_factura'  => 'REC-' . rand(10000, 99999),
                'fecha_hora'   => now(),
                'subtotal'     => $subtotalVenta,
                'descuento'    => $descuentoVenta,
                'total'        => $totalVenta,
                'metodo_pago'  => $request->input('metodo_pago', 'Efectivo'),
                'estadoA'      => true,
                'usuarioA'     => $usuarioId,
                'fechahoraA'   => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'id_venta' => $ventaId,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerVentas()
    {
        try {
            $ventas = DB::table('TVentas as v')
                ->leftJoin('TClientes as c', 'v.id_cliente', '=', 'c.id_cliente')
                ->select(
                    'v.id_venta',
                    'v.nro_factura',
                    'v.fecha_hora',
                    'v.subtotal',
                    'v.descuento',
                    'v.total',
                    'v.metodo_pago',
                    DB::raw("CONCAT(COALESCE(c.primer_nombre,''),' ',COALESCE(c.segundo_nombre,''),' ',COALESCE(c.apellido_paterno,''),' ',COALESCE(c.apellido_materno,'')) as cliente_nombre")
                )
                ->orderBy('v.id_venta', 'DESC')
                ->get();

            return response()->json(['ventas' => $ventas]);
        } catch (\Exception $e) {
            return response()->json(['ventas' => []]);
        }
    }

    public function cerrarCaja(Request $request)
    {
        $idCaja = $request->input('id_caja');
        $montoFisico = $request->input('monto_cierre', 0);
        $observacion = $request->input('observacion', '');
        $usuarioId = auth()->id();

        try {
            $caja = DB::table('TCajas')->where('id_caja', $idCaja)->first();
            if (!$caja) {
                return response()->json(['status' => 'error', 'message' => 'Caja no encontrada'], 404);
            }

            $montoSistema = DB::table('TVentas')
                ->where('id_caja', $idCaja)
                ->where('estadoA', true)
                ->sum('total');

            $diferencia = $montoFisico - ($caja->monto_apertura + $montoSistema);

            DB::table('TCajas')->where('id_caja', $idCaja)->update([
                'fecha_cierre' => now(),
                'monto_cierre_fisico' => $montoFisico,
                'monto_sistema' => $caja->monto_apertura + $montoSistema,
                'observacion' => $observacion ? "Diferencia: $diferencia | $observacion" : "Diferencia: $diferencia",
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'monto_sistema' => $caja->monto_apertura + $montoSistema,
                'diferencia' => $diferencia,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}