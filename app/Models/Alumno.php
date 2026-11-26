<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumno extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'id_usuario',
        'id_tutor',
        'id_grupo',
        'id_estado_alumno',
        'id_plan',
        'id_semestre',
        'id_capacitacion',
        'id_area_propedeutica',
        'actividad_escolar_apa',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'acta_nacimiento_ruta',
        'boleta_secundaria_ruta',
        'correo',
        'promedio_general',
    ];

    protected $casts = [
        'promedio_general' => 'float',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoAlumno::class, 'id_estado_alumno', 'id_estado_alumno');
    }

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

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_alumno', 'id_alumno');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'id_alumno', 'id_alumno');
    }

    public function aceptacionesCriterios(): HasMany
    {
        return $this->hasMany(AceptacionCriterio::class, 'id_alumno', 'id_alumno');
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombre.' '.$this->apellido_paterno.' '.$this->apellido_materno);
    }
}
