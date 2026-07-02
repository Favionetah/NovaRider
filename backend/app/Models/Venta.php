<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'TVentas';
    protected $primaryKey = 'id_venta';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_empleado',
        'id_caja',
        'nro_factura',
        'fecha_hora',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'concepto_resumen',
        'estadoA',
        'usuarioA',
        'fechahoraA'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }
}
