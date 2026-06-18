<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AuditoriaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    private function cargarUsuario(User $user)
    {
        $user->load('rol');

        return [
            'id_usuario' => $user->id_usuario,
            'username' => $user->username,
            'id_rol' => $user->id_rol,
            'rol' => $user->rol ? $user->rol->nombre : null,
            'id_empleado' => $user->id_empleado,
            'estadoA' => $user->estadoA,
        ];
    }
}
