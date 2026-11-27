<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KardexMateria extends Model
{
    public const CURSADA = 'cursada';
    public const R1 = 'r1';
    public const R2 = 'r2';
    public const R3 = 'r3';
    public const R4 = 'r4';
    public const APROBADA_R = 'aprobada_r';
    public const BAJA_TEMPORAL = 'baja_temporal';

    protected $table = 'kardex_materias';
    protected $primaryKey = 'id_kardex';

    protected $fillable = [
        'id_alumno',
        'id_materia',
        'id_plan',
        'semestre',
        'estatus',
        'calificacion_final',
        'fecha_finalizacion',
        'id_grupo',
    ];

    protected $casts = [
        'semestre' => 'integer',
        'calificacion_final' => 'float',
        'fecha_finalizacion' => 'date:Y-m-d',
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

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    /** @return array<int, string> */
    public static function estatusAprobados(): array
    {
        return [self::CURSADA, self::APROBADA_R];
    }

    public function rondaActual(): ?int
    {
        if (preg_match('/^r([1-4])$/', (string) $this->estatus, $coincidencia) === 1) {
            return (int) $coincidencia[1];
        }

        return null;
    }

    public function etiquetaEstatus(): string
    {
        return match ($this->estatus) {
            self::CURSADA => 'Cursada',
            self::R1 => 'R1',
            self::R2 => 'R2',
            self::R3 => 'R3',
            self::R4 => 'R4',
            self::APROBADA_R => 'Aprobada en R',
            self::BAJA_TEMPORAL => 'Baja temporal',
            default => 'Sin cursar',
        };
    }
}
