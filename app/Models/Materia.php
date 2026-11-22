<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $table = 'materias';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;

    protected $fillable = [
        'id_plan',
        'id_semestre',
        'id_capacitacion',
        'id_area_propedeutica',
        'clave',
        'nombre',
        'tipo_materia',
        'es_tronco_comun',
        'horas_semana',
        'descripcion',
        'objetivo',
        'programa_pdf_ruta',
        'is_active',
    ];

    protected $casts = [
        'horas_semana' => 'integer',
        'es_tronco_comun' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan', 'id_plan');
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre', 'id_semestre');
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaPropedeutica::class, 'id_area_propedeutica', 'id_area_propedeutica');
    }

    public function clases(): HasMany
    {
        return $this->hasMany(Clase::class, 'id_materia', 'id_materia');
    }

    public function etiqueta(): string
    {
        return trim($this->clave.' · '.$this->nombre);
    }
}
