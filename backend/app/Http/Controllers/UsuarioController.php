<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\OrdenTrabajo;
use App\Models\Rol;
use App\Models\User; // 🚀 Tu modelo se llama User
use App\Traits\AuditoriaTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    use AuditoriaTrait;

    public function index(Request $request)
    {
        $inactivos = $request->boolean('inactivos');

        $query = User::with('empleado.programaciones', 'empleado.ultimaPlanilla', 'roles')
            ->orderBy('id_usuario');

        if ($inactivos) {
            $query->where('estadoA', false);
        } else {
            $query->where('estadoA', true);
        }

        $usuarios = $query->get()->map(function ($user) {
            return $this->formatearUsuario($user);
        });

        return response()->json(['usuarios' => $usuarios]);
    }

    public function show($id)
    {
        $user = User::with('empleado', 'roles')->findOrFail($id);

        return response()->json(['usuario' => $this->formatearUsuario($user)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|string|min:7|max:8|regex:/^\d+$/|unique:TEmpleados,ci',
            'primer_nombre' => 'required|string|min:2|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'apellido_paterno' => 'required|string|min:2|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'telefono' => 'required|string|regex:/^[67]\d{6,7}$/',
            'cargo' => 'required|string|min:2|max:255',
            'sueldo_base' => 'nullable|numeric|min:0',
            'username' => 'required|string|min:3|max:255|unique:TUsuarios,username',
            'password' => 'required|string|min:6',
            'roles' => 'required|array',
            'roles.*' => 'exists:TRoles,id_rol',
        ], [
            'ci.regex' => 'La cédula de identidad debe contener solo números',
            'ci.unique' => 'Esta cédula de identidad ya está registrada',
            'ci.min' => 'La cédula debe tener 7 u 8 dígitos',
            'ci.max' => 'La cédula debe tener 7 u 8 dígitos',
            'telefono.regex' => 'Debe empezar con 6 o 7 y tener 7 u 8 dígitos',
            'telefono.required' => 'El teléfono es requerido',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura',
            'primer_nombre.min' => 'El primer nombre debe tener al menos 2 caracteres',
            'apellido_paterno.min' => 'El apellido paterno debe tener al menos 2 caracteres',
            'cargo.min' => 'El cargo debe tener al menos 2 caracteres',
            'username.min' => 'El usuario debe tener al menos 3 caracteres',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $empleado = Empleado::create([
                'ci' => $validated['ci'],
                'primer_nombre' => $validated['primer_nombre'],
                'segundo_nombre' => $validated['segundo_nombre'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'telefono' => $validated['telefono'],
                'fecha_ingreso' => now()->toDateString(),
                'sueldo_base' => $validated['sueldo_base'] ?? 0,
                'cargo' => $validated['cargo'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $valoresEmpleado = implode('|', [
                $validated['ci'],
                $validated['primer_nombre'],
                $validated['segundo_nombre'] ?? '',
                $validated['apellido_paterno'],
                $validated['apellido_materno'] ?? '',
                $validated['fecha_nacimiento'] ?? '',
                $validated['telefono'] ?? '',
                $validated['cargo'],
                now()->toDateString(),
                '0',
            ]);
            $this->registrarAuditoria('TEmpleados', $empleado->id_empleado, 'I', null, null, $valoresEmpleado, 'Creacion de empleado para nuevo usuario');

            $user = User::create([
                'username' => $validated['username'],
                'password_hash' => Hash::make($validated['password']),
                'id_empleado' => $empleado->id_empleado,
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $user->roles()->attach($validated['roles']);

            $valoresUsuario = implode('|', [
                $validated['username'],
                implode(',', $validated['roles']),
            ]);
            $this->registrarAuditoria('TUsuarios', $user->id_usuario, 'I', null, null, $valoresUsuario, 'Creacion de usuario');

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $this->formatearUsuario($user->load('empleado', 'roles')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::with('empleado', 'roles')->findOrFail($id);
        $empleadoId = $user->empleado?->id_empleado;

        $validated = $request->validate([
            'ci' => 'sometimes|string|min:7|max:8|regex:/^\d+$/|unique:TEmpleados,ci,' . $empleadoId . ',id_empleado',
            'primer_nombre' => 'sometimes|string|min:2|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'apellido_paterno' => 'sometimes|string|min:2|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'telefono' => 'sometimes|string|regex:/^[67]\d{6,7}$/',
            'cargo' => 'sometimes|string|min:2|max:255',
            'username' => 'sometimes|string|min:3|max:255|unique:TUsuarios,username,' . $id . ',id_usuario',
            'password' => 'nullable|string|min:6',
            'sueldo_base' => 'nullable|numeric|min:0',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:TRoles,id_rol',
        ], [
            'ci.regex' => 'La cédula de identidad debe contener solo números',
            'ci.unique' => 'Esta cédula de identidad ya está registrada',
            'ci.min' => 'La cédula debe tener 7 u 8 dígitos',
            'ci.max' => 'La cédula debe tener 7 u 8 dígitos',
            'telefono.regex' => 'Debe empezar con 6 o 7 y tener 7 u 8 dígitos',
            'telefono.required' => 'El teléfono es requerido',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura',
            'primer_nombre.min' => 'El primer nombre debe tener al menos 2 caracteres',
            'apellido_paterno.min' => 'El apellido paterno debe tener al menos 2 caracteres',
            'cargo.min' => 'El cargo debe tener al menos 2 caracteres',
            'username.min' => 'El usuario debe tener al menos 3 caracteres',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $empleado = $user->empleado;

            if ($empleado && $request->hasAny(['ci', 'primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'telefono', 'cargo', 'sueldo_base'])) {
                $cambios = [];
                $camposAudit = [];
                $valoresAnt = [];
                $valoresNue = [];

                $camposPersona = ['ci', 'primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'telefono'];

                foreach ($camposPersona as $campo) {
                    if ($request->has($campo) && $request->$campo !== $empleado->$campo) {
                        $cambios[$campo] = $request->$campo;
                        $camposAudit[] = $campo;
                        $valoresAnt[] = $empleado->$campo ?? '';
                        $valoresNue[] = $request->$campo;
                    }
                }

                if ($request->has('cargo') && $request->cargo !== $empleado->cargo) {
                    $cambios['cargo'] = $request->cargo;
                    $camposAudit[] = 'cargo';
                    $valoresAnt[] = $empleado->cargo ?? '';
                    $valoresNue[] = $request->cargo;
                }

                if ($request->has('sueldo_base') && (float) $request->sueldo_base !== (float) $empleado->sueldo_base) {
                    $cambios['sueldo_base'] = $request->sueldo_base;
                    $camposAudit[] = 'sueldo_base';
                    $valoresAnt[] = (string) $empleado->sueldo_base;
                    $valoresNue[] = (string) $request->sueldo_base;
                }

                if (!empty($cambios)) {
                    $cambios['usuarioA'] = $usuarioId;
                    $cambios['fechahoraA'] = now();
                    $empleado->update($cambios);
                    $this->registrarAuditoria('TEmpleados', $empleado->id_empleado, 'U', implode('|', $camposAudit), implode('|', $valoresAnt), implode('|', $valoresNue), 'Actualizacion de empleado');
                }
            }

            $datosUser = [];
            $camposUserAudit = [];
            $valoresUserAnt = [];
            $valoresUserNue = [];

            if ($request->has('username') && $request->username !== $user->username) {
                $datosUser['username'] = $request->username;
                $camposUserAudit[] = 'username';
                $valoresUserAnt[] = $user->username;
                $valoresUserNue[] = $request->username;
            }
            if ($request->filled('password')) {
                $datosUser['password_hash'] = Hash::make($request->password);
                $camposUserAudit[] = 'password';
                $valoresUserAnt[] = '[oculto]';
                $valoresUserNue[] = '[oculto]';
            }
            if ($request->has('roles')) {
                $rolesActuales = $user->roles->pluck('id_rol')->toArray();
                $rolesNuevos = $request->roles;
                if ($rolesActuales !== $rolesNuevos) {
                    $user->roles()->sync($rolesNuevos);
                    $camposUserAudit[] = 'roles';
                    $valoresUserAnt[] = implode(',', $rolesActuales);
                    $valoresUserNue[] = implode(',', $rolesNuevos);
                }
            }

            if (!empty($datosUser)) {
                $datosUser['usuarioA'] = $usuarioId;
                $datosUser['fechahoraA'] = now();
                $user->update($datosUser);
            }

            if (!empty($camposUserAudit)) {
                $this->registrarAuditoria('TUsuarios', $user->id_usuario, 'U', implode('|', $camposUserAudit), implode('|', $valoresUserAnt), implode('|', $valoresUserNue), 'Actualizacion de usuario');
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $this->formatearUsuario($user->fresh()->load('empleado', 'roles')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id_usuario === 1) {
            return response()->json(['message' => 'No se puede desactivar al administrador principal'], 403);
        }

        $user->update([
            'estadoA' => false,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TUsuarios', $user->id_usuario, 'U', 'estadoA', '1', '0', 'Desactivacion de usuario');

        return response()->json(['message' => 'Usuario desactivado exitosamente']);
    }

    public function reactivar($id)
    {
        $user = User::findOrFail($id);

        if ($user->estadoA) {
            return response()->json(['message' => 'El usuario ya está activo'], 422);
        }

        $user->update([
            'estadoA' => true,
            'usuarioA' => auth()->id(),
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria('TUsuarios', $user->id_usuario, 'U', 'estadoA', '0', '1', 'Reactivacion de usuario');

        return response()->json(['message' => 'Usuario reactivado exitosamente']);
    }

    public function ordenesActivas($id)
    {
        $user = User::with('empleado')->findOrFail($id);

        if (!$user->id_empleado) {
            return response()->json(['tiene_ordenes' => false, 'ordenes' => []]);
        }

        $ordenes = OrdenTrabajo::where('id_empleado', $user->id_empleado)
            ->where('estadoA', true)
            ->whereNotIn('estado', ['completada', 'entregado'])
            ->get(['id_orden', 'nro_orden', 'estado', 'fecha_ingreso']);

        return response()->json([
            'tiene_ordenes' => $ordenes->isNotEmpty(),
            'ordenes' => $ordenes,
        ]);
    }

    public function roles()
    {
        $roles = Rol::activos()->orderBy('nombre')->get(['id_rol', 'nombre', 'descripcion']);

        return response()->json(['roles' => $roles]);
    }

    // 📦 Cambio de tu compañero integrado (Reporte PDF)
    public function reportePdf(Request $request)
    {
        $busqueda = $request->input('busqueda', '');
        $rol = $request->input('rol', '');
        $inactivos = $request->boolean('inactivos');
        $tipo = $request->input('tipo', 'usuarios');

        $query = User::with('empleado', 'roles')
            ->orderBy('id_usuario');

        if ($tipo === 'pagos') {
            $query = User::with('empleado.ultimaPlanilla', 'roles');
        }

        if ($inactivos) {
            $query->where('estadoA', false);
        } else {
            $query->where('estadoA', true);
        }

        if ($rol) {
            $query->whereHas('roles', function ($q) use ($rol) {
                $q->where('TRoles.id_rol', $rol);
            });
        }

        if ($busqueda) {
            $q = strtolower($busqueda);
            $query->where(function ($query) use ($q) {
                $query->whereHas('empleado', function ($sub) use ($q) {
                    $sub->whereRaw("LOWER(CONCAT(primer_nombre, ' ', IFNULL(segundo_nombre,''), ' ', apellido_paterno, ' ', IFNULL(apellido_materno,''))) LIKE ?", ["%{$q}%"])
                        ->orWhereRaw("LOWER(ci) LIKE ?", ["%{$q}%"]);
                })->orWhereRaw("LOWER(username) LIKE ?", ["%{$q}%"]);
            });
        }

        $usuarios = $query->get()->map(function ($user) {
            return $this->formatearUsuario($user);
        });

        $rolNombre = '';
        if ($rol) {
            $rolObj = Rol::find($rol);
            $rolNombre = $rolObj ? $rolObj->nombre : '';
        }

        $filtros = [
            'busqueda' => $busqueda,
            'rol' => $rolNombre,
            'estado' => $inactivos ? 'Inactivos' : 'Activos',
        ];

        $logoPath = public_path('img/Logo3_NovaRider.png');
        $logoExists = file_exists($logoPath);
        $logoBase64 = $logoExists ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $viewName = $tipo === 'pagos' ? 'reportes.usuarios_pagos_pdf' : 'reportes.usuarios_pdf';

        $pdf = Pdf::loadView($viewName, [
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'usuarioGenera' => auth()->user()->username ?? 'Sistema',
            'logoBase64' => $logoBase64,
            'totalRegistros' => $usuarios->count(),
        ]);

        $filename = $tipo === 'pagos'
            ? 'reporte_pagos_' . now()->format('Y-m-d_His') . '.pdf'
            : 'reporte_usuarios_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->stream($filename);
    }

    // 🚀 Tu cambio integrado (Mecánicos Operativos del Taller)
    public function obtenerMecanicos()
    {
        $mecanicos = User::whereHas('roles', function($query) {
                $query->where('TRoles.id_rol', 3); // Filtra los usuarios con rol de mecánico
            })
            ->where('estadoA', true) // Asegura que el usuario esté activo
            ->with(['empleado' => function($query) {
                $query->select('id_empleado', 'primer_nombre', 'apellido_paterno');
            }])
            ->get()
            ->map(function($user) {
                return [
                    'id_empleado' => $user->id_usuario, // Usamos el id_usuario para asociar las acciones en Vue
                    'primer_nombre' => $user->empleado?->primer_nombre ?? 'Sin nombre',
                    'apellido_paterno' => $user->empleado?->apellido_paterno ?? ''
                ];
            });

        return response()->json($mecanicos);
    }

    private function formatearUsuario(User $user)
    {
        $empleado = $user->empleado;

        $nombreCompleto = '';
        if ($empleado) {
            $parts = array_filter([
                $empleado->primer_nombre,
                $empleado->segundo_nombre,
                $empleado->apellido_paterno,
                $empleado->apellido_materno,
            ]);
            $nombreCompleto = implode(' ', $parts);
        }

        $roles = $user->roles ?? collect();

        return [
            'id_usuario' => $user->id_usuario,
            'username' => $user->username,
            'roles' => $roles->toArray(),
            'rol' => $roles->pluck('nombre')->implode(', '),
            'id_empleado' => $user->id_empleado,
            'cargo' => $empleado?->cargo,
            'sueldo_base' => $empleado?->sueldo_base ? (float) $empleado->sueldo_base : 0,
            'nombre_completo' => $nombreCompleto,
            'ci' => $empleado?->ci,
            'telefono' => $empleado?->telefono,
            'primer_nombre' => $empleado?->primer_nombre,
            'segundo_nombre' => $empleado?->segundo_nombre,
            'apellido_paterno' => $empleado?->apellido_paterno,
            'apellido_materno' => $empleado?->apellido_materno,
            'fecha_nacimiento' => $empleado?->fecha_nacimiento,
            'fecha_ingreso' => $empleado?->fecha_ingreso,
            'estadoA' => $user->estadoA,
            'has_horario' => $empleado && $empleado?->programaciones?->where('estadoA', true)->count() > 0,
            'ultimo_pago' => $empleado && $empleado->ultimaPlanilla ? [
                'mes' => $empleado->ultimaPlanilla->mes,
                'anio' => $empleado->ultimaPlanilla->anio,
                'sueldo_neto' => (float) $empleado->ultimaPlanilla->sueldo_neto,
            ] : null,
        ];
    }
}