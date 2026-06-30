<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
           Schema::create('cajas_sesiones', function (Blueprint $table) {
               $table->id('id_sesion');
               $table->unsignedBigInteger('id_usuario_apertura'); 
               $table->decimal('monto_inicial', 10, 2); 
               $table->decimal('monto_final_digital', 10, 2)->default(0.00); 
               $table->decimal('monto_final_fisico', 10, 2)->nullable(); 
               $table->decimal('diferencia', 10, 2)->default(0.00); 
               $table->text('observaciones')->nullable(); 
               $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
               $table->dateTime('fecha_apertura');
               $table->dateTime('fecha_cierre')->nullable();
               $table->timestamps();

               $table->foreign('id_usuario_apertura')->references('id_usuario')->on('tusuarios');
           });
       }

       public function down(): void
       {
           Schema::dropIfExists('cajas_sesiones');
       }
   };