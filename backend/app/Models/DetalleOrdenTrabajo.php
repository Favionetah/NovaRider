<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleOrdenTrabajo extends Model
{
    protected $table = 'TDetallesOrdenTrabajo';
    protected $primaryKey = 'id_detalle_ot';
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'id_servicio',
        'id_producto',
        'descripcion',
        'cantidad',
        'precio_labor',
        'costo_unitario',
        'subtotal',
        'estadoA',
        'usuarioA',
        'fechahoraA'
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
