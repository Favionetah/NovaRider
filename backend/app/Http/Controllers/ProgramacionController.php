<?php

namespace App\Http\Controllers;

use App\Models\Programacion;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramacionController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $query = Programacion::with('empleado.usuario')
            ->where('estadoA', true)
            ->orderBy('id_empleado')
            ->orderBy('dia_semana');

        if ($request->has('id_empleado')) {
            $query->where('id_empleado', $request->id_empleado);
        }

        $programaciones = $query->get()->map(function ($p) {
            return $this->formatear($p);
        });

        return response()->json(['programaciones' => $programaciones]);
    }

    public function global()
    {
        $empleados = \App\Models\Empleado::where('estadoA', true)->pluck('id_empleado');

        $programaciones = Programacion::with('empleado.usuario')
            ->whereIn('id_empleado', $empleados)
            ->where('estadoA', true)
            ->orderBy('id_empleado')
            ->orderBy('dia_semana')
            ->get()
            ->map(function ($p) {
                return $this->formatear($p);
            });

        $porEmpleado = $programaciones->groupBy('id_empleado')->map(function ($dias, $empId) {
            $totalHoras = $dias->reduce(function ($sum, $d) {
                if (!$d['activo'] || !$d['hora_entrada'] || !$d['hora_salida']) return $sum;
                [$h1, $m1] = explode(':', $d['hora_entrada']);
                [$h2, $m2] = explode(':', $d['hora_salida']);
                return $sum + (($h2 * 60 + $m2) - ($h1 * 60 + $m1)) / 60;
            }, 0);

            return [
                'id_empleado' => $empId,
                'empleado' => $dias->first()['empleado'] ?? null,
                'dias' => $dias->values(),
                'total_horas_semana' => $totalHoras,
            ];
        })->values();

        $conteoPorDia = [];
        for ($d = 1; $d <= 6; $d++) {
            $conteoPorDia[$d] = $programaciones->where('dia_semana', $d)->where('activo', true)->count();
        }

        return response()->json([
            'empleados' => $porEmpleado,
            'conteo_por_dia' => $conteoPorDia,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empleado' => 'required|exists:TEmpleados,id_empleado',
            'horario' => 'required|array|size:6',
            'horario.*.dia_semana' => 'required|integer|min:1|max:6',
            'horario.*.activo' => 'required|boolean',
            'horario.*.hora_entrada' => 'nullable|date_format:H:i',
            'horario.*.hora_salida' => 'nullable|date_format:H:i',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            Programacion::where('id_empleado', $validated['id_empleado'])->delete();

            foreach ($validated['horario'] as $d) {
                Programacion::create([
                    'id_empleado' => $validated['id_empleado'],
                    'dia_semana' => $d['dia_semana'],
                    'hora_entrada' => $d['activo'] ? ($d['hora_entrada'] ?? null) : null,
                    'hora_salida' => $d['activo'] ? ($d['hora_salida'] ?? null) : null,
                    'activo' => $d['activo'],
                    'estadoA' => true,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);
            }

            $diasStr = implode(',', array_map(function ($d) {
                return ($d['activo'] ? 'Dia' . $d['dia_semana'] : 'X' . $d['dia_semana']);
            }, $validated['horario']));

            $this->registrarAuditoria('TProgramaciones', $validated['id_empleado'], 'I', null, null, $diasStr, 'Asignacion de horario semanal');

            DB::commit();

            $programaciones = Programacion::with('empleado.usuario')
            ->where('id_empleado', $validated['id_empleado'])
            ->orderBy('dia_semana')
            ->get()
            ->map(fn($p) => $this->formatear($p));

            return response()->json([
                'message' => 'Horario guardado exitosamente',
                'programaciones' => $programaciones,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar horario: ' . $e->getMessage()], 500);
        }
    }

    private function formatear(Programacion $p)
    {
        $emp = $p->empleado;
        return [
            'id_programacion' => $p->id_programacion,
            'id_empleado' => $p->id_empleado,
            'id_usuario' => $emp && $emp->usuario ? $emp->usuario->id_usuario : null,
            'empleado' => $emp ? [
                'id_empleado' => $emp->id_empleado,
                'id_usuario' => $emp->usuario ? $emp->usuario->id_usuario : null,
                'nombre_completo' => trim(implode(' ', array_filter([
                    $emp->primer_nombre, $emp->segundo_nombre,
                    $emp->apellido_paterno, $emp->apellido_materno,
                ]))),
<<<<<<< HEAD
=======
                'ci' => $emp->ci,
>>>>>>> respaldo-caja
                'cargo' => $emp->cargo,
            ] : null,
            'dia_semana' => $p->dia_semana,
            'hora_entrada' => $p->hora_entrada ? substr($p->hora_entrada, 0, 5) : null,
            'hora_salida' => $p->hora_salida ? substr($p->hora_salida, 0, 5) : null,
            'activo' => $p->activo,
        ];
    }
}
