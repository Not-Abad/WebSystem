<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Docente extends Model
{
    protected $table = 'docentes';
    protected $primaryKey = 'id_docente';

    protected $fillable = [
        'id_usuario',
        'id_capacitacion',
        'id_area_propedeutica',
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

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaPropedeutica::class, 'id_area_propedeutica', 'id_area_propedeutica');
    }
}
