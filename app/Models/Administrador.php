<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrador extends Model
{
    protected $table = 'administradores';
    protected $primaryKey = 'id_administrador';

    protected $fillable = [
        'id_usuario',
        'id_puesto',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'matricula_rfc',
        'correo_institucional',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(PuestoAdministrativo::class, 'id_puesto', 'id_puesto');
    }
}
