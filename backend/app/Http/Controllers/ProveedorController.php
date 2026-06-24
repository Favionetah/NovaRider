<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $inactivos = $request->boolean('inactivos');

        $query = Proveedor::orderBy('id_proveedor');

        if ($inactivos) {
            $query->where('estadoA', false);
        } else {
            $query->where('estadoA', true);
        }

        $proveedores = $query->get()->map(function ($p) {
            return $this->formatearProveedor($p);
        });

        return response()->json(['proveedores' => $proveedores]);
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return response()->json(['proveedor' => $this->formatearProveedor($proveedor)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|min:2|max:255',
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $proveedor = Proveedor::create([
                'nombre' => $validated['nombre'],
                'telefono' => $validated['telefono'],
                'direccion' => $validated['direccion'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valores = implode('|', [
                $validated['nombre'],
                $validated['telefono'] ?? '',
                $validated['direccion'] ?? '',
            ]);
            $this->registrarAuditoria('TProveedores', $proveedor->id_proveedor, 'I', null, null, $valores, 'Creacion de proveedor');

            DB::commit();

            return response()->json([
                'message' => 'Proveedor creado exitosamente',
                'proveedor' => $this->formatearProveedor($proveedor),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear proveedor: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|min:2|max:255',
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $camposAudit = [];
            $valoresAnt = [];
            $valoresNue = [];

            $camposEditables = ['nombre', 'telefono', 'direccion'];

            $datosActualizar = [];

            foreach ($camposEditables as $campo) {
                if ($request->has($campo) && $request->$campo !== $proveedor->$campo) {
                    $datosActualizar[$campo] = $request->$campo;
                    $camposAudit[] = $campo;
                    $valoresAnt[] = $proveedor->$campo ?? '';
                    $valoresNue[] = $request->$campo;
                }
            }

            if (!empty($datosActualizar)) {
                $datosActualizar['usuarioA'] = $usuarioId;
                $datosActualizar['fechahoraA'] = now();
                $proveedor->update($datosActualizar);

                $this->registrarAuditoria('TProveedores', $proveedor->id_proveedor, 'U',
                    implode('|', $camposAudit),
                    implode('|', $valoresAnt),
                    implode('|', $valoresNue),
                    'Actualizacion de proveedor'
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Proveedor actualizado exitosamente',
                'proveedor' => $this->formatearProveedor($proveedor->fresh()),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar proveedor: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $proveedor->update([
            'estadoA' => false,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TProveedores', $proveedor->id_proveedor, 'U', 'estadoA', '1', '0', 'Desactivacion de proveedor');

        return response()->json(['message' => 'Proveedor desactivado exitosamente']);
    }

    private function formatearProveedor(Proveedor $proveedor)
    {
        return [
            'id_proveedor' => $proveedor->id_proveedor,
            'nombre' => $proveedor->nombre,
            'telefono' => $proveedor->telefono,
            'direccion' => $proveedor->direccion,
            'estadoA' => $proveedor->estadoA,
        ];
    }
}
