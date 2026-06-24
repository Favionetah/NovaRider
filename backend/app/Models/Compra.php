<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'TCompras';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'fecha',
        'nro_factura_proveedor',
        'total_compra',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra');
    }
}
