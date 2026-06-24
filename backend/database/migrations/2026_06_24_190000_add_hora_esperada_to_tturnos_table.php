<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TTurnos', function (Blueprint $table) {
            $table->time('hora_entrada_esperada')->nullable()->after('hora_salida');
            $table->time('hora_salida_esperada')->nullable()->after('hora_entrada_esperada');
        });
    }

    public function down(): void
    {
        Schema::table('TTurnos', function (Blueprint $table) {
            $table->dropColumn(['hora_entrada_esperada', 'hora_salida_esperada']);
        });
    }
};
