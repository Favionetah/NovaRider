<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'TReservas';
    protected $primaryKey = 'id_reserva';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'monto_adelanto',
        'adelanto_metodo_pago',
        'fecha_solicitud',
        'fecha_expiracion',
        'estado',
        'departamento_origen',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
        'monto_adelanto' => 'float',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleReserva::class, 'id_reserva');
    }

    public function envio()
    {
        return $this->hasOne(Envio::class, 'id_reserva');
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'TReservasVentas', 'id_reserva', 'id_venta');
    }
}
