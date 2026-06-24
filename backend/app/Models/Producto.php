<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'TProductos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_ubicacion',
        'nombre',
        'descripcion',
        'precio_venta',
        'stock_fisico',
        'stock_disponible',
        'stock_minimo',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];
}
