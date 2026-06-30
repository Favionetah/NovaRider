<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
=======
>>>>>>> respaldo-caja

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
        DB::transaction(function () {
            // 1. TRoles
            DB::table('TRoles')->insert([
                ['id_rol' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => now()],
                ['id_rol' => 2, 'nombre' => 'Cajero', 'descripcion' => 'Gestion de ventas y caja', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => now()],
                ['id_rol' => 3, 'nombre' => 'Mecanico', 'descripcion' => 'Gestion de ordenes de trabajo', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => now()],
                ['id_rol' => 4, 'nombre' => 'Recepcionista', 'descripcion' => 'Registro y atencion de clientes', 'estadoA' => true, 'usuarioA' => null, 'fechahoraA' => now()],
            ]);

            // 2. TEmpleados (admin - persona fields merged in)
            DB::table('TEmpleados')->insert([
                'ci' => '0000000',
                'primer_nombre' => 'Administrador',
                'segundo_nombre' => null,
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => null,
                'fecha_nacimiento' => null,
                'telefono' => '70000000',
                'fecha_ingreso' => '2026-06-17',
                'sueldo_base' => 0,
                'cargo' => 'Administrador',
                'estadoA' => true,
                'usuarioA' => null,
                'fechahoraA' => now(),
            ]);

            // 3. TUsuarios (admin)
            DB::table('TUsuarios')->insert([
                'id_empleado' => 1,
                'username' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'estadoA' => true,
                'usuarioA' => null,
                'fechahoraA' => now(),
            ]);

            // 4. TUsuarioRol (admin → Administrador)
            DB::table('TUsuarioRol')->insert([
                'id_usuario' => 1,
                'id_rol' => 1,
            ]);
        });
=======
        $this->call([
            DatosPruebaSeeder::class,
        ]);
>>>>>>> respaldo-caja
    }
}
