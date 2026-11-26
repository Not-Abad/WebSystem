<?php

namespace App\Support;

use App\Models\AceptacionCriterio;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CriterioEvaluacion;
use App\Models\Evaluacion;
use Illuminate\Support\Collection;

/**
 * Reglas únicas de calificación usadas por docentes, alumnos y tutores.
 *
 * El docente captura un porcentaje de 0 a 100 por criterio. La calificación
 * de ese criterio en escala 0–10 es ese porcentaje entre 10. La calificación
 * del parcial es el promedio ponderado de esas notas, de modo que el máximo
 * siempre sea 10 aunque los pesos capturados no sumen exactamente 100.
 */
final class CalificacionService
{
    public static function notaCriterio(float $porcentajeObtenido): float
    {
        return round($porcentajeObtenido / 10, 2);
    }

    /** @param Collection<int, CriterioEvaluacion> $criterios */
    public static function ponderacionTotal(Collection $criterios): float
    {
        return (float) $criterios->sum(fn (CriterioEvaluacion $criterio) => (float) $criterio->porcentaje);
    }

    public static function aportacionCriterio(
        float $notaCriterio,
        float $porcentajeCriterio,
        float $ponderacionTotal
    ): float {
        if ($ponderacionTotal <= 0) {
            return 0.0;
        }

        return round($notaCriterio * ($porcentajeCriterio / $ponderacionTotal), 2);
    }

    /** @return array<string, mixed> */
    public static function criterioArreglo(CriterioEvaluacion $criterio): array
    {
        return [
            'id_criterio' => (int) $criterio->id_criterio,
            'nombre_rubro' => (string) $criterio->nombre_rubro,
            'tipo_evidencia' => (string) $criterio->tipo_evidencia,
            'porcentaje' => (float) $criterio->porcentaje,
            'fecha_entrega' => $criterio->fecha_entrega?->format('Y-m-d'),
        ];
    }

    /**
     * @param  Collection<int, CriterioEvaluacion>  $criterios
     * @return array<string, mixed>
     */
    public static function calificacionParcial(Collection $criterios, Alumno $alumno): array
    {
        $ponderacionTotal = self::ponderacionTotal($criterios);

        $notas = $criterios
            ->flatMap(fn (CriterioEvaluacion $criterio) => $criterio->calificaciones)
            ->filter(fn (Calificacion $calificacion) => (int) $calificacion->id_alumno === (int) $alumno->id_alumno)
            ->keyBy(fn (Calificacion $calificacion) => (int) $calificacion->id_criterio);

        $aportaciones = collect();
        $suma = 0.0;

        foreach ($criterios as $criterio) {
            $calificacion = $notas->get((int) $criterio->id_criterio);

            if (! $calificacion) {
                continue;
            }

            $aportacion = self::aportacionCriterio(
                self::notaCriterio((float) $calificacion->porcentaje_obtenido),
                (float) $criterio->porcentaje,
                $ponderacionTotal
            );

            $aportaciones->put((int) $criterio->id_criterio, $aportacion);
            $suma += $aportacion;
        }

        $tieneCalificacion = $criterios->isNotEmpty()
            && $criterios->every(fn (CriterioEvaluacion $criterio) => $notas->has((int) $criterio->id_criterio));

        return [
            'notas' => $notas,
            'aportaciones' => $aportaciones,
            'calificacion' => round($suma, 2),
            'tieneCalificacion' => $tieneCalificacion,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resumenEvaluacion(
        Evaluacion $evaluacion,
        Alumno $alumno,
        ?AceptacionCriterio $aceptacion = null
    ): array {
        $criterios = $evaluacion->criterios->sortBy('id_criterio')->values();
        $parcial = self::calificacionParcial($criterios, $alumno);

        $base = $aceptacion
            ? collect($aceptacion->criterios ?: [])
            : $criterios->map(fn (CriterioEvaluacion $criterio) => self::criterioArreglo($criterio));

        $filas = $base->map(function ($criterio) use ($parcial) {
            $idCriterio = (int) ($criterio['id_criterio'] ?? 0);
            $calificacion = $parcial['notas']->get($idCriterio);

            return [
                'id_criterio' => $idCriterio,
                'tipo_evidencia' => $criterio['tipo_evidencia'] ?? null,
                'nombre_rubro' => $criterio['nombre_rubro'] ?? '',
                'porcentaje' => (float) ($criterio['porcentaje'] ?? 0),
                'fecha_entrega' => $criterio['fecha_entrega'] ?? null,
                'porcentaje_obtenido' => $calificacion
                    ? (float) $calificacion->porcentaje_obtenido
                    : null,
                'calificacion' => $calificacion
                    ? self::notaCriterio((float) $calificacion->porcentaje_obtenido)
                    : null,
                'aportacion' => $parcial['aportaciones']->get($idCriterio),
            ];
        });

        return [
            'aceptada' => $aceptacion !== null,
            'criterios' => $filas,
            'calificacion' => $parcial['calificacion'],
            'tieneCalificacion' => $parcial['tieneCalificacion'],
        ];
    }

    /** @param Collection<int, array<string, mixed>> $parciales */
    public static function calificacionFinal(Collection $parciales, bool $requiereAceptacion = true): ?float
    {
        if ($parciales->count() !== 3) {
            return null;
        }

        $completo = $parciales->every(
            fn (array $parcial) => $parcial['tieneCalificacion']
                && (! $requiereAceptacion || $parcial['aceptada'])
        );

        return $completo ? round((float) $parciales->avg('calificacion'), 2) : null;
    }
}
