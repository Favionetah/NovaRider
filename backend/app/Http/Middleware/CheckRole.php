<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->roles()->whereIn('TRoles.id_rol', $roles)->exists()) {
            return response()->json(['message' => 'No tienes permiso para acceder a este recurso'], 403);
        }

        return $next($request);
    }
}
