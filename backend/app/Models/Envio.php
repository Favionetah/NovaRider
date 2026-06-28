<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $table = 'TEnvios';
    protected $primaryKey = 'id_envio';
    public $timestamps = false;

    protected $fillable = [
        'id_reserva',
        'empresa_transporte',
        'nro_guia',
        'fecha_despacho',
        'estado_envio',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }
}
