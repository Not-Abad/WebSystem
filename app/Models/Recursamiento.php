<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recursamiento extends Model
{
    public const RONDA_MAXIMA = 4;
    public const CALIFICACION_MINIMA_APROBATORIA = 6.0;
    public const MAXIMO_PRIMERA_RONDA = 100;
    public const MAXIMO_RONDAS_POSTERIORES = 80;

    public const PENDIENTE = 'pendiente';
    public const APROBADA = 'aprobada';
    public const REPROBADA = 'reprobada';
    public const BAJA_TEMPORAL = 'baja_temporal';

    protected $table = 'recursamientos';
    protected $primaryKey = 'id_recursamiento';

    protected $fillable = [
        'id_alumno',
        'id_materia',
        'id_plan',
        'semestre',
        'id_grupo_original',
        'id_docente',
        'ronda',
        'estatus',
        'calificacion_final',
        'fecha_examen',
    ];

    protected $casts = [
        'semestre' => 'integer',
        'ronda' => 'integer',
        'calificacion_final' => 'float',
        'fecha_examen' => 'date:Y-m-d',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan', 'id_plan');
    }

    public function grupoOriginal(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo_original', 'id_grupo');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }

    public function etiquetaRonda(): string
    {
        return 'R'.$this->ronda;
    }

    public function maximoPorcentaje(): int
    {
        return $this->ronda === 1
            ? self::MAXIMO_PRIMERA_RONDA
            : self::MAXIMO_RONDAS_POSTERIORES;
    }
}
