<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\Programacion;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $query = Turno::with('empleado')
            ->orderBy('fecha', 'desc')
            ->orderBy('id_turno', 'desc');

        if ($request->has('id_empleado')) {
            $query->where('id_empleado', $request->id_empleado);
        }

        if ($request->has('fecha')) {
            $query->where('fecha', $request->fecha);
        }

        $turnos = $query->get()->map(function ($t) {
            return $this->formatearTurno($t);
        });

        return response()->json(['turnos' => $turnos]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empleado' => 'required|exists:TEmpleados,id_empleado',
            'fecha' => 'required|date',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i',
            'observacion' => 'nullable|string|max:255',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $turno = Turno::create([
                'id_empleado' => $validated['id_empleado'],
                'fecha' => $validated['fecha'],
                'hora_entrada' => $validated['hora_entrada'],
                'hora_salida' => $validated['hora_salida'],
                'observacion' => $validated['observacion'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valores = implode('|', [
                'empleado:' . $validated['id_empleado'],
                'fecha:' . $validated['fecha'],
                'entrada:' . ($validated['hora_entrada'] ?? ''),
                'salida:' . ($validated['hora_salida'] ?? ''),
            ]);
            $this->registrarAuditoria('TTurnos', $turno->id_turno, 'I', null, null, $valores, 'Asignacion de turno');

            DB::commit();

            $turno->load('empleado');

            return response()->json([
                'message' => 'Turno registrado exitosamente',
                'turno' => $this->formatearTurno($turno),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar turno: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $turno = Turno::findOrFail($id);

        $validated = $request->validate([
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i',
            'observacion' => 'nullable|string|max:255',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $camposAudit = [];
            $valoresAnt = [];
            $valoresNue = [];

            $camposEditables = ['hora_entrada', 'hora_salida', 'observacion'];

            $datosActualizar = [];

            foreach ($camposEditables as $campo) {
                if ($request->has($campo) && $request->$campo !== $turno->$campo) {
                    $datosActualizar[$campo] = $request->$campo;
                    $camposAudit[] = $campo;
                    $valoresAnt[] = $turno->$campo ?? '';
                    $valoresNue[] = $request->$campo;
                }
            }

            if (!empty($datosActualizar)) {
                $datosActualizar['usuarioA'] = $usuarioId;
                $datosActualizar['fechahoraA'] = now();
                $turno->update($datosActualizar);

                $this->registrarAuditoria('TTurnos', $turno->id_turno, 'U',
                    implode('|', $camposAudit),
                    implode('|', $valoresAnt),
                    implode('|', $valoresNue),
                    'Actualizacion de turno'
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Turno actualizado exitosamente',
                'turno' => $this->formatearTurno($turno->fresh()->load('empleado')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar turno: ' . $e->getMessage()], 500);
        }
    }

    public function registrar(Request $request)
    {
        $idEmpleado = $request->input('id_empleado');
        if (!$idEmpleado) {
            return response()->json(['message' => 'id_empleado es requerido'], 422);
        }

        $hoy = now()->toDateString();
        $horaActual = now()->format('H:i');
        $diaSemana = (int) now()->format('N'); // 1=Lun ... 6=Sab, 7=Dom
        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $turnoHoy = Turno::where('id_empleado', $idEmpleado)
                ->where('fecha', $hoy)
                ->orderBy('id_turno', 'desc')
                ->first();

            $programacionHoy = Programacion::where('id_empleado', $idEmpleado)
                ->where('dia_semana', $diaSemana)
                ->where('estadoA', true)
                ->first();

            $horaEsperada = $programacionHoy && $programacionHoy->activo ? $programacionHoy->hora_entrada : null;
            $horaSalidaEsperada = $programacionHoy && $programacionHoy->activo ? $programacionHoy->hora_salida : null;

            if ($turnoHoy && !$turnoHoy->hora_salida) {
                $turnoHoy->update([
                    'hora_salida' => $horaActual,
                    'hora_salida_esperada' => $horaSalidaEsperada,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);

                $this->registrarAuditoria('TTurnos', $turnoHoy->id_turno, 'U',
                    'hora_salida|hora_salida_esperada',
                    $turnoHoy->getOriginal('hora_salida') . '|' . $turnoHoy->getOriginal('hora_salida_esperada'),
                    $horaActual . '|' . ($horaSalidaEsperada ?? ''),
                    'Registro de salida automatico'
                );

                DB::commit();
                $turnoHoy->load('empleado');

                return response()->json([
                    'message' => 'Salida registrada exitosamente',
                    'tipo' => 'salida',
                    'turno' => $this->formatearTurno($turnoHoy),
                ]);
            }

            $turno = Turno::create([
                'id_empleado' => $idEmpleado,
                'fecha' => $hoy,
                'hora_entrada' => $horaActual,
                'hora_entrada_esperada' => $horaEsperada,
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valores = implode('|', [
                'empleado:' . $idEmpleado,
                'fecha:' . $hoy,
                'entrada:' . $horaActual,
                'esperada:' . ($horaEsperada ?? ''),
            ]);
            $this->registrarAuditoria('TTurnos', $turno->id_turno, 'I', null, null, $valores, 'Registro de entrada automatico');

            DB::commit();
            $turno->load('empleado');

            return response()->json([
                'message' => 'Entrada registrada exitosamente',
                'tipo' => 'entrada',
                'turno' => $this->formatearTurno($turno),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar: ' . $e->getMessage()], 500);
        }
    }

    private function formatearTurno(Turno $turno)
    {
        $entrada = $turno->hora_entrada;
        $esperada = $turno->hora_entrada_esperada;
        $minutosTarde = null;
        if ($entrada && $esperada) {
            [$h1, $m1] = explode(':', $entrada);
            [$h2, $m2] = explode(':', $esperada);
            $diff = ($h1 * 60 + $m1) - ($h2 * 60 + $m2);
            if ($diff > 0) $minutosTarde = $diff;
        }

        return [
            'id_turno' => $turno->id_turno,
            'id_empleado' => $turno->id_empleado,
            'empleado' => $turno->empleado ? [
                'id_empleado' => $turno->empleado->id_empleado,
                'nombre_completo' => trim(implode(' ', array_filter([
                    $turno->empleado->primer_nombre,
                    $turno->empleado->segundo_nombre,
                    $turno->empleado->apellido_paterno,
                    $turno->empleado->apellido_materno,
                ]))),
            ] : null,
            'fecha' => $turno->fecha,
            'hora_entrada' => $entrada,
            'hora_salida' => $turno->hora_salida,
            'hora_entrada_esperada' => $esperada,
            'hora_salida_esperada' => $turno->hora_salida_esperada,
            'minutos_tarde' => $minutosTarde,
            'observacion' => $turno->observacion,
        ];
    }
}
