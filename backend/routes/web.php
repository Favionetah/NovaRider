<?php

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\MotocicletaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\ProgramacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\EstanteController;
use App\Http\Controllers\ModelosCompatibleController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UsuarioController;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenController;

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

        Route::get('/reportes/stats', [ReporteController::class, 'systemStats']);
        Route::get('/reportes/data', [ReporteController::class, 'data']);
        Route::get('/reportes/pdf', [ReporteController::class, 'exportPdf']);

        // --- Módulo Caja y Ventas ---
        Route::get('/caja/estado', [CajaController::class, 'obtenerEstadoCaja']);
        Route::post('/caja/abrir', [CajaController::class, 'abrirCaja']);
        Route::post('/caja/ventas', [CajaController::class, 'crearRecibo']);
        Route::get('/caja/ventas', [CajaController::class, 'obtenerVentas']);
        Route::post('/caja/cerrar', [CajaController::class, 'cerrarCaja']);

        // --- Módulo Inventario (Roles 1, 2) ---
        Route::get('/ubicaciones/arbol', [EstanteController::class, 'arbol']);

        Route::get('/productos', [ProductoController::class, 'index']);
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::get('/productos/{id}/motocicletas-compatibles/pdf', [ProductoController::class, 'motocicletasCompatiblesPdf']);
        Route::get('/productos/{id}/motocicletas-compatibles', [ProductoController::class, 'motocicletasCompatibles']);
        Route::get('/productos/{id}/modelos', [ProductoController::class, 'modelos']);
        Route::post('/productos/{id}/modelos', [ProductoController::class, 'modelosSync']);
        Route::get('/productos/{id}', [ProductoController::class, 'show']);
        Route::put('/productos/{id}', [ProductoController::class, 'update']);
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
        Route::put('/productos/{id}/reactivar', [ProductoController::class, 'reactivar']);

        Route::get('/estantes', [EstanteController::class, 'index']);
        Route::post('/estantes', [EstanteController::class, 'store']);
        Route::get('/estantes/{id}', [EstanteController::class, 'show']);
        Route::put('/estantes/{id}', [EstanteController::class, 'update']);
        Route::delete('/estantes/{id}', [EstanteController::class, 'destroy']);
        Route::put('/estantes/{id}/reactivar', [EstanteController::class, 'reactivar']);

        Route::get('/modelos-compatibles', [ModelosCompatibleController::class, 'index']);
        Route::post('/modelos-compatibles', [ModelosCompatibleController::class, 'store']);
        Route::get('/modelos-compatibles/{id}', [ModelosCompatibleController::class, 'show']);
        Route::put('/modelos-compatibles/{id}', [ModelosCompatibleController::class, 'update']);
        Route::delete('/modelos-compatibles/{id}', [ModelosCompatibleController::class, 'destroy']);
        Route::put('/modelos-compatibles/{id}/reactivar', [ModelosCompatibleController::class, 'reactivar']);
    });

    // --- Grupo para Recepción y Gestión de Clientes (Roles 1, 3, 4) ---
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

    // --- Grupo Operativo de Taller (Roles 1, 3, 4) ---
    Route::middleware('role:1,3,4')->group(function () {
        Route::get('/motocicletas', [MotocicletaController::class, 'index']);
        Route::post('/motocicletas', [MotocicletaController::class, 'store']);
        Route::get('/motocicletas/{id}', [MotocicletaController::class, 'show']);
        Route::put('/motocicletas/{id}', [MotocicletaController::class, 'update']);
        Route::delete('/motocicletas/{id}', [MotocicletaController::class, 'destroy']);
        Route::put('/motocicletas/{id}/reactivar', [MotocicletaController::class, 'reactivar']);
        Route::get('/motocicletas/{id}/historial', [MotocicletaController::class, 'historial']);

        Route::get('/mecanicos', [UsuarioController::class, 'obtenerMecanicos']);

        Route::get('/programaciones', [ProgramacionController::class, 'index']);
        Route::post('/programaciones', [ProgramacionController::class, 'store']);
        Route::get('/programaciones/global', [ProgramacionController::class, 'global']);

        Route::get('/productos', function () {
            $productos = Producto::where('estadoA', true)->orderBy('nombre')->get(['id_producto', 'nombre', 'stock_disponible']);
            return response()->json(['productos' => $productos]);
        });

        // === Rutas de Órdenes ===
        Route::get('/servicios', [OrdenController::class, 'servicios']);
        Route::post('/servicios', [OrdenController::class, 'guardarServicio']);
        Route::get('/ordenes/reporte/pdf', [OrdenController::class, 'reportePdf']);
        Route::get('/ordenes/{id}/verificacion', [OrdenController::class, 'obtenerListaVerificacion']);
        Route::post('/ordenes/guardar-verificacion', [OrdenController::class, 'guardarListaVerificacion']);
        Route::get('/ordenes', [OrdenController::class, 'index']);
        Route::post('/ordenes', [OrdenController::class, 'store']);
        Route::post('/ordenes/{id}/servicios', [OrdenController::class, 'guardarServicioOrden']);
        Route::put('/ordenes/{id}', [OrdenController::class, 'update']);
        Route::delete('/ordenes/{id}', [OrdenController::class, 'destroy']);
        Route::put('/ordenes/{id}/cambiar-estado', [OrdenController::class, 'cambiarEstado']);
    });
});
