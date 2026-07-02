<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    protected $table = 'TOrdenesTrabajo';
    protected $primaryKey = 'id_orden';
    public $timestamps = false;

    protected $fillable = [
        'id_motocicleta',
        'id_empleado',
        'nro_orden',
        'codigo_seguimiento',
        'fecha_ingreso',
        'fecha_cierre',
        'estado',
        'condicion_entrada',
        'estadoA',
        'usuarioA',
        'fechahoraA'
    ];

    public function motocicleta()
    {
        return $this->belongsTo(Motocicleta::class, 'id_motocicleta');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrdenTrabajo::class, 'id_orden');
    }
}
