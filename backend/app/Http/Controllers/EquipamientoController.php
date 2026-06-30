<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EquipamientoController extends Controller
{
    // Devuelve el inventario de las herramientas pesadas y de diagnóstico del taller
    public function index()
    {
        $herramientasTaller = [
            ['id' => 1, 'codigo' => 'ELEV-01', 'nombre' => 'Elevador Hidráulico Rampa 1', 'tipo' => 'Herramienta Pesada', 'estado' => 'Operativo'],
            ['id' => 2, 'codigo' => 'SCAN-01', 'nombre' => 'Escáner de Diagnóstico OBD2 OBDII', 'tipo' => 'Electrónica', 'estado' => 'Falla Reportada'],
            ['id' => 3, 'codigo' => 'COMP-01', 'nombre' => 'Compresor de Aire 50L', 'tipo' => 'Neumática', 'estado' => 'En Mantenimiento']
        ];

        return response()->json([
            'mensaje' => 'Inventario de Equipamiento y Activos Físicos - Novarider',
            'herramientas' => $herramientasTaller
        ]);
    }

    // Flujo para que los mecánicos reporten si una rampa o herramienta se averió
    public function reportarFalla(Request $request)
    {
        $codigo = $request->input('codigo_equipo', 'SCAN-01');
        $problema = $request->input('descripcion', 'No enlaza con la computadora de las motos Yamaha');

        return response()->json([
            'status' => 'success',
            'mensaje' => "Orden de revisión creada. El equipo {$codigo} cambió de estado.",
            'nuevo_estado' => 'Falla Reportada',
            'notificacion_administrador' => 'Enviada con éxito'
        ]);

    }
    // RF-30: Programación de Mantenimiento Preventivo Periódico para Evitar Paros
    public function programarMantenimiento(Request $request)
    {
        // 1. Validamos que ingresen el equipo, la fecha programada y el tipo de revisión
        $request->validate([
            'codigo_equipo' => 'required|string',
            'fecha_mantenimiento' => 'required|date|after_or_equal:today',
            'tipo_mantenimiento' => 'required|string' // Ej: Cambio de aceite, calibración, etc.
        ]);

        $codigo = $request->input('codigo_equipo');
        $fecha = $request->input('fecha_mantenimiento');
        $tipo = $request->input('tipo_mantenimiento');

        return response()->json([
            'status' => 'success',
            'mensaje' => "Planificación exitosa. Se agendó el mantenimiento preventivo para el equipo {$codigo}.",
            'programacion' => [
                'codigo_equipo' => $codigo,
                'fecha_agendada' => $fecha,
                'tipo_revision' => $tipo,
                'estado_alerta' => 'Mantenimiento Programado',
                'responsable_notificado' => 'Jefe de Mecánicos'
            ]
        ]);
    }
}