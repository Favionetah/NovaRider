<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelosCompatible extends Model
{
    protected $table = 'TModelosCompatibles';
    protected $primaryKey = 'id_modelo';
    public $timestamps = false;

    protected $fillable = [
        'marca_moto',
        'modelo_moto',
        'anio_inicio',
        'anio_fin',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'TProductosModelosCompatibles', 'id_modelo', 'id_producto')
            ->withPivot('estadoA', 'usuarioA', 'fechahoraA');
    }
}
