<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\ProgramacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UsuarioController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/cambiar-contrasena', [AuthController::class, 'cambiarContrasena']);

    Route::middleware('role:1')->group(function () {
        Route::get('/roles', [UsuarioController::class, 'roles']);
        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);
        Route::put('/usuarios/{id}/reactivar', [UsuarioController::class, 'reactivar']);

        Route::get('/proveedores', [ProveedorController::class, 'index']);
        Route::post('/proveedores', [ProveedorController::class, 'store']);
        Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
        Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
        Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);

        Route::get('/compras', [CompraController::class, 'index']);
        Route::post('/compras', [CompraController::class, 'store']);
        Route::get('/compras/{id}', [CompraController::class, 'show']);

        Route::get('/turnos', [TurnoController::class, 'index']);
        Route::post('/turnos', [TurnoController::class, 'store']);
        Route::post('/turnos/registrar', [TurnoController::class, 'registrar']);
        Route::put('/turnos/{id}', [TurnoController::class, 'update']);

        Route::get('/planillas', [PlanillaController::class, 'index']);
        Route::post('/planillas', [PlanillaController::class, 'store']);
        Route::delete('/planillas/{id}', [PlanillaController::class, 'destroy']);
        Route::get('/planillas/resumen', [PlanillaController::class, 'resumen']);

        Route::get('/programaciones', [ProgramacionController::class, 'index']);
        Route::post('/programaciones', [ProgramacionController::class, 'store']);
        Route::get('/programaciones/global', [ProgramacionController::class, 'global']);

        Route::get('/productos', function () {
            $productos = Producto::where('estadoA', true)
                ->orderBy('nombre')
                ->get(['id_producto', 'nombre', 'stock_disponible']);
            return response()->json(['productos' => $productos]);
        });
    });
});
