<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'TUbicaciones';
    protected $primaryKey = 'id_ubicacion';
    public $timestamps = false;

    protected $fillable = [
        'id_seccion',
        'nivel',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'id_seccion');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_ubicacion');
    }
}
