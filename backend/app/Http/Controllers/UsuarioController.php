<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    use AuditoriaTrait;

    public function index()
    {
        $usuarios = User::with('empleado.persona', 'rol')
            ->where('estadoA', true)
            ->orderBy('id_usuario')
            ->get()
            ->map(function ($user) {
                return $this->formatearUsuario($user);
            });

        return response()->json(['usuarios' => $usuarios]);
    }

    public function show($id)
    {
        $user = User::with('empleado.persona', 'rol')->findOrFail($id);

        return response()->json(['usuario' => $this->formatearUsuario($user)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|string|max:255',
            'primer_nombre' => 'required|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:255',
            'cargo' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:TUsuarios,username',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:TRoles,id_rol',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $persona = Persona::create([
                'ci' => $validated['ci'],
                'primer_nombre' => $validated['primer_nombre'],
                'segundo_nombre' => $validated['segundo_nombre'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'telefono' => $validated['telefono'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria('TPersonas', $persona->id_persona, 'I', null, null, null, 'Creacion de persona para nuevo usuario');

            $empleado = Empleado::create([
                'id_persona' => $persona->id_persona,
                'fecha_ingreso' => now()->toDateString(),
                'sueldo_base' => 0,
                'cargo' => $validated['cargo'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria('TEmpleados', $empleado->id_empleado, 'I', null, null, null, 'Creacion de empleado para nuevo usuario');

            $user = User::create([
                'username' => $validated['username'],
                'password_hash' => Hash::make($validated['password']),
                'id_empleado' => $empleado->id_empleado,
                'id_rol' => $validated['id_rol'],
                'estadoA' => true,
                'usuarioA' => $usuarioId,
                'fechahoraA' => now(),
            ]);

            $this->registrarAuditoria('TUsuarios', $user->id_usuario, 'I', null, null, null, 'Creacion de usuario');

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'usuario' => $this->formatearUsuario($user->load('empleado.persona', 'rol')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'ci' => 'sometimes|string|max:255',
            'primer_nombre' => 'sometimes|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'apellido_paterno' => 'sometimes|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:255',
            'cargo' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:TUsuarios,username,' . $id . ',id_usuario',
            'password' => 'nullable|string|min:6',
            'id_rol' => 'sometimes|exists:TRoles,id_rol',
        ]);

        $usuarioId = auth()->id();

        try {
            DB::beginTransaction();

            $empleado = $user->empleado;
            $persona = $empleado?->persona;

            if ($persona && $request->hasAny(['ci', 'primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'telefono'])) {
                $cambios = [];
                foreach (['ci', 'primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'telefono'] as $campo) {
                    if ($request->has($campo) && $request->$campo !== $persona->$campo) {
                        $cambios[$campo] = $request->$campo;
                    }
                }
                if (!empty($cambios)) {
                    $cambios['usuarioA'] = $usuarioId;
                    $cambios['fechahoraA'] = now();
                    foreach ($cambios as $campo => $valorNuevo) {
                        if ($campo === 'usuarioA' || $campo === 'fechahoraA') continue;
                        $this->registrarAuditoria('TPersonas', $persona->id_persona, 'U', $campo, $persona->$campo, $valorNuevo, 'Actualizacion de persona');
                    }
                    $persona->update($cambios);
                }
            }

            if ($empleado && $request->has('cargo')) {
                $cargoAnterior = $empleado->cargo;
                if ($request->cargo !== $cargoAnterior) {
                    $empleado->update([
                        'cargo' => $request->cargo,
                        'usuarioA' => $usuarioId,
                        'fechahoraA' => now(),
                    ]);
                    $this->registrarAuditoria('TEmpleados', $empleado->id_empleado, 'U', 'cargo', $cargoAnterior, $request->cargo, 'Actualizacion de cargo');
                }
            }

            $datosUser = [];
            if ($request->has('username') && $request->username !== $user->username) {
                $datosUser['username'] = $request->username;
            }
            if ($request->filled('password')) {
                $datosUser['password_hash'] = Hash::make($request->password);
            }
            if ($request->has('id_rol') && $request->id_rol != $user->id_rol) {
                $datosUser['id_rol'] = $request->id_rol;
            }

            if (!empty($datosUser)) {
                $datosUser['usuarioA'] = $usuarioId;
                $datosUser['fechahoraA'] = now();
                foreach ($datosUser as $campo => $valorNuevo) {
                    if ($campo === 'usuarioA' || $campo === 'fechahoraA') continue;
                    $campoReal = $campo === 'password_hash' ? 'password_hash' : $campo;
                    $valorAnterior = $campoReal === 'password_hash' ? '[oculto]' : $user->$campoReal;
                    $this->registrarAuditoria('TUsuarios', $user->id_usuario, 'U', $campoReal === 'password_hash' ? 'password' : $campoReal, $valorAnterior, $campoReal === 'password_hash' ? '[oculto]' : $valorNuevo, 'Actualizacion de usuario');
                }
                $user->update($datosUser);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $this->formatearUsuario($user->fresh()->load('empleado.persona', 'rol')),
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

    public function roles()
    {
        $roles = Rol::activos()->orderBy('nombre')->get(['id_rol', 'nombre', 'descripcion']);

        return response()->json(['roles' => $roles]);
    }

    private function formatearUsuario(User $user)
    {
        $persona = $user->empleado?->persona;

        $nombreCompleto = '';
        if ($persona) {
            $parts = array_filter([
                $persona->primer_nombre,
                $persona->segundo_nombre,
                $persona->apellido_paterno,
                $persona->apellido_materno,
            ]);
            $nombreCompleto = implode(' ', $parts);
        }

        return [
            'id_usuario' => $user->id_usuario,
            'username' => $user->username,
            'id_rol' => $user->id_rol,
            'rol' => $user->rol?->nombre,
            'id_empleado' => $user->id_empleado,
            'cargo' => $user->empleado?->cargo,
            'nombre_completo' => $nombreCompleto,
            'ci' => $persona?->ci,
            'telefono' => $persona?->telefono,
            'primer_nombre' => $persona?->primer_nombre,
            'segundo_nombre' => $persona?->segundo_nombre,
            'apellido_paterno' => $persona?->apellido_paterno,
            'apellido_materno' => $persona?->apellido_materno,
            'fecha_nacimiento' => $persona?->fecha_nacimiento,
            'estadoA' => $user->estadoA,
        ];
    }
}
