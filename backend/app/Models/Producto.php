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
<<<<<<< HEAD
=======
        'costo',
>>>>>>> respaldo-caja
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
<<<<<<< HEAD
=======

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion');
    }

    public function modelosCompatibles()
    {
        return $this->belongsToMany(ModelosCompatible::class, 'TProductosModelosCompatibles', 'id_producto', 'id_modelo')
            ->withPivot('estadoA', 'usuarioA', 'fechahoraA');
    }
>>>>>>> respaldo-caja
}
