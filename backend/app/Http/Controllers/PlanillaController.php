<?php

namespace App\Http\Controllers;

use App\Models\Planilla;
use App\Traits\AuditoriaTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanillaController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $query = Planilla::with('empleado')
            ->where('estadoA', true)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->orderBy('id_planilla', 'desc');

        if ($request->has('id_empleado')) {
            $query->where('id_empleado', $request->id_empleado);
        }

        $planillas = $query->get()->map(function ($p) {
            return $this->formatearPlanilla($p);
        });

        return response()->json(['planillas' => $planillas]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empleado' => 'required|exists:TEmpleados,id_empleado',
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2020|max:2100',
            'sueldo_bruto' => 'required|numeric|min:0',
            'bonos' => 'nullable|numeric|min:0',
            'deducciones' => 'nullable|numeric|min:0',
            'sueldo_neto' => 'required|numeric|min:0',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $planilla = Planilla::create([
                'id_empleado' => $validated['id_empleado'],
                'mes' => $validated['mes'],
                'anio' => $validated['anio'],
                'sueldo_bruto' => $validated['sueldo_bruto'],
                'bonos' => $validated['bonos'] ?? 0,
                'deducciones' => $validated['deducciones'] ?? 0,
                'sueldo_neto' => $validated['sueldo_neto'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valores = implode('|', [
                'empleado:' . $validated['id_empleado'],
                'periodo:' . $validated['mes'] . '/' . $validated['anio'],
                'bruto:' . $validated['sueldo_bruto'],
                'bonos:' . ($validated['bonos'] ?? 0),
                'deducciones:' . ($validated['deducciones'] ?? 0),
                'neto:' . $validated['sueldo_neto'],
            ]);
            $this->registrarAuditoria('TPlanillas', $planilla->id_planilla, 'I', null, null, $valores, 'Creacion de planilla mensual');

            DB::commit();

            $planilla->load('empleado');

            return response()->json([
                'message' => 'Planilla registrada exitosamente',
                'planilla' => $this->formatearPlanilla($planilla),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar planilla: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $planilla = Planilla::findOrFail($id);

        $planilla->update([
            'estadoA' => false,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TPlanillas', $planilla->id_planilla, 'U', 'estadoA', '1', '0', 'Eliminacion de planilla');

        return response()->json(['message' => 'Planilla eliminada exitosamente']);
    }

    public function resumen(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $mes = $request->get('mes');

        $query = Planilla::with('empleado')
            ->where('anio', $anio)
            ->where('estadoA', true);

        if ($mes) {
            $query->where('mes', $mes);
        }

        $planillas = $query->get();

        $totalBruto = $planillas->sum('sueldo_bruto');
        $totalBonos = $planillas->sum('bonos');
        $totalDeducciones = $planillas->sum('deducciones');
        $totalNeto = $planillas->sum('sueldo_neto');

        return response()->json([
            'planillas' => $planillas->map(fn($p) => $this->formatearPlanilla($p)),
            'resumen' => [
                'total_empleados' => $planillas->count(),
                'total_bruto' => (float) $totalBruto,
                'total_bonos' => (float) $totalBonos,
                'total_deducciones' => (float) $totalDeducciones,
                'total_neto' => (float) $totalNeto,
            ],
        ]);
    }

    public function reportePdf(Request $request)
    {
        $idEmpleado = $request->input('id_empleado');

        $query = Planilla::with('empleado')
            ->where('estadoA', true)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->orderBy('id_planilla', 'desc');

        $empleadoNombre = '';
        if ($idEmpleado) {
            $query->where('id_empleado', $idEmpleado);
            $empleado = \App\Models\Empleado::find($idEmpleado);
            if ($empleado) {
                $empleadoNombre = trim(implode(' ', array_filter([
                    $empleado->primer_nombre,
                    $empleado->segundo_nombre,
                    $empleado->apellido_paterno,
                    $empleado->apellido_materno,
                ])));
            }
        }

        $planillas = $query->get()->map(function ($p) {
            return $this->formatearPlanilla($p);
        });

        $totalBruto = $planillas->sum('sueldo_bruto');
        $totalBonos = $planillas->sum('bonos');
        $totalDeducciones = $planillas->sum('deducciones');
        $totalNeto = $planillas->sum('sueldo_neto');

        $logoPath = public_path('img/Logo3_NovaRider.png');
        $logoExists = file_exists($logoPath);
        $logoBase64 = $logoExists ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $pdf = Pdf::loadView('reportes.planillas_pdf', [
            'planillas' => $planillas,
            'empleadoNombre' => $empleadoNombre,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'usuarioGenera' => auth()->user()->username ?? 'Sistema',
            'logoBase64' => $logoBase64,
            'totalRegistros' => $planillas->count(),
            'totalBruto' => $totalBruto,
            'totalBonos' => $totalBonos,
            'totalDeducciones' => $totalDeducciones,
            'totalNeto' => $totalNeto,
        ]);

        $filename = 'reporte_planillas_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->stream($filename);
    }

    private function formatearPlanilla(Planilla $planilla)
    {
        return [
            'id_planilla' => $planilla->id_planilla,
            'id_empleado' => $planilla->id_empleado,
            'empleado' => $planilla->empleado ? [
                'id_empleado' => $planilla->empleado->id_empleado,
                'nombre_completo' => trim(implode(' ', array_filter([
                    $planilla->empleado->primer_nombre,
                    $planilla->empleado->segundo_nombre,
                    $planilla->empleado->apellido_paterno,
                    $planilla->empleado->apellido_materno,
                ]))),
            ] : null,
            'mes' => $planilla->mes,
            'anio' => $planilla->anio,
            'sueldo_bruto' => (float) $planilla->sueldo_bruto,
            'bonos' => (float) $planilla->bonos,
            'deducciones' => (float) $planilla->deducciones,
            'sueldo_neto' => (float) $planilla->sueldo_neto,
        ];
    }
}
