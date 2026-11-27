<?php

namespace App\Services;

use App\Models\AceptacionCriterio;
use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Evaluacion;
use App\Models\Grupo;
use App\Models\KardexMateria;
use App\Models\Materia;
use App\Models\Recursamiento;
use App\Models\Semestre;
use App\Models\EstadoAlumno;
use App\Support\CalificacionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class KardexService
{
    public function __construct(private readonly RecursamientoService $recursamientos)
    {
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function parcialesDeClase(Clase $clase, Alumno $alumno): Collection
    {
        $evaluaciones = Evaluacion::where('id_clase', $clase->id_clase)
            ->with([
                'criterios' => fn ($q) => $q->orderBy('id_criterio'),
                'criterios.calificaciones' => fn ($q) => $q->where('id_alumno', $alumno->id_alumno),
            ])
            ->orderBy('parcial')
            ->get();

        $aceptaciones = AceptacionCriterio::query()
            ->where('id_alumno', $alumno->id_alumno)
            ->whereIn('id_evaluacion', $evaluaciones->pluck('id_evaluacion'))
            ->get()
            ->keyBy('id_evaluacion');

        return $evaluaciones->map(function (Evaluacion $evaluacion) use ($alumno, $aceptaciones) {
            return array_merge(
                ['evaluacion' => $evaluacion],
                CalificacionService::resumenEvaluacion(
                    $evaluacion,
                    $alumno,
                    $aceptaciones->get($evaluacion->id_evaluacion)
                )
            );
        });
    }

    public function calificacionFinalDeClase(Clase $clase, Alumno $alumno): ?float
    {
        return CalificacionService::calificacionFinal($this->parcialesDeClase($clase, $alumno), false);
    }

    /**
     * Retícula completa del plan, con el estado actual de cada materia.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function reticula(Alumno $alumno): Collection
    {
        if (! $alumno->id_plan) {
            return collect();
        }

        $materias = Materia::where('id_plan', $alumno->id_plan)
            ->with('semestre')
            ->orderBy('id_semestre')
            ->orderBy('clave')
            ->get();

        $cursadas = KardexMateria::where('id_alumno', $alumno->id_alumno)->get()->keyBy('id_materia');

        $enCurso = collect();
        if ($alumno->id_grupo) {
            $enCurso = Clase::where('id_grupo', $alumno->id_grupo)
                ->where('is_active', true)
                ->pluck('id_materia')
                ->flip();
        }

        return $materias->map(function (Materia $materia) use ($cursadas, $enCurso) {
            $registro = $cursadas->get($materia->id_materia);

            $estatus = 'pendiente';
            $etiqueta = 'Sin cursar';
            if ($registro) {
                $estatus = $registro->estatus;
                $etiqueta = $registro->etiquetaEstatus();
            } elseif ($enCurso->has($materia->id_materia)) {
                $estatus = 'en_curso';
                $etiqueta = 'En curso';
            }

            return [
                'materia' => $materia,
                'semestre' => (int) ($materia->semestre?->numero_semestre ?? $materia->id_semestre),
                'estatus' => $estatus,
                'etiqueta_estatus' => $etiqueta,
                'calificacion_final' => $registro?->calificacion_final,
                'fecha_finalizacion' => $registro?->fecha_finalizacion,
            ];
        });
    }

    /**
     * Un semestre ya cursado (o anterior) queda fuera del portal de materias.
     */
    public function semestreYaCursado(Alumno $alumno, int $numeroSemestre): bool
    {
        if ($numeroSemestre <= 0) {
            return false;
        }

        return KardexMateria::where('id_alumno', $alumno->id_alumno)
            ->where('semestre', '>=', $numeroSemestre)
            ->exists();
    }

    /**
     * Semestre obligatorio para la siguiente reinscripción.
     */
    public function semestreSiguiente(Alumno $alumno): int
    {
        $ultimo = KardexMateria::where('id_alumno', $alumno->id_alumno)
            ->when($alumno->id_plan, fn ($q, $idPlan) => $q->where('id_plan', $idPlan))
            ->whereNotNull('calificacion_final')
            ->max('semestre');

        return min(6, ((int) $ultimo) + 1);
    }

    /**
     * Cierra un grupo vencido: congela el avance, desasigna a los alumnos y
     * los deja listos para reinscribirse en el semestre inmediatamente posterior.
     *
     * @return array{alumnos: int, materias: int, graduados: int, recursamientos: int}
     */
    public function cerrarGrupo(Grupo $grupo): array
    {
        $planId = (int) $grupo->id_plan;
        $idSemestre = (int) $grupo->id_semestre;
        $numeroSemestre = (int) ($grupo->semestre?->numero_semestre ?? 0);
        $fecha = $grupo->fecha_conclusion?->toDateString() ?? now()->toDateString();

        $materias = Materia::where('id_plan', $planId)
            ->where('id_semestre', $idSemestre)
            ->orderBy('clave')
            ->get();

        $clases = $grupo->clases()
            ->where('is_active', true)
            ->pluck('id_clase', 'id_materia');

        $alumnos = $grupo->alumnos()
            ->whereNotIn('id_estado_alumno', [
                EstadoAlumno::BAJA_DEFINITIVA,
                EstadoAlumno::BAJA_TEMPORAL,
                EstadoAlumno::GRADUADO,
            ])
            ->get();

        $siguiente = Semestre::where('numero_semestre', $numeroSemestre + 1)->value('id_semestre');
        $totalMaterias = 0;
        $totalGraduados = 0;
        $totalRecursamientos = 0;

        foreach ($alumnos as $alumno) {
            foreach ($materias as $materia) {
                $idClase = $clases->get($materia->id_materia);
                $clase = $idClase ? Clase::find($idClase) : null;
                $calificacion = $clase ? $this->calificacionFinalDeClase($clase, $alumno) : null;

                $registro = KardexMateria::firstOrNew([
                    'id_alumno' => $alumno->id_alumno,
                    'id_materia' => $materia->id_materia,
                ]);

                if ($registro->exists && $registro->calificacion_final !== null) {
                    continue;
                }

                $registro->fill([
                    'id_plan' => $planId ?: $alumno->id_plan,
                    'semestre' => $numeroSemestre,
                    'estatus' => KardexMateria::CURSADA,
                    'calificacion_final' => $calificacion,
                    'fecha_finalizacion' => $fecha,
                    'id_grupo' => $grupo->id_grupo,
                ])->save();

                $totalMaterias++;

                if ($calificacion !== null
                    && $calificacion < Recursamiento::CALIFICACION_MINIMA_APROBATORIA) {
                    $this->recursamientos->abrirPrimeraRonda(
                        $registro->fresh(),
                        $clase?->id_docente ? (int) $clase->id_docente : null
                    );
                    $totalRecursamientos++;
                }
            }

            if ($this->graduarSiCorresponde($alumno)) {
                $totalGraduados++;
                continue;
            }

            $alumno->update([
                'id_grupo' => null,
                'id_semestre' => $siguiente,
                'id_estado_alumno' => EstadoAlumno::REINSCRIPCION,
            ]);
        }

        $grupo->forceFill(['vencimiento_procesado_at' => now()])->save();

        return [
            'alumnos' => $alumnos->count(),
            'materias' => $totalMaterias,
            'graduados' => $totalGraduados,
            'recursamientos' => $totalRecursamientos,
        ];
    }

    /**
     * Solo se gradúa quien cursó y calificó todas las materias del plan y
     * además completó el sexto semestre.
     */
    public function puedeGraduarse(Alumno $alumno): bool
    {
        if (! $alumno->id_plan) {
            return false;
        }

        $total = Materia::where('id_plan', $alumno->id_plan)->count();

        if ($total === 0) {
            return false;
        }

        $consulta = KardexMateria::where('id_alumno', $alumno->id_alumno)
            ->where('id_plan', $alumno->id_plan)
            ->whereIn('estatus', KardexMateria::estatusAprobados())
            ->whereNotNull('calificacion_final');

        if ((clone $consulta)->count() < $total) {
            return false;
        }

        return (int) (clone $consulta)->max('semestre') >= 6;
    }

    /** Promedio de todas las calificaciones finales registradas en el kardex. */
    public function promedioFinal(Alumno $alumno): float
    {
        return round((float) KardexMateria::where('id_alumno', $alumno->id_alumno)
            ->where('id_plan', $alumno->id_plan)
            ->whereIn('estatus', KardexMateria::estatusAprobados())
            ->whereNotNull('calificacion_final')
            ->avg('calificacion_final'), 2);
    }

    /**
     * Gradúa automáticamente cuando el plan completo está cursado y guarda
     * el promedio final de todas las calificaciones.
     */
    public function graduarSiCorresponde(Alumno $alumno): bool
    {
        if (! $this->puedeGraduarse($alumno)) {
            return false;
        }

        $promedio = $this->promedioFinal($alumno);

        DB::transaction(function () use ($alumno, $promedio) {
            $alumno->update([
                'id_grupo' => null,
                'id_semestre' => null,
                'id_estado_alumno' => EstadoAlumno::GRADUADO,
                'promedio_general' => $promedio,
            ]);
        });

        return true;
    }
}
