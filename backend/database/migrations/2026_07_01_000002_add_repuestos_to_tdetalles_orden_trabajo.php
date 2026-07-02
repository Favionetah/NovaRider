<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TDetallesOrdenTrabajo', function (Blueprint $table) {
            $table->integer('id_producto')->nullable()->after('id_servicio');
            $table->decimal('costo_unitario', 10, 2)->nullable()->after('precio_labor');

            $table->foreign('id_producto')->references('id_producto')->on('TProductos');
        });
    }

    public function down(): void
    {
        Schema::table('TDetallesOrdenTrabajo', function (Blueprint $table) {
            $table->dropForeign(['id_producto']);
            $table->dropColumn(['id_producto', 'costo_unitario']);
        });
    }
};
