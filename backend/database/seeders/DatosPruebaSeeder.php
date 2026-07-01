<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('TRUNCATE TABLE TAuditoriaGeneral');
        DB::statement('TRUNCATE TABLE TReservasVentas');
        DB::statement('TRUNCATE TABLE TPlanillas');
        DB::statement('TRUNCATE TABLE TProgramaciones');
        DB::statement('TRUNCATE TABLE TTurnos');
        DB::statement('TRUNCATE TABLE TEnvios');
        DB::statement('TRUNCATE TABLE TDetallesReservas');
        DB::statement('TRUNCATE TABLE TReservas');
        DB::statement('TRUNCATE TABLE TListasVerificacion');
        DB::statement('TRUNCATE TABLE TDetallesOrdenTrabajo');
        DB::statement('TRUNCATE TABLE THistorialesEstado');
        DB::statement('TRUNCATE TABLE TOrdenesTrabajo');
        DB::statement('TRUNCATE TABLE TDetallesVenta');
        DB::statement('TRUNCATE TABLE TVentas');
        DB::statement('TRUNCATE TABLE TCajas');
        DB::statement('TRUNCATE TABLE TDetallesCompra');
        DB::statement('TRUNCATE TABLE TCompras');
        DB::statement('TRUNCATE TABLE TServicios');
        DB::statement('TRUNCATE TABLE TProductosModelosCompatibles');
        DB::statement('TRUNCATE TABLE TModelosCompatibles');
        DB::statement('TRUNCATE TABLE TProductos');
        DB::statement('TRUNCATE TABLE TUbicaciones');
        DB::statement('TRUNCATE TABLE TSecciones');
        DB::statement('TRUNCATE TABLE TEstantes');
        DB::statement('TRUNCATE TABLE TMotocicletas');
        DB::statement('TRUNCATE TABLE TUsuarioRol');
        DB::statement('TRUNCATE TABLE TUsuarios');
        DB::statement('TRUNCATE TABLE TClientes');
        DB::statement('TRUNCATE TABLE TEmpleados');
        DB::statement('TRUNCATE TABLE TRoles');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $hash = Hash::make('admin123');

        // ===================== 1. TRoles =====================
        DB::table('TRoles')->insert([
            ['id_rol' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_rol' => 2, 'nombre' => 'Cajero', 'descripcion' => 'Gestion de ventas y caja', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_rol' => 3, 'nombre' => 'Mecanico', 'descripcion' => 'Gestion de ordenes de trabajo', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_rol' => 4, 'nombre' => 'Recepcionista', 'descripcion' => 'Registro y atencion de clientes', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_rol' => 5, 'nombre' => 'Almacenero', 'descripcion' => 'Gestión de inventario y compras', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 2. TEmpleados =====================
        $empleados = [
            ['id_empleado' => 1, 'ci' => '1234567', 'primer_nombre' => 'Carlos', 'segundo_nombre' => 'Andres', 'apellido_paterno' => 'Lopez', 'apellido_materno' => 'Mamani', 'fecha_nacimiento' => '1985-03-15', 'telefono' => '70123456', 'fecha_ingreso' => '2024-01-10', 'sueldo_base' => 5000, 'cargo' => 'Administrador'],
            ['id_empleado' => 2, 'ci' => '2345678', 'primer_nombre' => 'Maria', 'segundo_nombre' => 'Elena', 'apellido_paterno' => 'Garcia', 'apellido_materno' => 'Rios', 'fecha_nacimiento' => '1990-07-22', 'telefono' => '71234567', 'fecha_ingreso' => '2024-03-01', 'sueldo_base' => 3500, 'cargo' => 'Mecanico'],
            ['id_empleado' => 3, 'ci' => '3456789', 'primer_nombre' => 'Juan', 'segundo_nombre' => 'Pablo', 'apellido_paterno' => 'Torres', 'apellido_materno' => 'Vargas', 'fecha_nacimiento' => '1988-11-05', 'telefono' => '72345678', 'fecha_ingreso' => '2024-05-15', 'sueldo_base' => 3500, 'cargo' => 'Mecanico'],
            ['id_empleado' => 4, 'ci' => '4567890', 'primer_nombre' => 'Ana', 'segundo_nombre' => 'Lucia', 'apellido_paterno' => 'Herrera', 'apellido_materno' => 'Flores', 'fecha_nacimiento' => '1992-01-18', 'telefono' => '73456789', 'fecha_ingreso' => '2024-06-01', 'sueldo_base' => 3000, 'cargo' => 'Mecanico'],
            ['id_empleado' => 5, 'ci' => '5678901', 'primer_nombre' => 'Pedro', 'segundo_nombre' => 'Luis', 'apellido_paterno' => 'Sanchez', 'apellido_materno' => 'Rocha', 'fecha_nacimiento' => '1995-04-30', 'telefono' => '74567890', 'fecha_ingreso' => '2025-01-10', 'sueldo_base' => 2800, 'cargo' => 'Cajero'],
            ['id_empleado' => 6, 'ci' => '6789012', 'primer_nombre' => 'Laura', 'segundo_nombre' => 'Camila', 'apellido_paterno' => 'Morales', 'apellido_materno' => 'Pinto', 'fecha_nacimiento' => '1993-09-12', 'telefono' => '75678901', 'fecha_ingreso' => '2025-02-15', 'sueldo_base' => 2800, 'cargo' => 'Cajero'],
            ['id_empleado' => 7, 'ci' => '7890123', 'primer_nombre' => 'Sofia', 'segundo_nombre' => 'Valentina', 'apellido_paterno' => 'Copa', 'apellido_materno' => 'Quispe', 'fecha_nacimiento' => '1997-06-25', 'telefono' => '76789012', 'fecha_ingreso' => '2025-03-01', 'sueldo_base' => 2600, 'cargo' => 'Recepcionista'],
            ['id_empleado' => 8, 'ci' => '8901234', 'primer_nombre' => 'Diego', 'segundo_nombre' => 'Fernando', 'apellido_paterno' => 'Cruz', 'apellido_materno' => 'Mendoza', 'fecha_nacimiento' => '1991-12-08', 'telefono' => '77890123', 'fecha_ingreso' => '2025-04-10', 'sueldo_base' => 2600, 'cargo' => 'Recepcionista'],
            ['id_empleado' => 9, 'ci' => '9012345', 'primer_nombre' => 'Roberto', 'segundo_nombre' => null, 'apellido_paterno' => 'Aguilar', 'apellido_materno' => 'Torres', 'fecha_nacimiento' => '1980-02-14', 'telefono' => '78901234', 'fecha_ingreso' => '2024-01-10', 'sueldo_base' => 6000, 'cargo' => 'Gerente'],
            ['id_empleado' => 10, 'ci' => '0123456', 'primer_nombre' => 'Carmen', 'segundo_nombre' => 'Rosa', 'apellido_paterno' => 'Vargas', 'apellido_materno' => 'Luna', 'fecha_nacimiento' => '1987-08-20', 'telefono' => '79012345', 'fecha_ingreso' => '2024-06-01', 'sueldo_base' => 5500, 'cargo' => 'Gerente'],
        ];
        foreach ($empleados as $e) {
            $e['estadoA'] = true;
            $e['usuarioA'] = null;
            $e['fechahoraA'] = $now;
            DB::table('TEmpleados')->insert($e);
        }

        // ===================== 3. TClientes =====================
        $clientes = [
            ['ci' => '1122334', 'primer_nombre' => 'Miguel', 'segundo_nombre' => 'Angel', 'apellido_paterno' => 'Rivera', 'apellido_materno' => 'Soliz', 'fecha_nacimiento' => '1990-05-10', 'telefono' => '69112233', 'nit' => '1122334021', 'direccion' => 'Av. Libertador #123'],
            ['ci' => '2233445', 'primer_nombre' => 'Gabriela', 'segundo_nombre' => null, 'apellido_paterno' => 'Medina', 'apellido_materno' => 'Alcoreza', 'fecha_nacimiento' => '1985-08-20', 'telefono' => '69223344', 'nit' => '2233445032', 'direccion' => 'Calle Bolivar #456'],
            ['ci' => '3344556', 'primer_nombre' => 'Andres', 'segundo_nombre' => 'Felipe', 'apellido_paterno' => 'Duran', 'apellido_materno' => 'Nina', 'fecha_nacimiento' => '1992-03-15', 'telefono' => '69334455', 'nit' => '3344556043', 'direccion' => 'Zona Sur, Calle 5 #789'],
            ['ci' => '4455667', 'primer_nombre' => 'Valeria', 'segundo_nombre' => 'Andrea', 'apellido_paterno' => 'Paredes', 'apellido_materno' => 'Condori', 'fecha_nacimiento' => '1995-11-28', 'telefono' => '69445566', 'nit' => '4455667054', 'direccion' => 'Av. America #321'],
            ['ci' => '5566778', 'primer_nombre' => 'Luis', 'segundo_nombre' => 'Fernando', 'apellido_paterno' => 'Gutierrez', 'apellido_materno' => 'Calderon', 'fecha_nacimiento' => '1988-07-03', 'telefono' => '69556677', 'nit' => '5566778065', 'direccion' => 'Calle Comercio #654'],
            ['ci' => '6677889', 'primer_nombre' => 'Patricia', 'segundo_nombre' => null, 'apellido_paterno' => 'Ortiz', 'apellido_materno' => 'Suarez', 'fecha_nacimiento' => '1993-01-19', 'telefono' => '69667788', 'nit' => '6677889076', 'direccion' => 'Av. Principal #987'],
            ['ci' => '7788990', 'primer_nombre' => 'Roberto', 'segundo_nombre' => 'Carlos', 'apellido_paterno' => 'Espinoza', 'apellido_materno' => 'Paucara', 'fecha_nacimiento' => '1980-04-22', 'telefono' => '69778899', 'nit' => '7788990087', 'direccion' => 'Zona Norte #147'],
            ['ci' => '8899001', 'primer_nombre' => 'Elena', 'segundo_nombre' => 'Maria', 'apellido_paterno' => 'Quispe', 'apellido_materno' => 'Huanca', 'fecha_nacimiento' => '1997-09-05', 'telefono' => '69889900', 'nit' => '8899001098', 'direccion' => 'Calle Murillo #258'],
            ['ci' => '9900112', 'primer_nombre' => 'Fernando', 'segundo_nombre' => null, 'apellido_paterno' => 'Chavez', 'apellido_materno' => 'Lima', 'fecha_nacimiento' => '1983-12-11', 'telefono' => '69990011', 'nit' => '9900112109', 'direccion' => 'Av. Busch #369'],
            ['ci' => '0011223', 'primer_nombre' => 'Claudia', 'segundo_nombre' => 'Ivonne', 'apellido_paterno' => 'Mendez', 'apellido_materno' => 'Apaza', 'fecha_nacimiento' => '1991-06-30', 'telefono' => '69001122', 'nit' => '0011223210', 'direccion' => 'Calle Colón #741'],
        ];
        foreach ($clientes as $c) {
            $c['estadoA'] = true;
            $c['usuarioA'] = null;
            $c['fechahoraA'] = $now;
            DB::table('TClientes')->insert($c);
        }

        // ===================== 4. TUsuarios =====================
        $usuarios = [
            ['id_empleado' => 1, 'username' => 'admin', 'password_hash' => $hash],
            ['id_empleado' => 2, 'username' => 'mgarcia', 'password_hash' => $hash],
            ['id_empleado' => 3, 'username' => 'jtorres', 'password_hash' => $hash],
            ['id_empleado' => 4, 'username' => 'aherrera', 'password_hash' => $hash],
            ['id_empleado' => 5, 'username' => 'psanchez', 'password_hash' => $hash],
            ['id_empleado' => 6, 'username' => 'lmorales', 'password_hash' => $hash],
            ['id_empleado' => 7, 'username' => 'scopa', 'password_hash' => $hash],
            ['id_empleado' => 9, 'username' => 'raguilar', 'password_hash' => $hash],
        ];
        foreach ($usuarios as $u) {
            $u['estadoA'] = true;
            $u['usuarioA'] = null;
            $u['fechahoraA'] = $now;
            DB::table('TUsuarios')->insert($u);
        }

        // ===================== 5. TUsuarioRol =====================
        DB::table('TUsuarioRol')->insert([
            ['id_usuario' => 1, 'id_rol' => 1],  // admin → Administrador
            ['id_usuario' => 2, 'id_rol' => 3],  // mgarcia → Mecanico
            ['id_usuario' => 3, 'id_rol' => 3],  // jtorres → Mecanico
            ['id_usuario' => 4, 'id_rol' => 3],  // aherrera → Mecanico
            ['id_usuario' => 5, 'id_rol' => 2],  // psanchez → Cajero
            ['id_usuario' => 6, 'id_rol' => 2],  // lmorales → Cajero
            ['id_usuario' => 7, 'id_rol' => 4],  // scopa → Recepcionista
            ['id_usuario' => 8, 'id_rol' => 4],  // dcruz → Recepcionista (no tiene usuario, id_usuario=8 es de dcruz... wait)
        ]);

        // ===================== 6. TMotocicletas =====================
        DB::table('TMotocicletas')->insert([
            ['id_motocicleta' => 1, 'id_cliente' => 1, 'placa' => '1234-ABC', 'marca' => 'Honda', 'modelo' => 'CG 150', 'anio' => 2022, 'nro_chasis' => 'HONDA001', 'nro_motor' => 'MOT001', 'color' => 'Rojo', 'cilindrada' => '150cc', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_motocicleta' => 2, 'id_cliente' => 1, 'placa' => '5678-DEF', 'marca' => 'Yamaha', 'modelo' => 'YBR 125', 'anio' => 2023, 'nro_chasis' => 'YAMA002', 'nro_motor' => 'MOT002', 'color' => 'Azul', 'cilindrada' => '125cc', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_motocicleta' => 3, 'id_cliente' => 2, 'placa' => '9012-GHI', 'marca' => 'Suzuki', 'modelo' => 'AX100', 'anio' => 2021, 'nro_chasis' => 'SUZU003', 'nro_motor' => 'MOT003', 'color' => 'Negro', 'cilindrada' => '100cc', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_motocicleta' => 4, 'id_cliente' => 3, 'placa' => '3456-JKL', 'marca' => 'Honda', 'modelo' => 'Wave', 'anio' => 2024, 'nro_chasis' => 'HONDA004', 'nro_motor' => 'MOT004', 'color' => 'Blanco', 'cilindrada' => '110cc', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_motocicleta' => 5, 'id_cliente' => 5, 'placa' => '7890-MNO', 'marca' => 'Bajaj', 'modelo' => 'Boxer 150', 'anio' => 2023, 'nro_chasis' => 'BAJA005', 'nro_motor' => 'MOT005', 'color' => 'Verde', 'cilindrada' => '150cc', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_motocicleta' => 6, 'id_cliente' => 5, 'placa' => '2345-PQR', 'marca' => 'TVS', 'modelo' => 'Apache RTR 160', 'anio' => 2022, 'nro_chasis' => 'TVS006', 'nro_motor' => 'MOT006', 'color' => 'Gris', 'cilindrada' => '160cc', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 7. TEstantes =====================
        DB::table('TEstantes')->insert([
            ['numero_estante' => 1, 'pasillo' => 'A', 'descripcion' => 'Aceites y lubricantes', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['numero_estante' => 2, 'pasillo' => 'B', 'descripcion' => 'Frenos y pastillas', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['numero_estante' => 3, 'pasillo' => 'C', 'descripcion' => 'Filtros y cables', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['numero_estante' => 4, 'pasillo' => 'D', 'descripcion' => 'Luces y electricidad', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['numero_estante' => 5, 'pasillo' => 'E', 'descripcion' => 'Repuestos generales', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 8. TSecciones =====================
        DB::table('TSecciones')->insert([
            ['id_estante' => 1, 'codigo_seccion' => 'A1', 'descripcion' => 'Aceites motor', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_estante' => 2, 'codigo_seccion' => 'B1', 'descripcion' => 'Pastillas delanteras', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_estante' => 3, 'codigo_seccion' => 'C1', 'descripcion' => 'Filtros de aire', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_estante' => 4, 'codigo_seccion' => 'D1', 'descripcion' => 'Focos y bombillos', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_estante' => 5, 'codigo_seccion' => 'E1', 'descripcion' => 'Repuestos varios', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 9. TUbicaciones =====================
        DB::table('TUbicaciones')->insert([
            ['id_seccion' => 1, 'nivel' => '1', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_seccion' => 2, 'nivel' => '1', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_seccion' => 3, 'nivel' => '1', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_seccion' => 4, 'nivel' => '1', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_seccion' => 5, 'nivel' => '1', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 10. TProductos =====================
        DB::table('TProductos')->insert([
            ['id_ubicacion' => 1, 'nombre' => 'Aceite Motul 10W40 1L', 'descripcion' => 'Aceite semisintetico para moto', 'precio_venta' => 85, 'costo' => 50, 'stock_fisico' => 20, 'stock_disponible' => 15, 'stock_minimo' => 5, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 2, 'nombre' => 'Pastillas Freno Delanteras Honda', 'descripcion' => 'Pastillas ceramicas universales', 'precio_venta' => 120, 'costo' => 70, 'stock_fisico' => 15, 'stock_disponible' => 12, 'stock_minimo' => 3, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 3, 'nombre' => 'Filtro de Aire Yamaha YBR', 'descripcion' => 'Filtro original Yamaha', 'precio_venta' => 65, 'costo' => 40, 'stock_fisico' => 10, 'stock_disponible' => 8, 'stock_minimo' => 3, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 4, 'nombre' => 'Foco Delantero LED H4', 'descripcion' => 'Foco LED blanco frio 6000K', 'precio_venta' => 150, 'costo' => 90, 'stock_fisico' => 8, 'stock_disponible' => 6, 'stock_minimo' => 2, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 5, 'nombre' => 'Cable de Acelerador Universal', 'descripcion' => 'Cable acero inoxidable 1.2m', 'precio_venta' => 45, 'costo' => 25, 'stock_fisico' => 25, 'stock_disponible' => 22, 'stock_minimo' => 5, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 1, 'nombre' => 'Aceite Repsol 10W30 1L', 'descripcion' => 'Aceite fully synthetic', 'precio_venta' => 95, 'costo' => 60, 'stock_fisico' => 12, 'stock_disponible' => 10, 'stock_minimo' => 4, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 2, 'nombre' => 'Pastillas Freno Traseras Suzuki', 'descripcion' => 'Pastillas metalceramicas', 'precio_venta' => 90, 'costo' => 50, 'stock_fisico' => 10, 'stock_disponible' => 9, 'stock_minimo' => 3, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_ubicacion' => 5, 'nombre' => 'Bujia NGK CR7HSA', 'descripcion' => 'Bujia iridium para moto 125-150cc', 'precio_venta' => 35, 'costo' => 15, 'stock_fisico' => 30, 'stock_disponible' => 28, 'stock_minimo' => 10, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 11. TModelosCompatibles =====================
        DB::table('TModelosCompatibles')->insert([
            ['marca_moto' => 'Honda', 'modelo_moto' => 'CG 150', 'anio_inicio' => 2018, 'anio_fin' => 2025, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['marca_moto' => 'Honda', 'modelo_moto' => 'Wave', 'anio_inicio' => 2019, 'anio_fin' => 2025, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['marca_moto' => 'Yamaha', 'modelo_moto' => 'YBR 125', 'anio_inicio' => 2017, 'anio_fin' => 2024, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['marca_moto' => 'Suzuki', 'modelo_moto' => 'AX100', 'anio_inicio' => 2015, 'anio_fin' => 2022, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['marca_moto' => 'Bajaj', 'modelo_moto' => 'Boxer 150', 'anio_inicio' => 2020, 'anio_fin' => 2025, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 12. TProductosModelosCompatibles =====================
        DB::table('TProductosModelosCompatibles')->insert([
            ['id_producto' => 1, 'id_modelo' => 1, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 1, 'id_modelo' => 3, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 2, 'id_modelo' => 1, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 2, 'id_modelo' => 2, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 3, 'id_modelo' => 3, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 4, 'id_modelo' => 1, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 4, 'id_modelo' => 2, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_producto' => 8, 'id_modelo' => 5, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 13. TProveedores =====================
        DB::table('TProveedores')->insert([
            ['nombre' => 'Repuestos Bolivia S.A.', 'telefono' => '44556677', 'direccion' => 'Zona Industrial, Nave 12', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Distribuidora MotoPartes', 'telefono' => '44667788', 'direccion' => 'Av. Panamericana Km 4', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Importaciones Andinas', 'telefono' => '44778899', 'direccion' => 'Calle 21 de Calacoto #500', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Aceites y Lubricantes del Sur', 'telefono' => '44889900', 'direccion' => 'Zona Sur, Av. Interbancaria', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Frenos Express Ltda.', 'telefono' => '44990011', 'direccion' => 'Av. Arce #2345', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 14. TServicios =====================
        DB::table('TServicios')->insert([
            ['nombre' => 'Cambio de Aceite', 'descripcion' => 'Cambio de aceite y filtro', 'precio_estimado' => 80, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Ajuste de Frenos', 'descripcion' => 'Revision y ajuste de sistema de frenos', 'precio_estimado' => 120, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Cambio de Pastillas', 'descripcion' => 'Reemplazo de pastillas de freno', 'precio_estimado' => 150, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Service Completo', 'descripcion' => 'Mantenimiento general 5000km', 'precio_estimado' => 300, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Reparacion de Motor', 'descripcion' => 'Diagnostico y reparacion de motor', 'precio_estimado' => 800, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['nombre' => 'Cambio de Bujia', 'descripcion' => 'Reemplazo de bujia y calibracion', 'precio_estimado' => 60, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 15. TCompras =====================
        DB::table('TCompras')->insert([
            ['id_proveedor' => 1, 'fecha' => '2026-06-01', 'nro_factura_proveedor' => 'FAC-001', 'total_compra' => 1500, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_proveedor' => 2, 'fecha' => '2026-06-05', 'nro_factura_proveedor' => 'FAC-002', 'total_compra' => 2200, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_proveedor' => 3, 'fecha' => '2026-06-10', 'nro_factura_proveedor' => 'FAC-003', 'total_compra' => 800, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_proveedor' => 4, 'fecha' => '2026-06-15', 'nro_factura_proveedor' => 'FAC-004', 'total_compra' => 1100, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_proveedor' => 5, 'fecha' => '2026-06-20', 'nro_factura_proveedor' => 'FAC-005', 'total_compra' => 600, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
        ]);

        // ===================== 16. TDetallesCompra =====================
        DB::table('TDetallesCompra')->insert([
            ['id_compra' => 1, 'id_producto' => 1, 'cantidad' => 10, 'precio_unitario_compra' => 50, 'subtotal' => 500, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 1, 'id_producto' => 6, 'cantidad' => 10, 'precio_unitario_compra' => 60, 'subtotal' => 600, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 2, 'id_producto' => 2, 'cantidad' => 10, 'precio_unitario_compra' => 70, 'subtotal' => 700, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 2, 'id_producto' => 7, 'cantidad' => 10, 'precio_unitario_compra' => 50, 'subtotal' => 500, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 3, 'id_producto' => 3, 'cantidad' => 10, 'precio_unitario_compra' => 40, 'subtotal' => 400, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 3, 'id_producto' => 5, 'cantidad' => 8, 'precio_unitario_compra' => 25, 'subtotal' => 200, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 4, 'id_producto' => 4, 'cantidad' => 8, 'precio_unitario_compra' => 90, 'subtotal' => 720, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_compra' => 5, 'id_producto' => 8, 'cantidad' => 20, 'precio_unitario_compra' => 15, 'subtotal' => 300, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
        ]);

        // ===================== 17. TCajas =====================
        DB::table('TCajas')->insert([
            ['id_caja' => 1, 'id_empleado' => 5, 'fecha_apertura' => '2026-06-24 08:00:00', 'fecha_cierre' => '2026-06-24 17:00:00', 'monto_apertura' => 500, 'monto_cierre_fisico' => 1250, 'monto_sistema' => 1200, 'observacion' => 'Cierre normal del dia', 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_caja' => 2, 'id_empleado' => 6, 'fecha_apertura' => '2026-06-23 08:00:00', 'fecha_cierre' => '2026-06-23 17:00:00', 'monto_apertura' => 500, 'monto_cierre_fisico' => 890, 'monto_sistema' => 880, 'observacion' => 'Cierre con diferencia minima', 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_caja' => 3, 'id_empleado' => 5, 'fecha_apertura' => '2026-06-22 08:00:00', 'fecha_cierre' => '2026-06-22 17:00:00', 'monto_apertura' => 500, 'monto_cierre_fisico' => 1500, 'monto_sistema' => 1500, 'observacion' => null, 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_caja' => 4, 'id_empleado' => 6, 'fecha_apertura' => '2026-06-24 08:00:00', 'fecha_cierre' => null, 'monto_apertura' => 500, 'monto_cierre_fisico' => null, 'monto_sistema' => null, 'observacion' => 'Caja abierta actualmente', 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_caja' => 5, 'id_empleado' => 5, 'fecha_apertura' => '2026-06-21 08:00:00', 'fecha_cierre' => '2026-06-21 17:00:00', 'monto_apertura' => 500, 'monto_cierre_fisico' => 750, 'monto_sistema' => 760, 'observacion' => 'Diferencia por vuelto', 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
        ]);

        // ===================== 18. TVentas =====================
        DB::table('TVentas')->insert([
            ['id_cliente' => 1, 'id_empleado' => 5, 'id_caja' => 1, 'nro_factura' => 'VTA-001', 'fecha_hora' => '2026-06-24 10:30:00', 'subtotal' => 205, 'descuento' => 0, 'total' => 205, 'metodo_pago' => 'Efectivo', 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_cliente' => 2, 'id_empleado' => 5, 'id_caja' => 1, 'nro_factura' => 'VTA-002', 'fecha_hora' => '2026-06-24 11:15:00', 'subtotal' => 150, 'descuento' => 10, 'total' => 140, 'metodo_pago' => 'QR', 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_cliente' => 3, 'id_empleado' => 6, 'id_caja' => 2, 'nro_factura' => 'VTA-003', 'fecha_hora' => '2026-06-23 14:00:00', 'subtotal' => 95, 'descuento' => 0, 'total' => 95, 'metodo_pago' => 'Efectivo', 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_cliente' => 5, 'id_empleado' => 6, 'id_caja' => 2, 'nro_factura' => 'VTA-004', 'fecha_hora' => '2026-06-23 16:45:00', 'subtotal' => 300, 'descuento' => 50, 'total' => 250, 'metodo_pago' => 'QR', 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_cliente' => 7, 'id_empleado' => 5, 'id_caja' => 3, 'nro_factura' => 'VTA-005', 'fecha_hora' => '2026-06-22 09:30:00', 'subtotal' => 80, 'descuento' => 0, 'total' => 80, 'metodo_pago' => 'Efectivo', 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
        ]);

        // ===================== 19. TDetallesVenta =====================
        DB::table('TDetallesVenta')->insert([
            ['id_venta' => 1, 'id_producto' => 1, 'cantidad' => 2, 'precio_unitario_historico' => 85, 'subtotal' => 170, 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_venta' => 1, 'id_producto' => 8, 'cantidad' => 1, 'precio_unitario_historico' => 35, 'subtotal' => 35, 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_venta' => 2, 'id_producto' => 4, 'cantidad' => 1, 'precio_unitario_historico' => 150, 'subtotal' => 150, 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_venta' => 3, 'id_producto' => 6, 'cantidad' => 1, 'precio_unitario_historico' => 95, 'subtotal' => 95, 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_venta' => 4, 'id_producto' => 2, 'cantidad' => 1, 'precio_unitario_historico' => 120, 'subtotal' => 120, 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_venta' => 4, 'id_producto' => 3, 'cantidad' => 1, 'precio_unitario_historico' => 65, 'subtotal' => 65, 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_venta' => 4, 'id_producto' => 5, 'cantidad' => 1, 'precio_unitario_historico' => 45, 'subtotal' => 45, 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_venta' => 4, 'id_producto' => 7, 'cantidad' => 1, 'precio_unitario_historico' => 90, 'subtotal' => 90, 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
        ]);

        // ===================== 20. TOrdenesTrabajo =====================
        DB::table('TOrdenesTrabajo')->insert([
            ['id_motocicleta' => 1, 'id_empleado' => 2, 'nro_orden' => 'OT-001', 'fecha_ingreso' => '2026-06-20', 'fecha_cierre' => null, 'estado' => 'en_proceso', 'condicion_entrada' => 'Moto con frenos desgastados, requiere cambio urgente', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_motocicleta' => 3, 'id_empleado' => 3, 'nro_orden' => 'OT-002', 'fecha_ingreso' => '2026-06-21', 'fecha_cierre' => '2026-06-22', 'estado' => 'completada', 'condicion_entrada' => 'Service programado 5000km', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_motocicleta' => 4, 'id_empleado' => 4, 'nro_orden' => 'OT-003', 'fecha_ingreso' => '2026-06-22', 'fecha_cierre' => null, 'estado' => 'pendiente', 'condicion_entrada' => 'Foco delantero quemado, cambiar LED', 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_motocicleta' => 2, 'id_empleado' => 2, 'nro_orden' => 'OT-004', 'fecha_ingreso' => '2026-06-18', 'fecha_cierre' => '2026-06-19', 'estado' => 'completada', 'condicion_entrada' => 'Motor making noise, diagnosticar', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_motocicleta' => 5, 'id_empleado' => 3, 'nro_orden' => 'OT-005', 'fecha_ingreso' => '2026-06-23', 'fecha_cierre' => null, 'estado' => 'en_proceso', 'condicion_entrada' => 'Cambio de aceite y filtro pendiente', 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
        ]);

        // ===================== 21. THistorialesEstado =====================
        DB::table('THistorialesEstado')->insert([
            ['id_orden' => 1, 'fecha_hora_cambio' => '2026-06-20 09:00:00', 'estado_asignado' => 'pendiente', 'observacion' => 'Orden creada por recepcion', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_orden' => 1, 'fecha_hora_cambio' => '2026-06-20 10:30:00', 'estado_asignado' => 'en_proceso', 'observacion' => 'Mecanico Garcia asumio la orden', 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_orden' => 2, 'fecha_hora_cambio' => '2026-06-21 08:00:00', 'estado_asignado' => 'pendiente', 'observacion' => 'Service programado', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_orden' => 2, 'fecha_hora_cambio' => '2026-06-21 09:00:00', 'estado_asignado' => 'en_proceso', 'observacion' => 'Mecanico Torres inicio service', 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
            ['id_orden' => 2, 'fecha_hora_cambio' => '2026-06-22 14:00:00', 'estado_asignado' => 'completada', 'observacion' => 'Service completado, cliente notificado', 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
            ['id_orden' => 4, 'fecha_hora_cambio' => '2026-06-18 11:00:00', 'estado_asignado' => 'pendiente', 'observacion' => 'Diagnostico solicitado', 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_orden' => 4, 'fecha_hora_cambio' => '2026-06-18 14:00:00', 'estado_asignado' => 'en_proceso', 'observacion' => 'Diagnostico en curso', 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_orden' => 4, 'fecha_hora_cambio' => '2026-06-19 16:00:00', 'estado_asignado' => 'completada', 'observacion' => 'Reparacion terminada', 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
        ]);

        // ===================== 22. TDetallesOrdenTrabajo =====================
        DB::table('TDetallesOrdenTrabajo')->insert([
            ['id_orden' => 1, 'id_servicio' => 2, 'descripcion' => 'Ajuste completo de frenos delanteros y traseros', 'cantidad' => 1, 'precio_labor' => 120, 'subtotal' => 120, 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_orden' => 2, 'id_servicio' => 4, 'descripcion' => 'Service 5000km completo', 'cantidad' => 1, 'precio_labor' => 300, 'subtotal' => 300, 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
            ['id_orden' => 2, 'id_servicio' => 1, 'descripcion' => 'Cambio de aceite incluido en service', 'cantidad' => 1, 'precio_labor' => 80, 'subtotal' => 80, 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
            ['id_orden' => 3, 'id_servicio' => null, 'descripcion' => 'Cambio de foco delantero LED', 'cantidad' => 1, 'precio_labor' => 50, 'subtotal' => 50, 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_orden' => 4, 'id_servicio' => 5, 'descripcion' => 'Diagnostico y reparacion menor de motor', 'cantidad' => 1, 'precio_labor' => 200, 'subtotal' => 200, 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_orden' => 5, 'id_servicio' => 1, 'descripcion' => 'Cambio de aceite y filtro', 'cantidad' => 1, 'precio_labor' => 80, 'subtotal' => 80, 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
        ]);

        // ===================== 23. TListasVerificacion =====================
        DB::table('TListasVerificacion')->insert([
            ['id_orden' => 2, 'frenos_revisados' => true, 'luces_revisadas' => true, 'piezas_ajustadas' => true, 'prueba_ruta' => true, 'fecha_validacion' => '2026-06-22', 'fecha' => '2026-06-22 13:00:00', 'kilometraje' => 5120, 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
            ['id_orden' => 4, 'frenos_revisados' => true, 'luces_revisadas' => true, 'piezas_ajustadas' => false, 'prueba_ruta' => false, 'fecha_validacion' => '2026-06-19', 'fecha' => '2026-06-19 15:00:00', 'kilometraje' => 3200, 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_orden' => 1, 'frenos_revisados' => false, 'luces_revisadas' => false, 'piezas_ajustadas' => false, 'prueba_ruta' => false, 'fecha_validacion' => null, 'fecha' => null, 'kilometraje' => null, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_orden' => 3, 'frenos_revisados' => false, 'luces_revisadas' => false, 'piezas_ajustadas' => false, 'prueba_ruta' => false, 'fecha_validacion' => null, 'fecha' => null, 'kilometraje' => null, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
            ['id_orden' => 5, 'frenos_revisados' => false, 'luces_revisadas' => false, 'piezas_ajustadas' => false, 'prueba_ruta' => false, 'fecha_validacion' => null, 'fecha' => null, 'kilometraje' => null, 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => $now],
        ]);

        // ===================== 24. TReservas =====================
        DB::table('TReservas')->insert([
            ['id_cliente' => 1, 'monto_adelanto' => 100, 'adelanto_metodo_pago' => 'Efectivo', 'fecha_solicitud' => '2026-06-20', 'fecha_expiracion' => '2026-06-30', 'estado' => 'pendiente', 'departamento_origen' => 'Santa Cruz', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_cliente' => 2, 'monto_adelanto' => 50, 'adelanto_metodo_pago' => 'QR', 'fecha_solicitud' => '2026-06-21', 'fecha_expiracion' => '2026-07-01', 'estado' => 'completada', 'departamento_origen' => 'La Paz', 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_cliente' => 3, 'monto_adelanto' => 0, 'adelanto_metodo_pago' => null, 'fecha_solicitud' => '2026-06-22', 'fecha_expiracion' => '2026-07-02', 'estado' => 'pendiente', 'departamento_origen' => 'Cochabamba', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_cliente' => 5, 'monto_adelanto' => 150, 'adelanto_metodo_pago' => 'QR', 'fecha_solicitud' => '2026-06-18', 'fecha_expiracion' => '2026-06-28', 'estado' => 'cancelada', 'departamento_origen' => 'Oruro', 'estadoA' => false, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_cliente' => 7, 'monto_adelanto' => 75, 'adelanto_metodo_pago' => 'Efectivo', 'fecha_solicitud' => '2026-06-23', 'fecha_expiracion' => '2026-07-03', 'estado' => 'enviado', 'departamento_origen' => 'Tarija', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
        ]);

        // ===================== 25. TDetallesReservas =====================
        DB::table('TDetallesReservas')->insert([
            ['id_reserva' => 1, 'id_producto' => 1, 'cantidad_reservada' => 2, 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_reserva' => 1, 'id_producto' => 2, 'cantidad_reservada' => 1, 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_reserva' => 2, 'id_producto' => 4, 'cantidad_reservada' => 1, 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_reserva' => 2, 'id_producto' => 3, 'cantidad_reservada' => 2, 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_reserva' => 3, 'id_producto' => 5, 'cantidad_reservada' => 3, 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_reserva' => 3, 'id_producto' => 8, 'cantidad_reservada' => 4, 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_reserva' => 4, 'id_producto' => 6, 'cantidad_reservada' => 1, 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_reserva' => 5, 'id_producto' => 1, 'cantidad_reservada' => 2, 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
        ]);

        // ===================== 26. TEnvios =====================
        DB::table('TEnvios')->insert([
            ['id_reserva' => 2, 'empresa_transporte' => 'Boliviana de Envios', 'nro_guia' => 'BOL-2026-001', 'fecha_despacho' => '2026-06-22', 'estado_envio' => 'entregado', 'estadoA' => true, 'usuarioA' => 8, 'fechahoraA' => $now],
            ['id_reserva' => 5, 'empresa_transporte' => 'Transporte Rapido', 'nro_guia' => 'TRA-2026-002', 'fecha_despacho' => '2026-06-24', 'estado_envio' => 'en_transito', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
            ['id_reserva' => 3, 'empresa_transporte' => 'Envios Express', 'nro_guia' => 'EXP-2026-003', 'fecha_despacho' => '2026-06-25', 'estado_envio' => 'en_transito', 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
        ]);

        // ===================== 27. TReservasVentas =====================
        DB::table('TReservasVentas')->insert([
            ['id_reserva' => 2, 'id_venta' => 3, 'estadoA' => true, 'usuarioA' => 6, 'fechahoraA' => $now],
            ['id_reserva' => 5, 'id_venta' => 5, 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
        ]);

        // ===================== 28. TTurnos =====================
        DB::table('TTurnos')->insert([
            ['id_empleado' => 2, 'fecha' => '2026-06-24', 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'hora_entrada_esperada' => '08:00:00', 'hora_salida_esperada' => '17:00:00', 'observacion' => null, 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_empleado' => 3, 'fecha' => '2026-06-24', 'hora_entrada' => '08:05:00', 'hora_salida' => '17:10:00', 'hora_entrada_esperada' => '08:00:00', 'hora_salida_esperada' => '17:00:00', 'observacion' => 'Llego 5 min tarde', 'estadoA' => true, 'usuarioA' => 3, 'fechahoraA' => $now],
            ['id_empleado' => 5, 'fecha' => '2026-06-24', 'hora_entrada' => '07:55:00', 'hora_salida' => '17:05:00', 'hora_entrada_esperada' => '08:00:00', 'hora_salida_esperada' => '17:00:00', 'observacion' => null, 'estadoA' => true, 'usuarioA' => 5, 'fechahoraA' => $now],
            ['id_empleado' => 2, 'fecha' => '2026-06-23', 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'hora_entrada_esperada' => '08:00:00', 'hora_salida_esperada' => '17:00:00', 'observacion' => null, 'estadoA' => true, 'usuarioA' => 2, 'fechahoraA' => $now],
            ['id_empleado' => 4, 'fecha' => '2026-06-24', 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'hora_entrada_esperada' => '08:00:00', 'hora_salida_esperada' => '17:00:00', 'observacion' => null, 'estadoA' => true, 'usuarioA' => 4, 'fechahoraA' => $now],
            ['id_empleado' => 7, 'fecha' => '2026-06-24', 'hora_entrada' => '07:58:00', 'hora_salida' => '17:00:00', 'hora_entrada_esperada' => '08:00:00', 'hora_salida_esperada' => '17:00:00', 'observacion' => null, 'estadoA' => true, 'usuarioA' => 7, 'fechahoraA' => $now],
        ]);

        // ===================== 29. TPlanillas =====================
        DB::table('TPlanillas')->insert([
            ['id_empleado' => 1, 'mes' => 6, 'anio' => 2026, 'sueldo_bruto' => 5000, 'bonos' => 500, 'deducciones' => 750, 'sueldo_neto' => 4750, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_empleado' => 2, 'mes' => 6, 'anio' => 2026, 'sueldo_bruto' => 3500, 'bonos' => 300, 'deducciones' => 525, 'sueldo_neto' => 3275, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_empleado' => 3, 'mes' => 6, 'anio' => 2026, 'sueldo_bruto' => 3500, 'bonos' => 200, 'deducciones' => 525, 'sueldo_neto' => 3175, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_empleado' => 5, 'mes' => 6, 'anio' => 2026, 'sueldo_bruto' => 2800, 'bonos' => 150, 'deducciones' => 420, 'sueldo_neto' => 2530, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
            ['id_empleado' => 9, 'mes' => 6, 'anio' => 2026, 'sueldo_bruto' => 6000, 'bonos' => 800, 'deducciones' => 900, 'sueldo_neto' => 5900, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now],
        ]);

        // ===================== 30. TProgramaciones =====================
        // Empleado 1: Carlos (Admin) — Lun-Vie 08:00-17:00
        $progCarlos = [];
        for ($d = 1; $d <= 5; $d++) {
            $progCarlos[] = ['id_empleado' => 1, 'dia_semana' => $d, 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 2: María (Mecánico) — Lun-Sáb 08:00-17:00
        $progMaria = [];
        for ($d = 1; $d <= 6; $d++) {
            $progMaria[] = ['id_empleado' => 2, 'dia_semana' => $d, 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 3: Juan (Mecánico) — Lun-Vie 08:00-17:00
        $progJuan = [];
        for ($d = 1; $d <= 5; $d++) {
            $progJuan[] = ['id_empleado' => 3, 'dia_semana' => $d, 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 4: Ana (Mecánico) — Lun-Vie 08:30-17:30
        $progAna = [];
        for ($d = 1; $d <= 5; $d++) {
            $progAna[] = ['id_empleado' => 4, 'dia_semana' => $d, 'hora_entrada' => '08:30:00', 'hora_salida' => '17:30:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 5: Pedro (Cajero) — Lun-Sáb 07:30-16:30
        $progPedro = [];
        for ($d = 1; $d <= 6; $d++) {
            $progPedro[] = ['id_empleado' => 5, 'dia_semana' => $d, 'hora_entrada' => '07:30:00', 'hora_salida' => '16:30:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 6: Laura (Cajero) — Lun-Vie 08:00-17:00
        $progLaura = [];
        for ($d = 1; $d <= 5; $d++) {
            $progLaura[] = ['id_empleado' => 6, 'dia_semana' => $d, 'hora_entrada' => '08:00:00', 'hora_salida' => '17:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 7: Sofia (Recepcionista) — Lun-Sáb 08:00-16:00
        $progSofia = [];
        for ($d = 1; $d <= 6; $d++) {
            $progSofia[] = ['id_empleado' => 7, 'dia_semana' => $d, 'hora_entrada' => '08:00:00', 'hora_salida' => '16:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 8: Diego (Recepcionista) — Lun-Vie 09:00-18:00
        $progDiego = [];
        for ($d = 1; $d <= 5; $d++) {
            $progDiego[] = ['id_empleado' => 8, 'dia_semana' => $d, 'hora_entrada' => '09:00:00', 'hora_salida' => '18:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        // Empleado 9: Roberto (Gerente) — Lun-Vie 07:30-17:00
        $progRoberto = [];
        for ($d = 1; $d <= 5; $d++) {
            $progRoberto[] = ['id_empleado' => 9, 'dia_semana' => $d, 'hora_entrada' => '07:30:00', 'hora_salida' => '17:00:00', 'activo' => true, 'estadoA' => true, 'usuarioA' => 1, 'fechahoraA' => $now];
        }

        $todasProgramaciones = array_merge(
            $progCarlos, $progMaria, $progJuan, $progAna,
            $progPedro, $progLaura, $progSofia, $progDiego, $progRoberto
        );
        foreach ($todasProgramaciones as $p) {
            DB::table('TProgramaciones')->insert($p);
        }

        // ===================== Usuarios desactivados para probar reactivacion =====================
        DB::table('TEmpleados')->where('id_empleado', 10)->update(['estadoA' => false, 'usuarioA' => 1, 'fechahoraA' => $now]);
        DB::table('TClientes')->where('id_cliente', 10)->update(['estadoA' => false, 'usuarioA' => 1, 'fechahoraA' => $now]);
    }
}
