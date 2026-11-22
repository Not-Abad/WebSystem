<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReincoExportacion extends Model
{
    protected $table = 'reinco_exportaciones';
    protected $primaryKey = 'id_exportacion';

    protected $fillable = [
        'id_docente',
        'id_clase',
        'id_evaluacion',
        'parcial',
        'estatus',
        'formato_calificacion',
        'turno',
        'periodo_escolar',
        'cct',
        'plantel',
        'hojas',
        'titulo',
        'archivo_ruta',
        'tamano_bytes',
    ];

    protected $casts = [
        'parcial' => 'integer',
        'hojas' => 'integer',
        'tamano_bytes' => 'integer',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class, 'id_clase', 'id_clase');
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function etiqueta(): string
    {
        return $this->evaluacion?->etiqueta() ?? 'Evaluación '.$this->parcial;
    }
}
