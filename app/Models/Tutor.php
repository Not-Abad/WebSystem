<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $table = 'tutores';
    protected $primaryKey = 'id_tutor';

    protected $fillable = [
        'id_usuario',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'correo',
        'ine_ruta',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
