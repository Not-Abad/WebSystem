<?php

namespace App\Data\Reinco;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\Evaluacion;
use App\Support\CalificacionService;
use Illuminate\Support\Collection;

/**
 * Entrada limpia del generador REINCO. Contiene únicamente datos de la
 * evaluación; la configuración de Excel vive en ReincoPresentacionData.
 */
final readonly class ReincoExportData
{
    /**
     * @param  array<string, mixed>  $institucional
     * @param  array<string, mixed>  $grupo
     * @param  array<string, mixed>  $materia
     * @param  array<string, mixed>  $evaluacion
     * @param  Collection<int, ReincoAlumnoData>  $alumnos
     * @param  Collection<int, ReincoEvidenciaData>  $evidencias
     * @param  array<string, int>  $estadisticas
     */
    public function __construct(
        public array $institucional,
        public array $grupo,
        public array $materia,
        public array $evaluacion,
        public Collection $alumnos,
        public Collection $evidencias,
        public array $estadisticas,
        public ReincoPresentacionData $presentacion,
    ) {
    }

    /**
     * @param  array<string, mixed>  $opciones
     */
    public static function desdeClase(Clase $clase, Evaluacion $evaluacion, array $opciones = [], int $hojas = 5): self
    {
        $clase->loadMissing([
            'materia.area',
            'grupo.semestre',
            'grupo.plan',
            'grupo.area',
            'docente',
            'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
        ]);
        $evaluacion->loadMissing('criterios');

        $config = config('reinco');
        $estatus = self::opcion($opciones, 'estatus', $evaluacion->estatus ?? null, $config['estatus'] ?? 'ordinaria');
        $formato = self::opcion($opciones, 'formato_calificacion', $evaluacion->formato_calificacion ?? null, $config['formato_calificacion'] ?? 'decimal');
        $presentacion = ReincoPresentacionData::desdeConfig(
            ['formato_calificacion' => $formato],
            $hojas
        );

        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get();
        $evidencias = $criterios->map(fn (CriterioEvaluacion $criterio, int $indice) => new ReincoEvidenciaData(
            id: (int) $criterio->id_criterio,
            numero: $indice + 1,
            rubro: (string) $criterio->nombre_rubro,
            tipo: (string) $criterio->tipo_evidencia,
            porcentaje: (float) $criterio->porcentaje,
            fechaEntrega: $criterio->fecha_entrega?->format('d/m/Y') ?? '',
        ))->values();

        $calificaciones = $criterios->isEmpty()
            ? collect()
            : Calificacion::whereIn('id_criterio', $criterios->pluck('id_criterio'))
                ->get()
                ->keyBy(fn (Calificacion $calificacion) => $calificacion->id_criterio.'-'.$calificacion->id_alumno);

        $asistencias = $clase->asistencias()->get();
        $alumnos = $clase->alumnosActivos();
        $decimales = $presentacion->decimales;
        $ponderacionTotal = CalificacionService::ponderacionTotal($criterios);

        $filas = $alumnos->map(function (Alumno $alumno, int $indice) use (
            $criterios,
            $calificaciones,
            $asistencias,
            $decimales,
            $ponderacionTotal
        ) {
            $notas = [];
            foreach ($criterios as $criterio) {
                $calificacion = $calificaciones->get($criterio->id_criterio.'-'.$alumno->id_alumno);
                $notas[$criterio->id_criterio] = [
                    'puntaje' => $calificacion ? round((float) $calificacion->puntaje_obtenido, 2) : 0.0,
                    'porcentaje' => $calificacion ? round((float) $calificacion->porcentaje_obtenido, 2) : 0.0,
                ];
            }

            $examinado = $criterios->isNotEmpty()
                && $criterios->every(fn (CriterioEvaluacion $criterio) => $calificaciones->has($criterio->id_criterio.'-'.$alumno->id_alumno));
            $suma = 0.0;
            foreach ($criterios as $criterio) {
                $calificacion = $calificaciones->get($criterio->id_criterio.'-'.$alumno->id_alumno);

                if ($calificacion) {
                    $suma += CalificacionService::aportacionCriterio(
                        CalificacionService::notaCriterio((float) $calificacion->porcentaje_obtenido),
                        (float) $criterio->porcentaje,
                        $ponderacionTotal
                    );
                }
            }
            $final = round($suma, $decimales);

            $registros = $asistencias->where('id_alumno', $alumno->id_alumno);
            $porParcial = [];
            foreach ([1, 2, 3, 'general'] as $parcial) {
                $delParcial = $parcial === 'general'
                    ? $registros
                    : $registros->where('parcial', $parcial);
                $porParcial[$parcial] = self::conteoAsistencias($delParcial);
            }

            return new ReincoAlumnoData(
                numero: $indice + 1,
                curp: strtoupper((string) $alumno->curp),
                nombreCompleto: trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre),
                asistenciasPorParcial: $porParcial,
                evidencias: $notas,
                calificacionFinal: (float) $final,
                examinado: $examinado,
                observaciones: $registros->pluck('observaciones')->filter()->implode('; '),
            );
        })->values();

        $examinados = $filas->filter(fn (ReincoAlumnoData $fila) => $fila->examinado)->count();
        $aprobados = $filas->filter(fn (ReincoAlumnoData $fila) => $fila->examinado && $fila->calificacionFinal >= 6)->count();

        return new self(
            institucional: [
                'plantel' => self::opcion($opciones, 'plantel', null, $config['plantel'] ?? ''),
                'cct' => self::opcion($opciones, 'cct', null, $config['cct'] ?? ''),
                'nivel' => self::opcion($opciones, 'nivel', null, $config['nivel'] ?? 'Bachillerato'),
                'turno' => self::opcion($opciones, 'turno', null, $config['turno'] ?? ''),
                'periodo_escolar' => self::opcion($opciones, 'periodo_escolar', null, self::periodoEscolar($config['periodo_escolar'] ?? null)),
                'generado' => now()->format('d/m/Y H:i'),
            ],
            grupo: [
                'semestre' => (string) ($clase->grupo->semestre?->numero_semestre ?? ''),
                'grupo' => $clase->grupo->etiqueta(),
                'plan_retricula' => (string) ($clase->grupo->plan?->etiqueta() ?? 'Plan vigente'),
                'area_componente' => (string) ($clase->grupo->area?->nombre ?? $clase->materia->area?->nombre ?? ''),
            ],
            materia: [
                'clave' => (string) $clase->materia->clave,
                'nombre' => (string) $clase->materia->nombre,
                'docente' => $clase->docente->nombreCompleto(),
                'sep' => (string) $clase->docente->numero_personal_sep,
                'rfc' => (string) $clase->docente->rfc,
            ],
            evaluacion: [
                'etiqueta' => $evaluacion->etiqueta(),
                'parcial' => (int) $evaluacion->parcial,
                'tipo' => match ($evaluacion->tipo) {
                    'examen' => 'Examen',
                    'proyecto' => 'Proyecto',
                    'ambos' => 'Examen y proyecto',
                    default => (string) $evaluacion->tipo,
                },
                'fecha_examen' => $evaluacion->fecha_examen?->format('d/m/Y') ?? '',
                'fecha_entrega' => $evaluacion->fecha_entrega_proyecto?->format('d/m/Y') ?? '',
                'estatus' => $estatus,
                'formato_calificacion' => $formato,
            ],
            alumnos: $filas,
            evidencias: $evidencias,
            estadisticas: [
                'inicio' => $alumnos->count(),
                'examinados' => $examinados,
                'aprobados' => $aprobados,
                'reprobados' => max(0, $examinados - $aprobados),
                'clases' => $clase->asistencias()->count(),
            ],
            presentacion: $presentacion,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'institucional' => $this->institucional,
            'grupo' => $this->grupo,
            'materia' => $this->materia,
            'evaluacion' => $this->evaluacion,
            'alumnos' => $this->alumnos->map(fn (ReincoAlumnoData $alumno) => $alumno->toArray())->all(),
            'evidencias' => $this->evidencias->map(fn (ReincoEvidenciaData $evidencia) => $evidencia->toArray())->all(),
            'estadisticas' => $this->estadisticas,
            'presentacion' => [
                'hojas' => $this->presentacion->hojas,
                'orientacion' => $this->presentacion->orientacion,
                'decimales' => $this->presentacion->decimales,
                'margenes' => $this->presentacion->margenes,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $opciones
     */
    private static function opcion(array $opciones, string $clave, mixed $modelo, mixed $defecto): mixed
    {
        $valor = $opciones[$clave] ?? $modelo;

        return is_string($valor) && trim($valor) === '' ? $defecto : ($valor ?? $defecto);
    }

    /**
     * @param  Collection<int, Asistencia>  $registros
     * @return array{presente: int, falta: int, retardo: int, justificada: int, porcentaje: float}
     */
    private static function conteoAsistencias(Collection $registros): array
    {
        $conteo = [
            'presente' => $registros->where('estatus', Asistencia::PRESENTE)->count(),
            'falta' => $registros->where('estatus', Asistencia::FALTA)->count(),
            'retardo' => $registros->where('estatus', Asistencia::RETARDO)->count(),
            'justificada' => $registros->where('estatus', Asistencia::JUSTIFICADA)->count(),
        ];
        $registradas = array_sum($conteo);
        $conteo['porcentaje'] = $registradas > 0
            ? round(($conteo['presente'] + $conteo['retardo'] + $conteo['justificada']) / $registradas * 100, 2)
            : 0.0;

        return $conteo;
    }

    private static function periodoEscolar(mixed $configurado): string
    {
        if (is_string($configurado) && trim($configurado) !== '') {
            return $configurado;
        }

        $anio = (int) now()->format('Y');
        $inicio = (int) now()->format('n') >= 8 ? $anio : $anio - 1;

        return $inicio.'-'.($inicio + 1);
    }
}
