<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planilla extends Model
{
    protected $table = 'TPlanillas';
    protected $primaryKey = 'id_planilla';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'mes',
        'anio',
        'sueldo_bruto',
        'bonos',
        'deducciones',
        'sueldo_neto',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
