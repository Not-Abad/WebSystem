<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AceptacionCriterio extends Model
{
    protected $table = 'aceptaciones_criterios';
    protected $primaryKey = 'id_aceptacion';

    protected $fillable = [
        'id_alumno',
        'id_clase',
        'id_evaluacion',
        'criterios',
        'fecha_aceptacion',
    ];

    protected $casts = [
        'criterios' => 'array',
        'fecha_aceptacion' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class, 'id_clase', 'id_clase');
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }
}
