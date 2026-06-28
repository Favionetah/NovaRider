<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\MotocicletaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\ProgramacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaController;
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
        Route::get('/usuarios/reporte/pdf', [UsuarioController::class, 'reportePdf']);

        Route::get('/proveedores', [ProveedorController::class, 'index']);
        Route::post('/proveedores', [ProveedorController::class, 'store']);
        Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
        Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
        Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);

        Route::get('/compras', [CompraController::class, 'index']);
        Route::post('/compras', [CompraController::class, 'store']);
        Route::get('/compras/{id}', [CompraController::class, 'show']);
        Route::get('/compras/reporte/pdf', [CompraController::class, 'reportePdf']);

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

        Route::get('/reportes/data', [ReporteController::class, 'data']);
        Route::get('/reportes/pdf', [ReporteController::class, 'exportPdf']);

        Route::get('/productos', function () {
            $productos = Producto::where('estadoA', true)
                ->orderBy('nombre')
                ->get(['id_producto', 'nombre', 'precio_venta', 'costo', 'stock_disponible', 'stock_fisico']);
            return response()->json(['productos' => $productos]);
        });
    });

    Route::middleware('role:1,3,4')->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index']);
        Route::post('/clientes', [ClienteController::class, 'store']);
        Route::get('/clientes/{id}', [ClienteController::class, 'show']);
        Route::put('/clientes/{id}', [ClienteController::class, 'update']);
        Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
        Route::put('/clientes/{id}/reactivar', [ClienteController::class, 'reactivar']);

        Route::get('/clientes-lista', function () {
            $clientes = \App\Models\Cliente::where('estadoA', true)
                ->orderBy('primer_nombre')
                ->get(['id_cliente', 'primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'ci', 'telefono']);
            return response()->json(['clientes' => $clientes]);
        });

        Route::get('/reservas', [ReservaController::class, 'index']);
        Route::post('/reservas', [ReservaController::class, 'store']);
        Route::get('/reservas/{id}', [ReservaController::class, 'show']);
        Route::put('/reservas/{id}', [ReservaController::class, 'update']);
        Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);
        Route::post('/reservas/{id}/convertir-venta', [ReservaController::class, 'convertirVenta']);
        Route::post('/reservas/{id}/registrar-envio', [ReservaController::class, 'registrarEnvio']);
    });

    Route::middleware('role:1,3')->group(function () {
        Route::get('/motocicletas', [MotocicletaController::class, 'index']);
        Route::post('/motocicletas', [MotocicletaController::class, 'store']);
        Route::get('/motocicletas/{id}', [MotocicletaController::class, 'show']);
        Route::put('/motocicletas/{id}', [MotocicletaController::class, 'update']);
        Route::delete('/motocicletas/{id}', [MotocicletaController::class, 'destroy']);
        Route::put('/motocicletas/{id}/reactivar', [MotocicletaController::class, 'reactivar']);
        Route::get('/motocicletas/{id}/historial', [MotocicletaController::class, 'historial']);
    });
});
