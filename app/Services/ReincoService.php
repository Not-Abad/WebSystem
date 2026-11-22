<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Evaluacion;
use Illuminate\Support\Collection;

/**
 * Genera el concentrado REINCO usado por el plantel:
 *  - REINCO 205: lista de asistencia.
 *  - REINCO 206-A: lista de evidencias y porcentajes asignados.
 *  - REINCO 206-B: asistencia, evidencias y calificación integrada.
 */
class ReincoService
{
    private const TIPO_EVIDENCIA = [
        'examen' => 'Examen',
        'proyecto' => 'Proyecto',
        'ambos' => 'Examen y proyecto',
    ];

    public function generar(Clase $clase, Evaluacion $evaluacion, string $rutaAbsoluta): int
    {
        $clase->loadMissing([
            'materia',
            'grupo.semestre',
            'grupo.plan',
            'docente',
            'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
        ]);
        $evaluacion->loadMissing('criterios');

        $alumnos = $clase->alumnosActivos();
        $asistencias = $clase->asistencias()->get()->groupBy('id_alumno');
        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get();
        $calificaciones = $criterios->isEmpty()
            ? collect()
            : \App\Models\Calificacion::whereIn('id_criterio', $criterios->pluck('id_criterio'))
                ->get()
                ->keyBy(fn ($calificacion) => $calificacion->id_criterio.'-'.$calificacion->id_alumno);

        $writer = new XlsxWriter();
        $writer->agregarHoja('Datos generales', $this->datosGenerales($clase, $evaluacion), [], [28, 52, 28, 40]);
        $writer->agregarHoja('REINCO 205', $this->hoja205($clase, $evaluacion, $alumnos, $asistencias), [], $this->anchos205(count($criterios)));
        $writer->agregarHoja('REINCO 206-A', $this->hoja206A($clase, $evaluacion, $alumnos, $criterios, $calificaciones), [], $this->anchos206A(count($criterios)));
        $writer->agregarHoja('206-A reverso', $this->reverso206A($evaluacion, $criterios), [], [10, 30, 28, 16, 18]);
        $writer->agregarHoja('REINCO 206-B', $this->hoja206B($clase, $evaluacion, $alumnos, $asistencias, $criterios, $calificaciones), [], $this->anchos206B(count($criterios)));

        return $writer->guardar($rutaAbsoluta);
    }

    private function datosGenerales(Clase $clase, Evaluacion $evaluacion): array
    {
        $docente = $clase->docente;

        return [
            [XlsxWriter::texto('FORMATOS DE CONTROL ESCOLAR · REINCO', 2)],
            [XlsxWriter::texto('Bachilleres Antonio María de Rivera Vespertina (AMRV)')],
            [XlsxWriter::texto('Generado', 1), XlsxWriter::texto(now()->format('d/m/Y H:i'))],
            [],
            [XlsxWriter::texto('Materia', 1), XlsxWriter::texto($clase->materia->clave.' · '.$clase->materia->nombre)],
            [XlsxWriter::texto('Plan de estudio', 1), XlsxWriter::texto($clase->grupo->plan?->etiqueta() ?? 'Plan vigente')],
            [XlsxWriter::texto('Semestre', 1), XlsxWriter::texto((string) $clase->grupo->semestre?->numero_semestre)],
            [XlsxWriter::texto('Grupo', 1), XlsxWriter::texto($clase->grupo->etiqueta())],
            [XlsxWriter::texto('Docente', 1), XlsxWriter::texto($docente->nombreCompleto())],
            [XlsxWriter::texto('Personal SEP', 1), XlsxWriter::texto((string) $docente->numero_personal_sep)],
            [XlsxWriter::texto('RFC', 1), XlsxWriter::texto($docente->rfc)],
            [XlsxWriter::texto('Nombramiento', 1), XlsxWriter::texto((string) $docente->nombramiento)],
            [XlsxWriter::texto('Categoría / plaza', 1), XlsxWriter::texto(trim(($docente->categoria ?? '').' · '.($docente->plaza ?? ''), ' ·'))],
            [XlsxWriter::texto('Sindicato', 1), XlsxWriter::texto((string) $docente->sindicato)],
            [XlsxWriter::texto('Título / cédula', 1), XlsxWriter::texto(trim(($docente->titulo ?? '').' · '.($docente->cedula_profesional ?? ''), ' ·'))],
            [],
            [XlsxWriter::texto('Evaluación', 1), XlsxWriter::texto($evaluacion->etiqueta())],
            [XlsxWriter::texto('Tipo', 1), XlsxWriter::texto(self::TIPO_EVIDENCIA[$evaluacion->tipo] ?? (string) $evaluacion->tipo)],
            [XlsxWriter::texto('Fecha de examen', 1), XlsxWriter::texto($evaluacion->fecha_examen?->format('d/m/Y') ?? '')],
            [XlsxWriter::texto('Fecha de entrega de proyecto', 1), XlsxWriter::texto($evaluacion->fecha_entrega_proyecto?->format('d/m/Y') ?? '')],
            [],
            [XlsxWriter::texto('Nota: los porcentajes de las evidencias pueden sumar hasta 100. La calificación integrada se calcula de 0.00 a 10.00.', 4)],
        ];
    }

    /**
     * @param Collection<int, \App\Models\Alumno> $alumnos
     * @param Collection<int, Collection<int, Asistencia>> $asistencias
     */
    private function hoja205(Clase $clase, Evaluacion $evaluacion, Collection $alumnos, Collection $asistencias): array
    {
        $filas = [
            [XlsxWriter::texto('REINCO 205 · LISTA DE ASISTENCIA', 2)],
            [XlsxWriter::texto('Materia: '.$clase->materia->nombre)],
            [XlsxWriter::texto('Grupo: '.$clase->grupo->etiqueta().' · Semestre: '.$clase->grupo->semestre?->numero.' · Evaluación: '.$evaluacion->etiqueta())],
            [XlsxWriter::texto('Docente: '.$clase->docente->nombreCompleto())],
            [],
            [
                XlsxWriter::texto('Núm.', 1),
                XlsxWriter::texto('Primer apellido, segundo apellido y nombre(s)', 1),
                XlsxWriter::texto('Asistencias', 1),
                XlsxWriter::texto('Faltas', 1),
                XlsxWriter::texto('Retardos', 1),
                XlsxWriter::texto('Justificadas', 1),
                XlsxWriter::texto('% de asistencia', 1),
                XlsxWriter::texto('Observaciones', 1),
            ],
        ];

        $totales = ['presente' => 0, 'falta' => 0, 'retardo' => 0, 'justificada' => 0];
        foreach ($alumnos as $indice => $alumno) {
            $registros = $asistencias->get($alumno->id_alumno, collect());
            $conteo = [
                'presente' => $registros->where('estatus', Asistencia::PRESENTE)->count(),
                'falta' => $registros->where('estatus', Asistencia::FALTA)->count(),
                'retardo' => $registros->where('estatus', Asistencia::RETARDO)->count(),
                'justificada' => $registros->where('estatus', Asistencia::JUSTIFICADA)->count(),
            ];
            foreach ($conteo as $clave => $valor) {
                $totales[$clave] += $valor;
            }
            $registradas = array_sum($conteo);
            $porcentaje = $registradas > 0
                ? round(($conteo['presente'] + $conteo['retardo'] + $conteo['justificada']) / $registradas * 100, 2)
                : 0;

            $filas[] = [
                XlsxWriter::numero($indice + 1),
                XlsxWriter::texto($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre),
                XlsxWriter::numero($conteo['presente']),
                XlsxWriter::numero($conteo['falta']),
                XlsxWriter::numero($conteo['retardo']),
                XlsxWriter::numero($conteo['justificada']),
                XlsxWriter::numero($porcentaje),
                XlsxWriter::texto($registros->pluck('observaciones')->filter()->implode('; ')),
            ];
        }

        $filas[] = [XlsxWriter::texto('SUMAS', 1), XlsxWriter::texto(''), ...array_map(fn ($valor) => XlsxWriter::numero($valor), array_values($totales)), XlsxWriter::numero(0), XlsxWriter::texto('')];
        $filas[] = [];
        $filas[] = [XlsxWriter::texto('Fecha: '.now()->format('d/m/Y'))];
        $filas[] = [XlsxWriter::texto('Firma del docente: ______________________________')];
        $filas[] = [XlsxWriter::texto('Nombre y firma de quien recibe en el plantel: ______________________________')];

        return $filas;
    }

    /**
     * @param Collection<int, \App\Models\Alumno> $alumnos
     * @param Collection<int, \App\Models\CriterioEvaluacion> $criterios
     * @param Collection<string, \App\Models\Calificacion> $calificaciones
     */
    private function hoja206A(Clase $clase, Evaluacion $evaluacion, Collection $alumnos, Collection $criterios, Collection $calificaciones): array
    {
        $cabecera = [
            XlsxWriter::texto('Núm.', 1),
            XlsxWriter::texto('Nombre de los educandos', 1),
        ];
        foreach ($criterios as $indice => $criterio) {
            $cabecera[] = XlsxWriter::texto('Evidencia '.($indice + 1).': '.$criterio->tipo_evidencia.' ('.$criterio->porcentaje.'%)', 1);
        }
        $cabecera[] = XlsxWriter::texto('Total de evidencias', 1);
        $cabecera[] = XlsxWriter::texto('Porcentaje total', 1);
        $cabecera[] = XlsxWriter::texto('Fecha de entrega', 1);

        $filas = [
            [XlsxWriter::texto('REINCO 206-A · LISTA DE EVIDENCIAS Y PORCENTAJES ASIGNADOS', 2)],
            [XlsxWriter::texto('Materia: '.$clase->materia->nombre.' · Grupo: '.$clase->grupo->etiqueta().' · '.$evaluacion->etiqueta())],
            [XlsxWriter::texto('Porcentajes asignados: '.number_format((float) $criterios->sum('porcentaje'), 2).'% · Fecha de examen: '.($evaluacion->fecha_examen?->format('d/m/Y') ?? 'No registrada'))],
            [],
            $cabecera,
        ];

        foreach ($alumnos as $indice => $alumno) {
            $fila = [
                XlsxWriter::numero($indice + 1),
                XlsxWriter::texto($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre),
            ];
            $total = 0.0;
            foreach ($criterios as $criterio) {
                $calificacion = $calificaciones->get($criterio->id_criterio.'-'.$alumno->id_alumno);
                $obtenido = $calificacion ? (float) $calificacion->porcentaje_obtenido : 0.0;
                $total += $obtenido;
                $fila[] = XlsxWriter::numero($obtenido);
            }
            $fila[] = XlsxWriter::numero($criterios->count());
            $fila[] = XlsxWriter::numero(round($total, 2));
            $fila[] = XlsxWriter::texto($criterios->first()?->fecha_entrega?->format('d/m/Y') ?? '');
            $filas[] = $fila;
        }

        $filas[] = [];
        $filas[] = [XlsxWriter::texto('Reverso: consulta la hoja «206-A reverso» para el detalle de cada evidencia.', 4)];

        return $filas;
    }

    /**
     * @param Collection<int, \App\Models\CriterioEvaluacion> $criterios
     */
    private function reverso206A(Evaluacion $evaluacion, Collection $criterios): array
    {
        $filas = [
            [XlsxWriter::texto('REINCO 206-A (REVERSO) · DEFINICIÓN DE EVIDENCIAS', 2)],
            [XlsxWriter::texto($evaluacion->etiqueta())],
            [],
            [
                XlsxWriter::texto('Núm.', 1),
                XlsxWriter::texto('Rubro', 1),
                XlsxWriter::texto('Tipo de evidencia', 1),
                XlsxWriter::texto('Valor (%)', 1),
                XlsxWriter::texto('Fecha de entrega', 1),
            ],
        ];

        foreach ($criterios as $indice => $criterio) {
            $filas[] = [
                XlsxWriter::numero($indice + 1),
                XlsxWriter::texto($criterio->nombre_rubro),
                XlsxWriter::texto($criterio->tipo_evidencia),
                XlsxWriter::numero((float) $criterio->porcentaje),
                XlsxWriter::texto($criterio->fecha_entrega?->format('d/m/Y') ?? ''),
            ];
        }

        return $filas;
    }

    /**
     * @param Collection<int, \App\Models\Alumno> $alumnos
     * @param Collection<int, Collection<int, Asistencia>> $asistencias
     * @param Collection<int, \App\Models\CriterioEvaluacion> $criterios
     * @param Collection<string, \App\Models\Calificacion> $calificaciones
     */
    private function hoja206B(Clase $clase, Evaluacion $evaluacion, Collection $alumnos, Collection $asistencias, Collection $criterios, Collection $calificaciones): array
    {
        $filas = [
            [XlsxWriter::texto('REINCO 206-B · REGISTRO DE EVALUACIÓN Y ASISTENCIA', 2)],
            [XlsxWriter::texto('Materia: '.$clase->materia->nombre.' · Grupo: '.$clase->grupo->etiqueta().' · '.$evaluacion->etiqueta())],
            [],
            [
                XlsxWriter::texto('Núm.', 1),
                XlsxWriter::texto('Primer apellido, segundo apellido y nombre(s)', 1),
                XlsxWriter::texto('Asistencias (205)', 1),
                XlsxWriter::texto('Faltas (205)', 1),
                XlsxWriter::texto('% asistencia', 1),
                XlsxWriter::texto('Total evidencias (206-A)', 1),
                XlsxWriter::texto('Total porcentaje obtenido', 1),
                XlsxWriter::texto('Calificación con decimal', 1),
                XlsxWriter::texto('Calificación con letra', 1),
            ],
        ];

        $aprobados = 0;
        $examinados = 0;
        foreach ($alumnos as $indice => $alumno) {
            $registros = $asistencias->get($alumno->id_alumno, collect());
            $asistenciasCount = $registros->where('estatus', Asistencia::PRESENTE)->count()
                + $registros->where('estatus', Asistencia::RETARDO)->count()
                + $registros->where('estatus', Asistencia::JUSTIFICADA)->count();
            $faltas = $registros->where('estatus', Asistencia::FALTA)->count();
            $registradas = $asistenciasCount + $faltas;
            $porcentajeAsistencia = $registradas > 0 ? round($asistenciasCount / $registradas * 100, 2) : 0.0;

            $totalPorcentaje = 0.0;
            foreach ($criterios as $criterio) {
                $calificacion = $calificaciones->get($criterio->id_criterio.'-'.$alumno->id_alumno);
                $totalPorcentaje += $calificacion ? (float) $calificacion->puntaje_obtenido : 0.0;
            }
            $calificacion = round($totalPorcentaje, 2);
            if ($criterios->isNotEmpty() && $this->tieneCalificaciones($calificaciones, $criterios, $alumno)) {
                $examinados++;
                if ($calificacion >= 6) {
                    $aprobados++;
                }
            }

            $filas[] = [
                XlsxWriter::numero($indice + 1),
                XlsxWriter::texto($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre),
                XlsxWriter::numero($asistenciasCount),
                XlsxWriter::numero($faltas),
                XlsxWriter::numero($porcentajeAsistencia),
                XlsxWriter::numero($criterios->count()),
                XlsxWriter::numero(round($totalPorcentaje, 2)),
                XlsxWriter::numero($calificacion),
                XlsxWriter::texto($criterios->isEmpty() ? '' : ($calificacion >= 6 ? 'A' : 'NA')),
            ];
        }

        $filas[] = [];
        $filas[] = [XlsxWriter::texto('DATOS ESTADÍSTICOS', 1)];
        $filas[] = [XlsxWriter::texto('Total de educandos al inicio', 1), XlsxWriter::numero($alumnos->count())];
        $filas[] = [XlsxWriter::texto('Total de educandos examinados', 1), XlsxWriter::numero($examinados)];
        $filas[] = [XlsxWriter::texto('Total de educandos aprobados', 1), XlsxWriter::numero($aprobados)];
        $filas[] = [XlsxWriter::texto('Total de educandos reprobados', 1), XlsxWriter::numero(max(0, $examinados - $aprobados))];
        $filas[] = [XlsxWriter::texto('Total de educandos desafiliados', 1), XlsxWriter::numero(0)];
        $filas[] = [XlsxWriter::texto('Número de clases en el parcial', 1), XlsxWriter::numero($clase->asistencias()->count())];
        $filas[] = [];
        $filas[] = [XlsxWriter::texto('Día, mes y año: '.now()->format('d/m/Y'))];
        $filas[] = [XlsxWriter::texto('Firma del docente: ______________________________')];
        $filas[] = [XlsxWriter::texto('Nombre y firma de quien recibe en el plantel: ______________________________')];
        $filas[] = [XlsxWriter::texto('Fecha de recepción: ______________________________')];

        return $filas;
    }

    /**
     * @param Collection<string, \App\Models\Calificacion> $calificaciones
     * @param Collection<int, \App\Models\CriterioEvaluacion> $criterios
     */
    private function tieneCalificaciones(Collection $calificaciones, Collection $criterios, \App\Models\Alumno $alumno): bool
    {
        return $criterios->every(fn ($criterio) => $calificaciones->has($criterio->id_criterio.'-'.$alumno->id_alumno));
    }

    /** @return array<int, int> */
    private function anchos205(int $numeroCriterios): array
    {
        return array_merge([8, 42, 14, 10, 12, 14, 16, 34], array_fill(0, max(0, $numeroCriterios - 6), 12));
    }

    /** @return array<int, int> */
    private function anchos206A(int $numeroCriterios): array
    {
        return array_merge([8, 42], array_fill(0, $numeroCriterios, 22), [18, 18, 18]);
    }

    /** @return array<int, int> */
    private function anchos206B(int $numeroCriterios): array
    {
        return array_merge([8, 42, 16, 12, 14, 20, 22, 22, 20], array_fill(0, max(0, $numeroCriterios - 8), 12));
    }
}
