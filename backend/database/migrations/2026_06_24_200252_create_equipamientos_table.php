<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla para el inventario de herramientas pesadas del taller Novarider
        Schema::create('equipamientos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_equipo')->unique(); // Ej: ELEV-01, SCAN-02
            $table->string('nombre');                 // Ej: Elevador Hidráulico Rampa 1
            $table->string('tipo');                   // Ej: Herramienta Pesada, Diagnóstico, Neumática
            
            // Estado operativo actual del activo del taller
            $table->enum('estado', ['Operativo', 'En Mantenimiento', 'Fuera de Servicio', 'Falla Reportada'])->default('Operativo');
            
            $table->string('ubicacion_bahia')->nullable(); // En qué zona o bahía del taller está
            $table->date('fecha_adquisicion')->nullable();
            $table->text('notas_mantenimiento')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipamientos');
    }
};