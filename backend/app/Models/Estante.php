<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estante extends Model
{
    protected $table = 'TEstantes';
    protected $primaryKey = 'id_estante';
    public $timestamps = false;

    protected $fillable = [
        'numero_estante',
        'pasillo',
        'descripcion',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'id_estante');
    }

    public function ubicaciones()
    {
        return $this->hasManyThrough(Ubicacion::class, Seccion::class, 'id_estante', 'id_seccion');
    }
}
