<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clase extends Model
{
    protected $table = 'clases';
    protected $primaryKey = 'id_clase';

    protected $fillable = [
        'id_materia',
        'id_docente',
        'id_grupo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_clase', 'id_clase');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'id_clase', 'id_clase');
    }

    public function criterios(): HasMany
    {
        return $this->hasMany(CriterioEvaluacion::class, 'id_clase', 'id_clase');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_clase', 'id_clase');
    }

    public function reincoExportaciones(): HasMany
    {
        return $this->hasMany(ReincoExportacion::class, 'id_clase', 'id_clase');
    }

    /**
     * Alumnos del grupo que siguen activos para efectos de asistencia y calificación.
     *
     * @return Collection<int, Alumno>
     */
    public function alumnosActivos(): Collection
    {
        $grupo = $this->grupo;
        if (! $grupo) {
            return new Collection();
        }

        return $grupo->alumnos()
            ->whereNotIn('id_estado_alumno', [
                EstadoAlumno::BAJA_DEFINITIVA,
                EstadoAlumno::BAJA_TEMPORAL,
                EstadoAlumno::GRADUADO,
                EstadoAlumno::REINSCRIPCION,
            ])
            ->with('usuario')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    public function docenteNombre(): string
    {
        return trim(
            $this->docente?->nombre.' '.
            $this->docente?->apellido_paterno.' '.
            $this->docente?->apellido_materno
        );
    }
}
