<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'TSecciones';
    protected $primaryKey = 'id_seccion';
    public $timestamps = false;

    protected $fillable = [
        'id_estante',
        'codigo_seccion',
        'descripcion',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function estante()
    {
        return $this->belongsTo(Estante::class, 'id_estante');
    }

    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'id_seccion');
    }
}
