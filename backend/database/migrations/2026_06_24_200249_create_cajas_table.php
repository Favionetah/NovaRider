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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            
            // Montos de control de la caja presencial
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_final', 10, 2)->nullable(); // Se llena al cerrar la caja
            
            // Estado de la caja
            $table->enum('estado', ['Abierta', 'Cerrada'])->default('Abierta');
            
            // Auditoría básica
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            
            // Relación con el usuario/empleado que abrió la caja (creado por el Integrante 1)
            // Usamos un entero normal por si la tabla de usuarios aún no está migrada por tu compañero
            $table->unsignedBigInteger('user_id'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};