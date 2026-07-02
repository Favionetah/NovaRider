<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TOrdenesTrabajo', function (Blueprint $table) {
            $table->string('codigo_seguimiento', 32)->nullable()->unique()->after('nro_orden');
        });

        DB::table('TOrdenesTrabajo')
            ->whereNull('codigo_seguimiento')
            ->orderBy('id_orden')
            ->get(['id_orden'])
            ->each(function ($orden) {
                DB::table('TOrdenesTrabajo')
                    ->where('id_orden', $orden->id_orden)
                    ->update([
                        'codigo_seguimiento' => 'NR-' . str_pad((string) $orden->id_orden, 6, '0', STR_PAD_LEFT),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('TOrdenesTrabajo', function (Blueprint $table) {
            $table->dropUnique(['codigo_seguimiento']);
            $table->dropColumn('codigo_seguimiento');
        });
    }
};
