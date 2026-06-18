<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'TPersonas';
    protected $primaryKey = 'id_persona';
    public $timestamps = false;

    protected $fillable = [
        'ci',
        'primer_nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'telefono',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_persona');
    }
}
