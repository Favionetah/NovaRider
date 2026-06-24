<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TProgramaciones', function (Blueprint $table) {
            $table->integer('id_programacion', true);
            $table->integer('id_empleado');
            $table->integer('dia_semana')->comment('1=Lun,2=Mar,3=Mie,4=Jue,5=Vie,6=Sab');
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('estadoA')->default(true);
            $table->integer('usuarioA')->nullable();
            $table->datetime('fechahoraA')->nullable();

            $table->foreign('id_empleado')->references('id_empleado')->on('TEmpleados');
            $table->unique(['id_empleado', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TProgramaciones');
    }
};
