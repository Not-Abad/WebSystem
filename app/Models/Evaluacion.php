<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';
    protected $primaryKey = 'id_evaluacion';

    protected $fillable = [
        'id_clase',
        'parcial',
        'tipo',
        'fecha_examen',
        'fecha_entrega_proyecto',
    ];

    protected $casts = [
        'parcial' => 'integer',
        'fecha_examen' => 'date:Y-m-d',
        'fecha_entrega_proyecto' => 'date:Y-m-d',
    ];

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class, 'id_clase', 'id_clase');
    }

    public function criterios(): HasMany
    {
        return $this->hasMany(CriterioEvaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function etiqueta(): string
    {
        return match ((int) $this->parcial) {
            1 => 'Primera evaluación',
            2 => 'Segunda evaluación',
            3 => 'Tercera evaluación',
            default => 'Evaluación '.$this->parcial,
        };
    }
}
