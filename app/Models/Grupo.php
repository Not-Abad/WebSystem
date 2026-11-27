<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false;

    protected $fillable = [
        'id_semestre',
        'id_plan',
        'id_area_propedeutica',
        'nombre',
        'cupo_maximo',
        'fecha_conclusion',
        'vencimiento_procesado_at',
    ];

    protected $casts = [
        'cupo_maximo' => 'integer',
        'fecha_conclusion' => 'date:Y-m-d',
        'vencimiento_procesado_at' => 'datetime',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaPropedeutica::class, 'id_area_propedeutica', 'id_area_propedeutica');
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre', 'id_semestre');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'id_plan', 'id_plan');
    }

    public function clases(): HasMany
    {
        return $this->hasMany(Clase::class, 'id_grupo', 'id_grupo');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'id_grupo', 'id_grupo');
    }

    public function etiqueta(): string
    {
        if ($this->area) {
            return $this->nombre.' de '.$this->area->nombre;
        }

        return $this->nombre.' · '.($this->semestre?->numero_semestre ?? 'Sin semestre');
    }
}
