<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    protected $table = 'TProgramaciones';
    protected $primaryKey = 'id_programacion';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'dia_semana',
        'hora_entrada',
        'hora_salida',
        'activo',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
