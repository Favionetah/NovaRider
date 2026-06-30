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
        Schema::create('recibos', function (Blueprint $table) {
            $table->id(); // Este ID actuará de forma automática como el número de recibo 
            
            // Relación con la caja activa en la que ingresa el dinero
            $table->unsignedBigInteger('caja_id');
            
            // Datos del cliente y del vehículo (específico para el taller mecánico)
            $table->string('cliente_nombre');
            $table->string('cliente_documento')->nullable();
            $table->string('motocicleta_placa')->nullable(); // Identificador clave de la moto atendida
            
            // Detalles de la transacción
            $table->string('concepto_cobro'); // Ej: "Mantenimiento preventivo", "Cambio de pastillas de freno", etc.
            $table->decimal('monto', 10, 2);
            
            // Modalidad de pago presencial [cite: 38]
            $table->enum('metodo_pago', ['Efectivo', 'Transferencia QR', 'Tarjeta de Débito', 'Tarjeta de Crédito']);
            
            // Usuario operador/recepcionista que realiza el cobro 
            $table->unsignedBigInteger('user_id');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};