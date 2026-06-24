<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'TProveedores';
    protected $primaryKey = 'id_proveedor';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor');
    }
}
