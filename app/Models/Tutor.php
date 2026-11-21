<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'id_tutor', 'id_tutor');
    }
}
