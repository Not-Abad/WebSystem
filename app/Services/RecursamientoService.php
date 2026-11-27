<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\EstadoAlumno;
use App\Models\KardexMateria;
use App\Models\Recursamiento;
use App\Support\CalificacionService;
use Illuminate\Support\Facades\DB;

final class RecursamientoService
{
    public function porcentajeMaximo(int $ronda): int
    {
        return $ronda === 1
            ? Recursamiento::MAXIMO_PRIMERA_RONDA
            : Recursamiento::MAXIMO_RONDAS_POSTERIORES;
    }

    /**
     * Abre R1 cuando el promedio de los tres parciales fue menor a 6.
     * Si la ronda ya existe no se duplica, para mantener el proceso idempotente.
     */
    public function abrirPrimeraRonda(KardexMateria $kardex, ?int $idDocente = null): Recursamiento
    {
        $existente = Recursamiento::where('id_alumno', $kardex->id_alumno)
            ->where('id_materia', $kardex->id_materia)
            ->where('ronda', 1)
            ->first();

        if ($existente) {
            if ($existente->estatus === Recursamiento::PENDIENTE) {
                $kardex->update(['estatus' => KardexMateria::R1]);
            }

            return $existente;
        }

        return DB::transaction(function () use ($kardex, $idDocente) {
            $recursamiento = Recursamiento::create([
                'id_alumno' => $kardex->id_alumno,
                'id_materia' => $kardex->id_materia,
                'id_plan' => $kardex->id_plan,
                'semestre' => $kardex->semestre,
                'id_grupo_original' => $kardex->id_grupo,
                'id_docente' => $idDocente,
                'ronda' => 1,
                'estatus' => Recursamiento::PENDIENTE,
                'calificacion_final' => $kardex->calificacion_final,
            ]);

            $kardex->update(['estatus' => KardexMateria::R1]);

            BitacoraService::registrar(
                'recursamientos',
                'ALTA',
                sprintf(
                    'R1 abierto para el alumno %d en la materia %d (semestre %d).',
                    $kardex->id_alumno,
                    $kardex->id_materia,
                    $kardex->semestre
                ),
                $recursamiento->id_recursamiento
            );

            return $recursamiento;
        });
    }

    /**
     * Registra el único examen de la ronda. El porcentaje capturado se convierte
     * a la escala 0–10; 6 es aprobatorio y R2–R4 tienen tope de 80 % (8.0).
     */
    public function calificar(Recursamiento $recursamiento, float $porcentaje): Recursamiento
    {
        if ($recursamiento->estatus !== Recursamiento::PENDIENTE) {
            throw new \InvalidArgumentException('Esta ronda ya fue calificada.');
        }

        $maximo = $this->porcentajeMaximo((int) $recursamiento->ronda);

        if ($porcentaje < 0 || $porcentaje > $maximo) {
            throw new \InvalidArgumentException("La calificación de {$recursamiento->etiquetaRonda()} debe estar entre 0 y {$maximo} %.");
        }

        $nota = CalificacionService::notaCriterio($porcentaje);
        $ronda = (int) $recursamiento->ronda;
        $aprobado = $nota >= Recursamiento::CALIFICACION_MINIMA_APROBATORIA;

        DB::transaction(function () use ($recursamiento, $nota, $ronda, $aprobado) {
            $estatusR = $aprobado
                ? Recursamiento::APROBADA
                : ($ronda >= Recursamiento::RONDA_MAXIMA
                    ? Recursamiento::BAJA_TEMPORAL
                    : Recursamiento::REPROBADA);

            $recursamiento->update([
                'calificacion_final' => $nota,
                'fecha_examen' => now()->toDateString(),
                'estatus' => $estatusR,
            ]);

            $kardex = KardexMateria::where('id_alumno', $recursamiento->id_alumno)
                ->where('id_materia', $recursamiento->id_materia)
                ->firstOrFail();

            $estatusKardex = $aprobado
                ? KardexMateria::APROBADA_R
                : ($ronda >= Recursamiento::RONDA_MAXIMA
                    ? KardexMateria::BAJA_TEMPORAL
                    : 'r'.($ronda + 1));

            $kardex->update([
                'calificacion_final' => $nota,
                'estatus' => $estatusKardex,
            ]);

            if (! $aprobado && $ronda < Recursamiento::RONDA_MAXIMA) {
                Recursamiento::create([
                    'id_alumno' => $recursamiento->id_alumno,
                    'id_materia' => $recursamiento->id_materia,
                    'id_plan' => $recursamiento->id_plan,
                    'semestre' => $recursamiento->semestre,
                    'id_grupo_original' => $recursamiento->id_grupo_original,
                    'id_docente' => $recursamiento->id_docente,
                    'ronda' => $ronda + 1,
                    'estatus' => Recursamiento::PENDIENTE,
                ]);
            }

            if (! $aprobado && $ronda >= Recursamiento::RONDA_MAXIMA) {
                $alumno = Alumno::findOrFail($recursamiento->id_alumno);
                $alumno->update([
                    'id_estado_alumno' => EstadoAlumno::BAJA_TEMPORAL,
                    'id_grupo' => null,
                ]);
                $alumno->usuario?->update(['is_active' => false]);
            }

            BitacoraService::registrar(
                'recursamientos',
                'EDICION',
                sprintf(
                    '%s calificado con %s: %s. Alumno %d, materia %d, semestre %d.',
                    $recursamiento->etiquetaRonda(),
                    number_format($nota, 2),
                    $aprobado ? 'aprobado' : ($ronda >= Recursamiento::RONDA_MAXIMA ? 'baja temporal' : 'pasa a la siguiente ronda'),
                    $recursamiento->id_alumno,
                    $recursamiento->id_materia,
                    $recursamiento->semestre
                ),
                $recursamiento->id_recursamiento
            );
        });

        return $recursamiento->fresh();
    }
}
