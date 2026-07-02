<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Motocicleta;
use App\Models\User;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\OrdenTrabajo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function systemStats()
    {
        return response()->json([
            'stats' => [
                'clientes' => Cliente::where('estadoA', true)->count(),
                'motocicletas' => Motocicleta::where('estadoA', true)->count(),
                'usuarios' => User::where('estadoA', true)->count(),
                'productos' => Producto::where('estadoA', true)->count(),
                'ventas_mes' => Venta::where('estadoA', true)
                    ->whereMonth('fecha_hora', now()->month)
                    ->whereYear('fecha_hora', now()->year)
                    ->count(),
                'ingresos_mes' => (float) Venta::where('estadoA', true)
                    ->whereMonth('fecha_hora', now()->month)
                    ->whereYear('fecha_hora', now()->year)
                    ->sum('total'),
                'stock_critico' => Producto::where('estadoA', true)
                    ->whereRaw('stock_disponible <= stock_minimo')
                    ->count()
            ]
        ]);
    }

    public function data(Request $request)
    {
        $tipo = $request->query('tipo', 'clientes');

        switch ($tipo) {
            case 'clientes':
                $data = Cliente::withCount('motocicletas')
                    ->where('estadoA', true)
                    ->orderBy('primer_nombre')
                    ->get();
                break;
            case 'motos':
                $data = Motocicleta::with('cliente')
                    ->where('estadoA', true)
                    ->orderBy('placa')
                    ->get();
                break;
            case 'usuarios':
                $data = User::with('empleado', 'roles')
                    ->where('estadoA', true)
                    ->get();
                break;
            case 'inventario':
                $data = Producto::where('estadoA', true)
                    ->orderBy('nombre')
                    ->get();
                break;
            case 'ventas':
                $data = Venta::with('cliente')
                    ->where('estadoA', true)
                    ->orderBy('fecha_hora', 'DESC')
                    ->take(50)
                    ->get();
                break;
            case 'taller':
                $data = OrdenTrabajo::with(['motocicleta.cliente', 'empleado', 'detalles.producto'])
                    ->where('estadoA', true)
                    ->orderBy('fecha_ingreso', 'DESC')
                    ->take(50)
                    ->get();
                break;
            case 'caja':
                $data = Venta::with('cliente')
                    ->where('estadoA', true)
                    ->orderBy('fecha_hora', 'DESC')
                    ->take(50)
                    ->get();
                break;
            default:
                $data = [];
        }

        return response()->json([
            'tipo' => $tipo,
            'data' => $data
        ]);
    }

    public function exportPdf(Request $request)
    {
        $tipo = $request->query('tipo', 'clientes');
        $titulo = 'Reporte de ' . ucfirst($tipo);

        switch ($tipo) {
            case 'clientes':
                $items = Cliente::withCount('motocicletas')->where('estadoA', true)->orderBy('primer_nombre')->get();
                $titulo = 'Reporte de Clientes y Motocicletas';
                break;
            case 'motos':
                $items = Motocicleta::with('cliente')->where('estadoA', true)->orderBy('placa')->get();
                $titulo = 'Reporte de Motocicletas Registradas';
                break;
            case 'usuarios':
                $items = User::with('empleado', 'roles')->where('estadoA', true)->get();
                $titulo = 'Reporte de Personal del Sistema';
                break;
            case 'inventario':
                $items = Producto::where('estadoA', true)->orderBy('nombre')->get();
                $titulo = 'Reporte de Inventario de Repuestos';
                break;
            case 'ventas':
                $items = Venta::with('cliente')->where('estadoA', true)->orderBy('fecha_hora', 'DESC')->get();
                $titulo = 'Reporte de Ventas';
                break;
            case 'taller':
                $items = OrdenTrabajo::with(['motocicleta.cliente', 'empleado', 'detalles.producto'])
                    ->where('estadoA', true)
                    ->orderBy('fecha_ingreso', 'DESC')
                    ->get();
                $titulo = 'Reporte Operativo de Taller';
                break;
            case 'caja':
                $items = Venta::with('cliente')->where('estadoA', true)->orderBy('fecha_hora', 'DESC')->get();
                $titulo = 'Reporte de Caja e Ingresos';
                break;
            case 'sistema':
                return $this->exportGlobalReport();
            default:
                $items = [];
        }

        $pdf = Pdf::loadView('reportes.pdf', [
            'titulo' => $titulo,
            'tipo' => $tipo,
            'items' => $items,
            'fecha' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->stream("reporte_{$tipo}_" . now()->format('YmdHis') . ".pdf");
    }

    private function exportGlobalReport()
    {
        $stats = [
            'clientes' => Cliente::where('estadoA', true)->count(),
            'motos' => Motocicleta::where('estadoA', true)->count(),
            'usuarios' => User::where('estadoA', true)->count(),
            'productos' => Producto::where('estadoA', true)->count(),
            'ventas_mes' => Venta::where('estadoA', true)->whereMonth('fecha_hora', now()->month)->count(),
            'ingresos_total' => Venta::where('estadoA', true)->sum('total')
        ];

        $pdf = Pdf::loadView('reportes.pdf_global', [
            'titulo' => 'Reporte General del Sistema NovaRider',
            'stats' => $stats,
            'fecha' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->stream("reporte_general_" . now()->format('YmdHis') . ".pdf");
    }
}
