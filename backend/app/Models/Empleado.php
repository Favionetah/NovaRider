<?php

namespace App\Models;

use App\Models\Planilla;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'TEmpleados';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'ci',
        'primer_nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'telefono',
        'fecha_ingreso',
        'sueldo_base',
        'cargo',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'id_empleado');
    }

    public function programaciones()
    {
        return $this->hasMany(Programacion::class, 'id_empleado');
    }

    public function ultimaPlanilla()
    {
        return $this->hasOne(Planilla::class, 'id_empleado')
            ->where('estadoA', true)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->orderBy('id_planilla', 'desc');
    }
}
