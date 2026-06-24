<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'TUsuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password_hash',
        'id_empleado',
        'estadoA',
        'usuarioA',
        'fechahoraA',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'estadoA' => 'boolean',
        'fechahoraA' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function username()
    {
        return 'username';
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'TUsuarioRol', 'id_usuario', 'id_rol');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }
}
