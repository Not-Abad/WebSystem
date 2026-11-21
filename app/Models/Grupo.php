<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'id_periodo',
        'id_semestre',
        'nombre',
        'cupo_maximo',
    ];

    protected $casts = [
        'cupo_maximo' => 'integer',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo', 'id_periodo');
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre', 'id_semestre');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'id_grupo', 'id_grupo');
    }

    public function etiqueta(): string
    {
        return trim($this->nombre.' · '.($this->semestre?->nombre ?? 'Sin semestre'));
    }
}
