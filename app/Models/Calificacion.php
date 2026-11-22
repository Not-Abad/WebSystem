<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificacion extends Model
{
    protected $table = 'calificaciones';
    protected $primaryKey = 'id_calificacion';

    protected $fillable = [
        'id_criterio',
        'id_alumno',
        'puntaje_obtenido',
        'porcentaje_obtenido',
    ];

    protected $casts = [
        'puntaje_obtenido' => 'float',
        'porcentaje_obtenido' => 'float',
    ];

    public function criterio(): BelongsTo
    {
        return $this->belongsTo(CriterioEvaluacion::class, 'id_criterio', 'id_criterio');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}
