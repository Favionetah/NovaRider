<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait AuditoriaTrait
{
    protected function registrarAuditoria(
        string $tablaNombre,
        int $registroId,
        string $accion,
        ?string $campo = null,
        ?string $valorAnterior = null,
        ?string $valorNuevo = null,
        ?string $detalles = null
    ): void {
        DB::table('TAuditoriaGeneral')->insert([
            'TablaNombre' => $tablaNombre,
            'RegistroId' => $registroId,
            'Accion' => $accion,
            'Campo' => $campo,
            'ValorAnterior' => $valorAnterior,
            'ValorNuevo' => $valorNuevo,
            'usuarioA' => auth()->id(),
            'fechaHoraA' => now(),
            'direccionIP' => request()->ip(),
            'Detalles' => $detalles,
        ]);
    }
}
