<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleReserva extends Model
{
    protected $table = 'TDetallesReservas';
    protected $primaryKey = 'id_detalle_reserva';
    public $timestamps = false;

    protected $fillable = [
        'id_reserva',
        'id_producto',
        'cantidad_reservada',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
