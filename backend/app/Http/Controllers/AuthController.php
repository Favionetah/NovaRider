<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use AuditoriaTrait;

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
            'estadoA' => true,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        $user = Auth::user();

        $this->registrarAuditoria(
            'TUsuarios',
            $user->id_usuario,
            'I',
            null,
            null,
            null,
            'Inicio de sesion'
        );

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => $this->cargarUsuario($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        $this->registrarAuditoria(
            'TUsuarios',
            $user->id_usuario,
            'U',
            null,
            null,
            null,
            'Cierre de sesion'
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function me()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        return response()->json([
            'user' => $this->cargarUsuario($user),
        ]);
    }

    public function cambiarContrasena(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_actual, $user->password_hash)) {
            return response()->json([
                'message' => 'La contraseña actual no es correcta',
            ], 422);
        }

        $user->update([
            'password_hash' => Hash::make($request->password),
            'usuarioA' => $user->id_usuario,
            'fechahoraA' => now(),
        ]);

        $this->registrarAuditoria(
            'TUsuarios',
            $user->id_usuario,
            'U',
            'password',
            '[oculto]',
            '[oculto]',
            'Cambio de contrasena'
        );

        return response()->json(['message' => 'Contraseña actualizada exitosamente']);
    }

    private function cargarUsuario(User $user)
    {
        $user->load('rol');

        $usuario = [
            'id_usuario' => $user->id_usuario,
            'username' => $user->username,
            'id_rol' => $user->id_rol,
            'rol' => $user->rol ? $user->rol->nombre : null,
            'id_empleado' => $user->id_empleado,
            'estadoA' => $user->estadoA,
        ];

        $modulos = [
            [
                'id' => 'usuarios',
                'nombre' => 'Gestión de Usuarios',
                'descripcion' => 'Administrar usuarios del sistema',
                'ruta' => '/usuarios',
                'color' => '#741102',
                'roles_permitidos' => [1],
            ],
            [
                'id' => 'clientes',
                'nombre' => 'Clientes y Vehículos',
                'descripcion' => 'Registro de clientes y motocicletas',
                'ruta' => null,
                'color' => '#042D29',
                'roles_permitidos' => [1, 3, 4],
            ],
            [
                'id' => 'taller',
                'nombre' => 'Taller y Reparaciones',
                'descripcion' => 'Órdenes de trabajo y seguimiento',
                'ruta' => null,
                'color' => '#042D29',
                'roles_permitidos' => [1, 3],
            ],
            [
                'id' => 'inventario',
                'nombre' => 'Inventario',
                'descripcion' => 'Productos, repuestos y stock',
                'ruta' => null,
                'color' => '#042D29',
                'roles_permitidos' => [1, 2],
            ],
            [
                'id' => 'ventas',
                'nombre' => 'Ventas y Caja',
                'descripcion' => 'Registro de ventas y arqueo de caja',
                'ruta' => null,
                'color' => '#042D29',
                'roles_permitidos' => [1, 2],
            ],
            [
                'id' => 'compras',
                'nombre' => 'Compras y Reportes',
                'descripcion' => 'Proveedores, reservas y empleados',
                'ruta' => null,
                'color' => '#042D29',
                'roles_permitidos' => [1],
            ],
        ];

        $usuario['modulos'] = array_values(array_filter($modulos, function ($m) use ($user) {
            return in_array($user->id_rol, $m['roles_permitidos']);
        }));

        return $usuario;
    }
}
