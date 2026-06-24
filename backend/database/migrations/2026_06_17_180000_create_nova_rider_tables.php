<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TRoles
        Schema::create('TRoles', function (Blueprint $table) {
            $table->integer('id_rol', true);
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 2. TEmpleados (persona fields merged in)
        Schema::create('TEmpleados', function (Blueprint $table) {
            $table->integer('id_empleado', true);
            $table->string('ci')->nullable();
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->decimal('sueldo_base', 10, 0)->default(0);
            $table->string('cargo')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 3. TClientes (persona fields merged in)
        Schema::create('TClientes', function (Blueprint $table) {
            $table->integer('id_cliente', true);
            $table->string('ci')->nullable();
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono')->nullable();
            $table->string('nit')->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 4. TUsuarios (without id_rol)
        Schema::create('TUsuarios', function (Blueprint $table) {
            $table->integer('id_usuario', true);
            $table->integer('id_empleado')->unique()->nullable();
            $table->string('username');
            $table->string('password_hash');
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
        });

        // 5. TUsuarioRol (NEW pivot table)
        Schema::create('TUsuarioRol', function (Blueprint $table) {
            $table->integer('id_usuario');
            $table->integer('id_rol');
            $table->primary(['id_usuario', 'id_rol']);
            $table->foreign('id_usuario')->references('id_usuario')->on('TUsuarios');
            $table->foreign('id_rol')->references('id_rol')->on('TRoles');
        });

        // 6. TMotocicletas (with ficha tecnica fields, without kilometraje)
        Schema::create('TMotocicletas', function (Blueprint $table) {
            $table->integer('id_motocicleta', true);
            $table->integer('id_cliente')->nullable();
            $table->string('placa')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->integer('anio')->nullable();
            $table->string('nro_chasis')->nullable();
            $table->string('nro_motor')->nullable();
            $table->string('color')->nullable();
            $table->string('cilindrada')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_cliente')->references('id_cliente')->on('TClientes');
        });

        // 7. TEstantes
        Schema::create('TEstantes', function (Blueprint $table) {
            $table->integer('id_estante', true);
            $table->integer('numero_estante')->nullable();
            $table->string('pasillo')->nullable();
            $table->string('descripcion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 8. TSecciones
        Schema::create('TSecciones', function (Blueprint $table) {
            $table->integer('id_seccion', true);
            $table->integer('id_estante')->nullable();
            $table->string('codigo_seccion')->nullable();
            $table->string('descripcion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_estante')->references('id_estante')->on('TEstantes');
        });

        // 9. TUbicaciones
        Schema::create('TUbicaciones', function (Blueprint $table) {
            $table->integer('id_ubicacion', true);
            $table->integer('id_seccion')->nullable();
            $table->string('nivel')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_seccion')->references('id_seccion')->on('TSecciones');
        });

        // 10. TProductos (without codigo_barras)
        Schema::create('TProductos', function (Blueprint $table) {
            $table->integer('id_producto', true);
            $table->integer('id_ubicacion')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 10, 0)->nullable();
            $table->integer('stock_fisico')->nullable();
            $table->integer('stock_disponible')->nullable();
            $table->integer('stock_minimo')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_ubicacion')->references('id_ubicacion')->on('TUbicaciones');
        });

        // 11. TModelosCompatibles
        Schema::create('TModelosCompatibles', function (Blueprint $table) {
            $table->integer('id_modelo', true);
            $table->string('marca_moto')->nullable();
            $table->string('modelo_moto')->nullable();
            $table->integer('anio_inicio')->nullable();
            $table->integer('anio_fin')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 12. TProductosModelosCompatibles
        Schema::create('TProductosModelosCompatibles', function (Blueprint $table) {
            $table->integer('id_producto');
            $table->integer('id_modelo');
            $table->primary(['id_producto', 'id_modelo']);
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_producto')->references('id_producto')->on('TProductos');
            $table->foreign('id_modelo')->references('id_modelo')->on('TModelosCompatibles');
        });

        // 13. TProveedores
        Schema::create('TProveedores', function (Blueprint $table) {
            $table->integer('id_proveedor', true);
            $table->string('nombre')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 14. TCompras
        Schema::create('TCompras', function (Blueprint $table) {
            $table->integer('id_compra', true);
            $table->integer('id_proveedor')->nullable();
            $table->date('fecha')->nullable();
            $table->string('nro_factura_proveedor')->nullable();
            $table->decimal('total_compra', 10, 0)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_proveedor')->references('id_proveedor')->on('TProveedores');
        });

        // 15. TDetallesCompra
        Schema::create('TDetallesCompra', function (Blueprint $table) {
            $table->integer('id_detalle_compra', true);
            $table->integer('id_compra')->nullable();
            $table->integer('id_producto')->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('precio_unitario_compra', 10, 0)->nullable();
            $table->decimal('subtotal', 10, 0)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_compra')->references('id_compra')->on('TCompras');
            $table->foreign('id_producto')->references('id_producto')->on('TProductos');
        });

        // 16. TCajas
        Schema::create('TCajas', function (Blueprint $table) {
            $table->integer('id_caja', true);
            $table->integer('id_empleado')->nullable();
            $table->dateTime('fecha_apertura')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_apertura', 10, 0)->nullable();
            $table->decimal('monto_cierre_fisico', 10, 0)->nullable();
            $table->decimal('monto_sistema', 10, 0)->nullable();
            $table->text('observacion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
        });

        // 17. TVentas
        Schema::create('TVentas', function (Blueprint $table) {
            $table->integer('id_venta', true);
            $table->integer('id_cliente')->nullable();
            $table->integer('id_empleado')->nullable();
            $table->integer('id_caja')->nullable();
            $table->string('nro_factura')->nullable();
            $table->dateTime('fecha_hora')->nullable();
            $table->decimal('subtotal', 10, 0)->nullable();
            $table->decimal('descuento', 10, 0)->nullable();
            $table->decimal('total', 10, 0)->nullable();
            $table->string('metodo_pago')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_cliente')->references('id_cliente')->on('TClientes');
            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
            $table->foreign('id_caja')->references('id_caja')->on('TCajas');
        });

        // 18. TDetallesVenta
        Schema::create('TDetallesVenta', function (Blueprint $table) {
            $table->integer('id_detalle_venta', true);
            $table->integer('id_venta')->nullable();
            $table->integer('id_producto')->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('precio_unitario_historico', 10, 0)->nullable();
            $table->decimal('subtotal', 10, 0)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_venta')->references('id_venta')->on('TVentas');
            $table->foreign('id_producto')->references('id_producto')->on('TProductos');
        });

        // 19. TServicios (NEW)
        Schema::create('TServicios', function (Blueprint $table) {
            $table->integer('id_servicio', true);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio_estimado', 10, 0)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // 20. TOrdenesTrabajo (without codigo_qr, with condicion_entrada)
        Schema::create('TOrdenesTrabajo', function (Blueprint $table) {
            $table->integer('id_orden', true);
            $table->integer('id_motocicleta')->nullable();
            $table->integer('id_empleado')->nullable();
            $table->string('nro_orden')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->string('estado')->nullable();
            $table->text('condicion_entrada')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_motocicleta')->references('id_motocicleta')->on('TMotocicletas');
            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
        });

        // 21. THistorialesEstado
        Schema::create('THistorialesEstado', function (Blueprint $table) {
            $table->integer('id_historial', true);
            $table->integer('id_orden')->nullable();
            $table->dateTime('fecha_hora_cambio')->nullable();
            $table->string('estado_asignado')->nullable();
            $table->text('observacion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_orden')->references('id_orden')->on('TOrdenesTrabajo');
        });

        // 22. TDetallesOrdenTrabajo (without id_producto, with id_servicio)
        Schema::create('TDetallesOrdenTrabajo', function (Blueprint $table) {
            $table->integer('id_detalle_ot', true);
            $table->integer('id_orden')->nullable();
            $table->integer('id_servicio')->nullable();
            $table->string('descripcion')->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('precio_labor', 10, 0)->nullable();
            $table->decimal('subtotal', 10, 0)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_orden')->references('id_orden')->on('TOrdenesTrabajo');
            $table->foreign('id_servicio')->references('id_servicio')->on('TServicios');
        });

        // 23. TListasVerificacion (with fecha and kilometraje)
        Schema::create('TListasVerificacion', function (Blueprint $table) {
            $table->integer('id_lista', true);
            $table->integer('id_orden')->unique()->nullable();
            $table->boolean('frenos_revisados')->nullable();
            $table->boolean('luces_revisadas')->nullable();
            $table->boolean('piezas_ajustadas')->nullable();
            $table->boolean('prueba_ruta')->nullable();
            $table->date('fecha_validacion')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->integer('kilometraje')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_orden')->references('id_orden')->on('TOrdenesTrabajo');
        });

        // 24. TReservas
        Schema::create('TReservas', function (Blueprint $table) {
            $table->integer('id_reserva', true);
            $table->integer('id_cliente')->nullable();
            $table->date('fecha_solicitud')->nullable();
            $table->date('fecha_expiracion')->nullable();
            $table->string('estado')->nullable();
            $table->string('departamento_origen')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_cliente')->references('id_cliente')->on('TClientes');
        });

        // 25. TDetallesReservas
        Schema::create('TDetallesReservas', function (Blueprint $table) {
            $table->integer('id_detalle_reserva', true);
            $table->integer('id_reserva')->nullable();
            $table->integer('id_producto')->nullable();
            $table->integer('cantidad_reservada')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_reserva')->references('id_reserva')->on('TReservas');
            $table->foreign('id_producto')->references('id_producto')->on('TProductos');
        });

        // 26. TEnvios
        Schema::create('TEnvios', function (Blueprint $table) {
            $table->integer('id_envio', true);
            $table->integer('id_reserva')->unique()->nullable();
            $table->string('empresa_transporte')->nullable();
            $table->string('nro_guia')->nullable();
            $table->date('fecha_despacho')->nullable();
            $table->string('estado_envio')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_reserva')->references('id_reserva')->on('TReservas');
        });

        // 27. TTurnos (without asistio)
        Schema::create('TTurnos', function (Blueprint $table) {
            $table->integer('id_turno', true);
            $table->integer('id_empleado')->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->text('observacion')->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
        });

        // 28. TPlanillas
        Schema::create('TPlanillas', function (Blueprint $table) {
            $table->integer('id_planilla', true);
            $table->integer('id_empleado')->nullable();
            $table->integer('mes')->nullable();
            $table->integer('anio')->nullable();
            $table->decimal('sueldo_bruto', 10, 0)->nullable();
            $table->decimal('bonos', 10, 0)->nullable();
            $table->decimal('deducciones', 10, 0)->nullable();
            $table->decimal('sueldo_neto', 10, 0)->nullable();
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
        });

        // 29. TReservasVentas
        Schema::create('TReservasVentas', function (Blueprint $table) {
            $table->integer('id_reserva');
            $table->integer('id_venta');
            $table->primary(['id_reserva', 'id_venta']);
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->dateTime('fechahoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_reserva')->references('id_reserva')->on('TReservas');
            $table->foreign('id_venta')->references('id_venta')->on('TVentas');
        });

        // 30. TAuditoriaGeneral
        Schema::create('TAuditoriaGeneral', function (Blueprint $table) {
            $table->integer('idAuditoria', true);
            $table->string('TablaNombre', 100);
            $table->integer('RegistroId');
            $table->string('Accion', 1)->comment('I=Insert, U=Update, D=Delete');
            $table->string('Campo', 100)->nullable();
            $table->text('ValorAnterior')->nullable();
            $table->text('ValorNuevo')->nullable();
            $table->integer('usuarioA');
            $table->dateTime('fechaHoraA')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('direccionIP', 45)->nullable();
            $table->text('Detalles')->nullable();
            $table->foreign('usuarioA')->references('id_usuario')->on('TUsuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TAuditoriaGeneral');
        Schema::dropIfExists('TReservasVentas');
        Schema::dropIfExists('TPlanillas');
        Schema::dropIfExists('TTurnos');
        Schema::dropIfExists('TEnvios');
        Schema::dropIfExists('TDetallesReservas');
        Schema::dropIfExists('TReservas');
        Schema::dropIfExists('TListasVerificacion');
        Schema::dropIfExists('TDetallesOrdenTrabajo');
        Schema::dropIfExists('THistorialesEstado');
        Schema::dropIfExists('TOrdenesTrabajo');
        Schema::dropIfExists('TServicios');
        Schema::dropIfExists('TDetallesVenta');
        Schema::dropIfExists('TVentas');
        Schema::dropIfExists('TCajas');
        Schema::dropIfExists('TDetallesCompra');
        Schema::dropIfExists('TCompras');
        Schema::dropIfExists('TProveedores');
        Schema::dropIfExists('TProductosModelosCompatibles');
        Schema::dropIfExists('TModelosCompatibles');
        Schema::dropIfExists('TProductos');
        Schema::dropIfExists('TUbicaciones');
        Schema::dropIfExists('TSecciones');
        Schema::dropIfExists('TEstantes');
        Schema::dropIfExists('TMotocicletas');
        Schema::dropIfExists('TUsuarioRol');
        Schema::dropIfExists('TUsuarios');
        Schema::dropIfExists('TClientes');
        Schema::dropIfExists('TEmpleados');
        Schema::dropIfExists('TRoles');
    }
};
