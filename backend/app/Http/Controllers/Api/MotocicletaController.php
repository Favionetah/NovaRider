<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Motocicleta;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;

class MotocicletaController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        try {
            $inactivos = $request->boolean('inactivos');
            $id_cliente = $request->query('id_cliente');

            // 🚀 Cargamos la relación 'cliente' para la vista principal de motos
            $query = Motocicleta::with('cliente')->orderBy('placa', 'ASC');

            if ($inactivos) {
                $query->where('estadoA', false);
            } else {
                $query->where('estadoA', true);
            }

            if ($id_cliente) {
                $query->where('id_cliente', $id_cliente);
            }

            // 🚀 Traemos todos los campos necesarios completos
            $motocicletas = $query->get();

            // 🚀 CORRECCIÓN: Devolver el arreglo directamente sin envolverlo en ['motocicletas' => ...]
            return response()->json($motocicletas);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $motocicleta = Motocicleta::with('cliente')->findOrFail($id);

        return response()->json(['motocicleta' => $motocicleta]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:TClientes,id_cliente',
            'placa' => 'required|string|max:20|unique:TMotocicletas,placa',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'nro_chasis' => 'nullable|string|max:100|unique:TMotocicletas,nro_chasis',
            'nro_motor' => 'nullable|string|max:100|unique:TMotocicletas,nro_motor',
            'color' => 'nullable|string|max:50',
            'cilindrada' => 'nullable|string|max:50|regex:/^[0-9]+(\s?cc)?$/i',
        ], [
            'id_cliente.required' => 'El cliente es obligatorio',
            'id_cliente.exists' => 'El cliente no existe',
            'placa.required' => 'La placa es obligatoria',
            'placa.unique' => 'Esta placa ya está registrada',
            'marca.required' => 'La marca es obligatoria',
            'modelo.required' => 'El modelo es obligatorio',
            'anio.required' => 'El año es obligatorio',
            'anio.integer' => 'El año debe ser un número entero',
            'anio.min' => 'El año no puede ser anterior a 1900',
            'nro_chasis.unique' => 'Este número de chasis ya está registrado',
            'nro_motor.unique' => 'Este número de motor ya está registrado',
            'cilindrada.regex' => 'La cilindrada debe ser numérica (ej: 250 o 250cc)',
        ]);

        $usuarioId = auth()->id();

        $motocicleta = Motocicleta::create([
            'id_cliente' => $validated['id_cliente'],
            'placa' => $validated['placa'],
            'marca' => $validated['marca'],
            'modelo' => $validated['modelo'],
            'anio' => $validated['anio'],
            'nro_chasis' => $validated['nro_chasis'],
            'nro_motor' => $validated['nro_motor'],
            'color' => $validated['color'],
            'cilindrada' => $validated['cilindrada'],
            'estadoA' => true,
            'usuarioA' => $usuarioId,
            'fechahoraA' => now(),
        ]);

        $valoresMotocicleta = implode('|', [
            $validated['id_cliente'],
            $validated['placa'],
            $validated['marca'],
            $validated['modelo'],
            $validated['anio'],
            $validated['nro_chasis'] ?? '',
            $validated['nro_motor'] ?? '',
            $validated['color'] ?? '',
            $validated['cilindrada'] ?? '',
        ]);

        $this->registrarAuditoria(
            'TMotocicletas',
            $motocicleta->id_motocicleta,
            'I',
            null,
            null,
            $valoresMotocicleta,
            'Creación de motocicleta'
        );

        return response()->json([
            'message' => 'Motocicleta creada exitosamente',
            'motocicleta' => $motocicleta->load('cliente'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $motocicleta = Motocicleta::findOrFail($id);

        $validated = $request->validate([
            'id_cliente' => 'sometimes|exists:TClientes,id_cliente',
            'placa' => 'sometimes|string|max:20|unique:TMotocicletas,placa,' . $id . ',id_motocicleta',
            'marca' => 'sometimes|string|max:100',
            'modelo' => 'sometimes|string|max:100',
            'anio' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'nro_chasis' => 'nullable|string|max:100|unique:TMotocicletas,nro_chasis,' . $id . ',id_motocicleta',
            'nro_motor' => 'nullable|string|max:100|unique:TMotocicletas,nro_motor,' . $id . ',id_motocicleta',
            'color' => 'nullable|string|max:50',
            'cilindrada' => 'nullable|string|max:50|regex:/^[0-9]+(\s?cc)?$/i',
        ], [
            'placa.unique' => 'Esta placa ya está registrada',
            'nro_chasis.unique' => 'Este número de chasis ya está registrado',
            'nro_motor.unique' => 'Este número de motor ya está registrado',
            'cilindrada.regex' => 'La cilindrada debe ser numérica (ej: 250 o 250cc)',
        ]);

        $usuarioId = auth()->id();
        $camposModificados = [];
        $valoresAnteriores = [];
        $valoresNuevos = [];

        foreach ($validated as $campo => $valor) {
            if ($motocicleta->{$campo} !== $valor) {
                $camposModificados[] = $campo;
                $valoresAnteriores[] = $motocicleta->{$campo} ?? '';
                $valoresNuevos[] = $valor ?? '';
            }
        }

        if (!empty($camposModificados)) {
            $motocicleta->update($validated);
            $motocicleta->update([
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria(
                'TMotocicletas',
                $id,
                'U',
                implode('|', $camposModificados),
                implode('|', $valoresAnteriores),
                implode('|', $valoresNuevos),
                'Actualización de motocicleta'
            );
        }

        return response()->json([
            'message' => 'Motocicleta actualizada exitosamente',
            'motocicleta' => $motocicleta->load('cliente'),
        ]);
    }

    public function destroy($id)
    {
        $motocicleta = Motocicleta::findOrFail($id);
        $usuarioId = auth()->id();

        $motocicleta->update([
            'estadoA' => false,
            'usuarioA' => $usuarioId,
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria(
            'TMotocicletas',
            $id,
            'U',
            'estadoA',
            '1',
            '0',
            'Desactivación de motocicleta'
        );

        return response()->json(['message' => 'Motocicleta desactivada exitosamente']);
    }

    public function reactivar($id)
    {
        $motocicleta = Motocicleta::findOrFail($id);
        $usuarioId = auth()->id();

        $motocicleta->update([
            'estadoA' => true,
            'usuarioA' => $usuarioId,
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria(
            'TMotocicletas',
            $id,
            'U',
            'estadoA',
            '0',
            '1',
            'Reactivación de motocicleta'
        );

        return response()->json(['message' => 'Motocicleta reactivada exitosamente']);
    }

    // 📦 Cambio de tu compañero integrado correctamente
    public function historial($id)
    {
        $motocicleta = Motocicleta::findOrFail($id);
        $historial = $motocicleta->ordenesTrabajo()
            ->with(['detalles.servicio', 'empleado'])
            ->orderBy('fecha_ingreso', 'DESC')
            ->get();

        return response()->json([
            'motocicleta' => $motocicleta,
            'historial' => $historial
        ]);
    }
}