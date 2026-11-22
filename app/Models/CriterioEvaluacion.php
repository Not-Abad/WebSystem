<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CriterioEvaluacion extends Model
{
    protected $table = 'criterios_evaluacion';
    protected $primaryKey = 'id_criterio';
    public $timestamps = false;

    protected $fillable = [
        'id_clase',
        'parcial',
        'id_evaluacion',
        'nombre_rubro',
        'tipo_evidencia',
        'porcentaje',
        'fecha_entrega',
    ];

    protected $casts = [
        'parcial' => 'integer',
        'porcentaje' => 'float',
        'fecha_entrega' => 'date:Y-m-d',
    ];

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class, 'id_clase', 'id_clase');
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'id_criterio', 'id_criterio');
    }
}
