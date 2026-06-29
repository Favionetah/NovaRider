<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\AuditoriaTrait;
use Barryvdh\DomPDF\Facades\Pdf; 

class OrdenController extends Controller
{
    use AuditoriaTrait;

    // 🔍 CORREGIDO: Ahora incluye id_motocicleta e id_empleado para que Vue pueda usarlos al modificar
    public function index()
    {
        try {
            $ordenesRaw = DB::table('tordenestrabajo as o') 
                ->join('tmotocicletas as m', 'o.id_motocicleta', '=', 'm.id_motocicleta')
                ->join('templeados as e', 'o.id_empleado', '=', 'e.id_empleado')
                ->select([
                    'o.id_orden',
                    'o.nro_orden',
                    'o.fecha_ingreso',
                    'o.estado',
                    'o.condicion_entrada', // Por si lo necesitas en el modal
                    'o.id_motocicleta',    // <-- ID Crítico agregado
                    'o.id_empleado',       // <-- ID Crítico agregado
                    'm.placa as moto_placa',
                    'e.primer_nombre as emp_nombre',
                    'e.apellido_paterno as emp_apellido'
                ])
                ->where('o.estadoA', true)
                ->orderBy('o.id_orden', 'desc')
                ->get();

            $ordenesFormateadas = $ordenesRaw->map(function ($orden) {
                return [
                    'id_orden'         => $orden->id_orden,
                    'nro_orden'        => $orden->nro_orden,
                    'fecha_ingreso'    => $orden->fecha_ingreso,
                    'estado'           => $orden->estado,
                    'condicion_entrada'=> $orden->condicion_entrada ?? '',
                    'id_motocicleta'   => $orden->id_motocicleta, // <-- Enviado a Vue
                    'id_empleado'      => $orden->id_empleado,    // <-- Enviado a Vue
                    'motocicleta'   => [
                        'id_motocicleta' => $orden->id_motocicleta,
                        'placa'          => $orden->moto_placa
                    ],
                    'empleado'      => [
                        'id_empleado'      => $orden->id_empleado,
                        'primer_nombre'    => $orden->emp_nombre,
                        'apellido_paterno' => $orden->emp_apellido
                    ]
                ];
            });

            return response()->json(['ordenes' => $ordenesFormateadas]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error en index: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_motocicleta'    => 'required|integer',
            'id_empleado'       => 'required|integer',
            'nro_orden'         => 'required|string|max:50',
            'fecha_ingreso'     => 'required|date',
            'estado'            => 'required|string|max:255',
            'condicion_entrada' => 'required|string',
        ]);

        $usuarioId = auth()->id() ?? 1;

        try {
            DB::beginTransaction();

            $idOrden = DB::table('tordenestrabajo')->insertGetId([
                'id_motocicleta'    => $validated['id_motocicleta'],
                'id_empleado'       => $validated['id_empleado'],
                'nro_orden'         => $validated['nro_orden'],
                'fecha_ingreso'     => $validated['fecha_ingreso'],
                'estado'            => $validated['estado'],            
                'condicion_entrada' => $validated['condicion_entrada'], 
                'estadoA'           => true,
                'usuarioA'          => $usuarioId,
                'fechahoraA'        => now(),
            ]);

            if (method_exists($this, 'registrarAuditoria')) {
                $this->registrarAuditoria('tordenes_trabajo', $idOrden, 'I', null, null, $validated['nro_orden'], 'Registro de orden');
            }

            DB::commit();

            return response()->json([
                'message' => 'Orden de trabajo guardada exitosamente',
                'id_orden' => $idOrden
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error en store: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $usuarioId = auth()->id() ?? 1; 

        // 🚨 DIAGNÓSTICO 1: Ver qué datos exactos están llegando desde Vue al servidor
        // Si quieres verlos de golpe en la consola, puedes descomentar la línea de abajo:
        // return response()->json(['datos_recibidos' => $request->all()], 400);

        try {
            DB::beginTransaction();

            $anterior = DB::table('tordenestrabajo')->where('id_orden', $id)->first();

            if (!$anterior) {
                return response()->json(['message' => 'La orden seleccionada no existe en la BD.'], 404);
            }

            // 🚨 DIAGNÓSTICO 2: Ejecución directa sin pasar por el Validator intermedio para aislar el error
            DB::table('tordenestrabajo')
                ->where('id_orden', $id)
                ->update([
                    'id_motocicleta'    => $request->input('id_motocicleta'),
                    'id_empleado'       => $request->input('id_empleado'),
                    'fecha_ingreso'     => $request->input('fecha_ingreso'),
                    'estado'            => $request->input('estado'),
                    'condicion_entrada' => $request->input('condicion_entrada'),
                    'usuarioA'          => $usuarioId, 
                    'fechahoraA'        => now(),
                ]);

            if (method_exists($this, 'registrarAuditoria')) {
                $nroOrden = $anterior->nro_orden ?? 'N/A';
                $this->registrarAuditoria('tordenes_trabajo', $id, 'U', json_encode($anterior), null, $nroOrden, 'Modificación de orden');
            }

            DB::commit();
            return response()->json(['message' => 'Orden de trabajo actualizada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // 🚨 DIAGNÓSTICO 3: Esto te va a devolver el error real del motor MySQL o de código PHP
            return response()->json([
                'error_servidor' => true,
                'mensaje_error'  => $e->getMessage(),
                'archivo'        => $e->getFile(),
                'linea'          => $e->getLine()
            ], 500);
        }
    }

    // 🚀 CORREGIDO: Evita problemas si el registro de auditoría falla
    public function destroy($id)
    {
        $usuarioId = auth()->id() ?? 1; 

        try {
            DB::beginTransaction();

            $anterior = DB::table('tordenestrabajo')->where('id_orden', $id)->first();

            if (!$anterior) {
                return response()->json(['message' => 'La orden que intenta eliminar no existe.'], 404);
            }

            DB::table('tordenestrabajo')
                ->where('id_orden', $id)
                ->update([
                    'estadoA'    => false, 
                    'usuarioA'   => $usuarioId, 
                    'fechahoraA' => now(),
                ]);

            if (method_exists($this, 'registrarAuditoria')) {
                $nroOrden = $anterior->nro_orden ?? 'N/A';
                try {
                    $this->registrarAuditoria('tordenes_trabajo', $id, 'D', json_encode($anterior), null, $nroOrden, 'Eliminación lógica de orden');
                } catch (\Exception $aud) {
                    // Si falla la auditoría por codificación del JSON, no detiene la eliminación
                }
            }

            DB::commit();
            return response()->json(['message' => 'Orden de trabajo eliminada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al borrar orden en el backend.',
                'error_exacto' => $e->getMessage()
            ], 500);
        }
    }

    public function guardarListaVerificacion(Request $request)
{
    // Validar que los campos coincidan con el formulario
    $request->validate([
        'id_orden'         => 'required',
        'frenos_revisados' => 'required|boolean',
        'luces_revisadas'  => 'required|boolean',
        'piezas_ajustadas' => 'required|boolean',
        'prueba_ruta'      => 'required|boolean',
        'kilometraje'      => 'required|numeric',
        'fecha_validacion' => 'required|date',
    ]);

    // 1. Guardar o actualizar en tlistasverificacion
    \DB::table('tlistasverificacion')->updateOrInsert(
        ['id_orden' => $request->id_orden],
        [
            'frenos_revisados' => $request->frenos_revisados,
            'luces_revisadas'  => $request->luces_revisadas,
            'piezas_ajustadas' => $request->piezas_ajustadas,
            'prueba_ruta'      => $request->prueba_ruta,
            'kilometraje'      => $request->kilometraje,
            'fecha_validacion' => $request->fecha_validacion,
        ]
    );

    // 2. Actualizar el estado en TOrdenesTrabajo usando el modelo correcto
    \App\Models\OrdenTrabajo::where('id_orden', $request->id_orden)->update([
        'estado' => 'Listo para entrega'
    ]);

    return response()->json(['success' => true, 'message' => 'Certificación registrada.']);
}
    public function cambiarEstado(Request $request, $id)
{
    try {
        $nuevoEstado = $request->input('estado', 'Listo para entrega');

        // Actualizamos directamente en la tabla
        $actualizado = DB::table('tordenestrabajo')
            ->where('id_orden', $id)
            ->update([
                'estado'     => $nuevoEstado,
                'usuarioA'   => auth()->id() ?? 1,
                'fechahoraA' => now()
            ]);

        if (!$actualizado) {
            return response()->json(['message' => 'No se pudo actualizar o la orden no existe.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado a ' . $nuevoEstado . ' correctamente.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error en el servidor al cambiar estado.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function reportePdf(Request $request)
{
    $busqueda = $request->input('busqueda', '');
    $estado = $request->input('estado', '');
    $id_mecanico = $request->input('id_mecanico', '');

    // Construimos la consulta base con sus relaciones
    $query = \App\Models\OrdenTrabajo::with(['motocicleta.cliente', 'empleado'])
        ->orderBy('fecha_ingreso', 'DESC');

    // 1. Filtro por Estado
    if ($estado) {
        $query->where('estado', $estado);
    }

    // 2. Filtro por Mecánico asignado (id_empleado en la orden)
    if ($id_mecanico) {
        $query->where('id_empleado', $id_mecanico);
    }

    // 3. Búsqueda por Placa o Nombre del Cliente
    if ($busqueda) {
        $q = strtolower($busqueda);
        $query->where(function ($query) use ($q) {
            $query->whereHas('motocicleta', function ($sub) use ($q) {
                $sub->whereRaw("LOWER(placa) LIKE ?", ["%{$q}%"])
                    ->orWhereHas('cliente', function ($clienteSub) use ($q) {
                        $clienteSub->whereRaw("LOWER(CONCAT(primer_nombre, ' ', apellido_paterno)) LIKE ?", ["%{$q}%"]);
                    });
            });
        });
    }

    $ordenes = $query->get();

    // Obtener nombre del mecánico para los filtros del encabezado si aplica
    $mecanicoNombre = 'Todos';
    if ($id_mecanico) {
        $mecanico = \App\Models\Empleado::find($id_mecanico);
        $mecanicoNombre = $mecanico ? $mecanico->primer_nombre . ' ' . $mecanico->apellido_paterno : 'Todos';
    }

    $filtros = [
        'busqueda' => $busqueda,
        'estado'   => $estado ? ucfirst($estado) : 'Todos',
        'mecanico' => $mecanicoNombre,
    ];

    // Carga del Logo en Base64
    $logoPath = public_path('img/Logo3_NovaRider.png');
    $logoExists = file_exists($logoPath);
    $logoBase64 = $logoExists ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';


// 🚀 CAMBIO EN LA LÍNEA 338: Llamamos a la clase por su ruta interna completa
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.ordenes_pdf', [
        'ordenes'         => $ordenes,
        'filtros'         => $filtros,
        'fechaGeneracion' => now()->format('d/m/Y H:i'),
        'usuarioGenera'   => auth()->user()->username ?? 'Sistema',
        'logoBase64'      => $logoBase64,
        'totalRegistros'  => $ordenes->count(),
    ]);

    $filename = 'reporte_ordenes_' . now()->format('Y-m-d_His') . '.pdf';

    if ($request->boolean('preview')) {
        return $pdf->stream($filename);
    }

    return $pdf->download($filename);
}
}