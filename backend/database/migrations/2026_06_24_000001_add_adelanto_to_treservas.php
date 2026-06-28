<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TReservas', function (Blueprint $table) {
            $table->decimal('monto_adelanto', 11, 2)->default(0)->after('id_cliente');
            $table->enum('adelanto_metodo_pago', ['QR', 'Efectivo'])->nullable()->after('monto_adelanto');
        });
    }

    public function down(): void
    {
        Schema::table('TReservas', function (Blueprint $table) {
            $table->dropColumn(['monto_adelanto', 'adelanto_metodo_pago']);
        });
    }
};
