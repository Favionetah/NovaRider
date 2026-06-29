<?php

namespace App\Http\Controllers;

use App\Models\ModelosCompatible;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModelosCompatibleController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $inactivos = $request->boolean('inactivos');

        $query = ModelosCompatible::orderBy('marca_moto')->orderBy('modelo_moto');

        if ($inactivos) {
            $query->where('estadoA', false);
        } else {
            $query->where('estadoA', true);
        }

        $modelos = $query->get()->map(fn($m) => $this->formatearModelo($m));

        return response()->json(['modelos' => $modelos]);
    }

    public function show($id)
    {
        $modelo = ModelosCompatible::findOrFail($id);
        return response()->json(['modelo' => $this->formatearModelo($modelo)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marca_moto' => 'required|string|max:255',
            'modelo_moto' => 'required|string|max:255',
            'anio_inicio' => 'nullable|integer|min:1900|max:2099',
            'anio_fin' => 'nullable|integer|min:1900|max:2099|gte:anio_inicio',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $modelo = ModelosCompatible::create([
                'marca_moto' => $validated['marca_moto'],
                'modelo_moto' => $validated['modelo_moto'],
                'anio_inicio' => $validated['anio_inicio'],
                'anio_fin' => $validated['anio_fin'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valores = implode('|', [
                $validated['marca_moto'],
                $validated['modelo_moto'],
                (string)($validated['anio_inicio'] ?? ''),
                (string)($validated['anio_fin'] ?? ''),
            ]);
            $this->registrarAuditoria('TModelosCompatibles', $modelo->id_modelo, 'I', null, null, $valores, 'Creacion de modelo compatible');

            DB::commit();

            return response()->json([
                'message' => 'Modelo compatible creado exitosamente',
                'modelo' => $this->formatearModelo($modelo),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear modelo: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $modelo = ModelosCompatible::findOrFail($id);

        $validated = $request->validate([
            'marca_moto' => 'sometimes|string|max:255',
            'modelo_moto' => 'sometimes|string|max:255',
            'anio_inicio' => 'nullable|integer|min:1900|max:2099',
            'anio_fin' => 'nullable|integer|min:1900|max:2099|gte:anio_inicio',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $camposAudit = [];
            $valoresAnt = [];
            $valoresNue = [];

            $camposEditables = ['marca_moto', 'modelo_moto', 'anio_inicio', 'anio_fin'];
            $datosActualizar = [];

            foreach ($camposEditables as $campo) {
                if ($request->has($campo) && (string)$request->$campo !== (string)$modelo->$campo) {
                    $datosActualizar[$campo] = $request->$campo;
                    $camposAudit[] = $campo;
                    $valoresAnt[] = (string)($modelo->$campo ?? '');
                    $valoresNue[] = (string)($request->$campo ?? '');
                }
            }

            if (!empty($datosActualizar)) {
                $datosActualizar['usuarioA'] = $usuarioId;
                $datosActualizar['fechahoraA'] = now();
                $modelo->update($datosActualizar);

                $this->registrarAuditoria('TModelosCompatibles', $modelo->id_modelo, 'U',
                    implode('|', $camposAudit),
                    implode('|', $valoresAnt),
                    implode('|', $valoresNue),
                    'Actualizacion de modelo compatible'
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Modelo compatible actualizado exitosamente',
                'modelo' => $this->formatearModelo($modelo->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar modelo: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $modelo = ModelosCompatible::findOrFail($id);

        $modelo->update([
            'estadoA' => false,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TModelosCompatibles', $modelo->id_modelo, 'U', 'estadoA', '1', '0', 'Desactivacion de modelo compatible');

        return response()->json(['message' => 'Modelo compatible desactivado exitosamente']);
    }

    public function reactivar($id)
    {
        $modelo = ModelosCompatible::where('estadoA', false)->findOrFail($id);

        $modelo->update([
            'estadoA' => true,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TModelosCompatibles', $modelo->id_modelo, 'U', 'estadoA', '0', '1', 'Reactivacion de modelo compatible');

        return response()->json([
            'message' => 'Modelo compatible reactivado exitosamente',
            'modelo' => $this->formatearModelo($modelo->fresh()),
        ]);
    }

    private function formatearModelo(ModelosCompatible $modelo)
    {
        return [
            'id_modelo' => $modelo->id_modelo,
            'marca_moto' => $modelo->marca_moto,
            'modelo_moto' => $modelo->modelo_moto,
            'anio_inicio' => $modelo->anio_inicio,
            'anio_fin' => $modelo->anio_fin,
            'estadoA' => $modelo->estadoA,
        ];
    }
}
