<?php

namespace App\Http\Controllers;

use App\Models\Estante;
use App\Models\Seccion;
use App\Models\Ubicacion;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstanteController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $inactivos = $request->boolean('inactivos');

        $query = Estante::with('secciones.ubicaciones')
            ->orderBy('numero_estante');

        if ($inactivos) {
            $query->where('estadoA', false);
        } else {
            $query->where('estadoA', true);
        }

        $estantes = $query->get()->map(fn($e) => $this->formatearEstante($e));

        return response()->json(['estantes' => $estantes]);
    }

    public function show($id)
    {
        $estante = Estante::with('secciones.ubicaciones')->findOrFail($id);
        return response()->json(['estante' => $this->formatearEstante($estante)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_estante' => 'required|integer|min:1|max:50',
            'pasillo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'secciones' => 'present|array',
            'secciones.*.codigo_seccion' => 'required|string|in:A,B,C,D',
            'secciones.*.niveles' => 'present|array',
            'secciones.*.niveles.*' => 'required|string|in:Alto,Medio,Bajo',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $estante = Estante::create([
                'numero_estante' => $validated['numero_estante'],
                'pasillo' => $validated['pasillo'],
                'descripcion' => $validated['descripcion'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            foreach ($validated['secciones'] as $secData) {
                $seccion = Seccion::create([
                    'id_estante' => $estante->id_estante,
                    'codigo_seccion' => $secData['codigo_seccion'],
                    'descripcion' => 'Seccion ' . $secData['codigo_seccion'],
                    'estadoA' => true,
                    'usuarioA' => $usuarioId,
                    'fechahoraA' => now(),
                ]);

                foreach ($secData['niveles'] as $nivel) {
                    Ubicacion::create([
                        'id_seccion' => $seccion->id_seccion,
                        'nivel' => $nivel,
                        'estadoA' => true,
                        'usuarioA' => $usuarioId,
                        'fechahoraA' => now(),
                    ]);
                }
            }

            $valores = implode('|', [
                (string)$validated['numero_estante'],
                $validated['pasillo'] ?? '',
                $validated['descripcion'] ?? '',
            ]);
            $this->registrarAuditoria('TEstantes', $estante->id_estante, 'I', null, null, $valores, 'Creacion de estante');

            DB::commit();

            $estante->load('secciones.ubicaciones');

            return response()->json([
                'message' => 'Estante creado exitosamente',
                'estante' => $this->formatearEstante($estante),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear estante: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $estante = Estante::findOrFail($id);

        $validated = $request->validate([
            'numero_estante' => 'sometimes|integer|min:1|max:50',
            'pasillo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $camposAudit = [];
            $valoresAnt = [];
            $valoresNue = [];

            $camposEditables = ['numero_estante', 'pasillo', 'descripcion'];
            $datosActualizar = [];

            foreach ($camposEditables as $campo) {
                if ($request->has($campo) && (string)$request->$campo !== (string)$estante->$campo) {
                    $datosActualizar[$campo] = $request->$campo;
                    $camposAudit[] = $campo;
                    $valoresAnt[] = (string)($estante->$campo ?? '');
                    $valoresNue[] = (string)($request->$campo ?? '');
                }
            }

            if (!empty($datosActualizar)) {
                $datosActualizar['usuarioA'] = $usuarioId;
                $datosActualizar['fechahoraA'] = now();
                $estante->update($datosActualizar);

                $this->registrarAuditoria('TEstantes', $estante->id_estante, 'U',
                    implode('|', $camposAudit),
                    implode('|', $valoresAnt),
                    implode('|', $valoresNue),
                    'Actualizacion de estante'
                );
            }

            DB::commit();

            $estante->fresh()->load('secciones.ubicaciones');

            return response()->json([
                'message' => 'Estante actualizado exitosamente',
                'estante' => $this->formatearEstante($estante),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar estante: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $estante = Estante::findOrFail($id);

        $estante->update([
            'estadoA' => false,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TEstantes', $estante->id_estante, 'U', 'estadoA', '1', '0', 'Desactivacion de estante');

        return response()->json(['message' => 'Estante desactivado exitosamente']);
    }

    public function reactivar($id)
    {
        $estante = Estante::where('estadoA', false)->findOrFail($id);

        $estante->update([
            'estadoA' => true,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TEstantes', $estante->id_estante, 'U', 'estadoA', '0', '1', 'Reactivacion de estante');

        return response()->json([
            'message' => 'Estante reactivado exitosamente',
            'estante' => $this->formatearEstante($estante->fresh()->load('secciones.ubicaciones')),
        ]);
    }

    public function arbol()
    {
        $estantes = Estante::where('estadoA', true)
            ->with(['secciones' => function ($q) {
                $q->where('estadoA', true)->with(['ubicaciones' => function ($q2) {
                    $q2->where('estadoA', true);
                }]);
            }])
            ->orderBy('numero_estante')
            ->get()
            ->map(function ($e) {
                return [
                    'id_estante' => $e->id_estante,
                    'numero_estante' => $e->numero_estante,
                    'pasillo' => $e->pasillo,
                    'secciones' => $e->secciones->map(function ($s) {
                        return [
                            'id_seccion' => $s->id_seccion,
                            'codigo_seccion' => $s->codigo_seccion,
                            'ubicaciones' => $s->ubicaciones->map(function ($u) {
                                return [
                                    'id_ubicacion' => $u->id_ubicacion,
                                    'nivel' => $u->nivel,
                                ];
                            }),
                        ];
                    }),
                ];
            });

        return response()->json(['estantes' => $estantes]);
    }

    private function formatearEstante(Estante $estante)
    {
        return [
            'id_estante' => $estante->id_estante,
            'numero_estante' => $estante->numero_estante,
            'pasillo' => $estante->pasillo,
            'descripcion' => $estante->descripcion,
            'estadoA' => $estante->estadoA,
            'secciones' => $estante->secciones->map(function ($s) {
                return [
                    'id_seccion' => $s->id_seccion,
                    'codigo_seccion' => $s->codigo_seccion,
                    'descripcion' => $s->descripcion,
                    'estadoA' => $s->estadoA,
                    'ubicaciones' => $s->ubicaciones->map(function ($u) {
                        return [
                            'id_ubicacion' => $u->id_ubicacion,
                            'nivel' => $u->nivel,
                            'estadoA' => $u->estadoA,
                        ];
                    }),
                ];
            }),
        ];
    }
}
