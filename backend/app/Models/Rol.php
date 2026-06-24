<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'TRoles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'TUsuarioRol', 'id_rol', 'id_usuario');
    }

    public function scopeActivos($query)
    {
        return $query->where('estadoA', true);
    }
}
