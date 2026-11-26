<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\Docente;
use App\Models\Evaluacion;
use App\Models\ReincoExportacion;
use App\Services\BitacoraService;
use App\Support\CalificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClaseController extends Controller
{
    /** Banco institucional de criterios seleccionables en cada evaluación. */
    public const BANCO_EVIDENCIAS = [
        'Examen',
        'Tareas',
        'Asistencia',
        'Proyecto',
        'Actividades en clase',
        'Participación en clase',
        'Cuaderno de trabajo',
        'Portafolio de evidencias',
        'Investigación documental',
        'Presentación oral',
        'Informe de laboratorio',
        'Trabajo colaborativo',
        'Ejercicios y prácticas',
        'Evaluación continua',
    ];

    /** Los porcentajes seleccionados pueden sumar como máximo este valor. */
    public const PORCENTAJE_MAXIMO_EVALUACION = 100.0;

    /** @var array<int, string> */
    public const ESTATUS_REINCO = ['ordinaria', 'final', 'R1', 'R2', 'R3'];

    /** @var array<int, string> */
    public const FORMATO_CALIFICACION = ['entero', 'decimal'];

    /** @var array<int, string> */
    public const ESTATUS_ASISTENCIA = [
        Asistencia::PRESENTE,
        Asistencia::FALTA,
        Asistencia::RETARDO,
        Asistencia::JUSTIFICADA,
    ];

    private function docente(): Docente
    {
        $docente = Auth::user()->docente()->firstOrFail();
        abort_unless($docente->is_active, 403, 'Tu perfil docente está inactivo.');

        return $docente;
    }

    private function clase(Docente $docente, Clase $clase): Clase
    {
        abort_unless(
            $clase->id_docente === $docente->id_docente && $clase->is_active,
            404
        );

        return $clase;
    }

    private function evaluacion(Clase $clase, Evaluacion $evaluacion): Evaluacion
    {
        abort_unless($evaluacion->id_clase === $clase->id_clase, 404);

        return $evaluacion;
    }

    public function show(Request $request, Clase $clase)
    {
        $docente = $this->docente();
        $clase = $this->clase($docente, $clase)->load([
            'materia',
            'grupo.semestre',
            'grupo.plan',
            'horarios.salon',
            'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
            'evaluaciones.criterios' => fn ($q) => $q->orderBy('id_criterio'),
            'evaluaciones.criterios.calificaciones',
            'reincoExportaciones' => fn ($q) => $q->orderByDesc('id_exportacion'),
        ]);

        $horarioSeleccionado = null;
        if ($request->filled('horario')) {
            $horarioSeleccionado = $clase->horarios->firstWhere('id_horario', (int) $request->input('horario'));
            abort_if($horarioSeleccionado === null, 404, 'El horario seleccionado no pertenece a esta clase.');
        }

        $fecha = $request->date('fecha')?->toDateString() ?? now()->toDateString();
        $alumnos = $clase->alumnosActivos();
        $asistencias = $clase->asistencias()
            ->whereDate('fecha', $fecha)
            ->get()
            ->keyBy('id_alumno');

        return view('docente.clases.show', [
            'titulo' => $clase->materia->nombre,
            'subtitulo' => $clase->grupo->etiqueta().' · '.$clase->materia->clave,
            'icono' => 'fa-chalkboard-user',
            'etiqueta' => 'Docente',
            'docente' => $docente,
            'clase' => $clase,
            'alumnos' => $alumnos,
            'asistencias' => $asistencias,
            'fechaAsistencia' => $fecha,
            'horarioSeleccionado' => $horarioSeleccionado,
            'bancoEvidencias' => self::BANCO_EVIDENCIAS,
            'estatusAsistencia' => self::ESTATUS_ASISTENCIA,
        ]);
    }

    public function guardarAsistencia(Request $request, Clase $clase)
    {
        $docente = $this->docente();
        $clase = $this->clase($docente, $clase);
        $alumnos = $clase->alumnosActivos()->keyBy('id_alumno');

        Validator::make($request->all(), [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'asistencias' => ['required', 'array', 'min:1'],
            'asistencias.*.id_alumno' => ['required', 'integer'],
            'asistencias.*.estatus' => ['required', Rule::in(self::ESTATUS_ASISTENCIA)],
            'asistencias.*.observaciones' => ['nullable', 'string', 'max:200'],
        ], [
            'fecha.required' => 'Selecciona la fecha de la asistencia.',
            'fecha.before_or_equal' => 'No se puede registrar asistencia en una fecha futura.',
            'asistencias.required' => 'No se recibieron registros de asistencia.',
            'asistencias.*.estatus.in' => 'El estatus de asistencia no es válido.',
        ])->validate();

        $registros = $request->input('asistencias', []);
        foreach ($registros as $registro) {
            abort_unless($alumnos->has((int) ($registro['id_alumno'] ?? 0)), 422, 'El alumno no pertenece al grupo de esta clase.');
        }

        $parcial = $this->parcialParaFecha($clase, (string) $request->input('fecha'));

        DB::transaction(function () use ($registros, $clase, $request, $parcial) {
            foreach ($registros as $registro) {
                Asistencia::updateOrCreate(
                    [
                        'id_clase' => $clase->id_clase,
                        'id_alumno' => (int) $registro['id_alumno'],
                        'fecha' => $request->input('fecha'),
                    ],
                    [
                        'parcial' => $parcial,
                        'estatus' => $registro['estatus'],
                        'observaciones' => trim((string) ($registro['observaciones'] ?? '')) ?: null,
                    ]
                );
            }
        });

        BitacoraService::registrar(
            'asistencias',
            'ALTA',
            sprintf('Asistencia registrada para la clase %d con fecha %s.', $clase->id_clase, $request->input('fecha')),
            $clase->id_clase
        );

        return back()->with('status', 'Asistencia guardada correctamente.');
    }

    public function guardarEvaluaciones(Request $request, Clase $clase)
    {
        $docente = $this->docente();
        $clase = $this->clase($docente, $clase);

        // Solo se guardan las fechas que corresponden al tipo indicado. Así,
        // una evaluación de examen no arrastra una fecha de proyecto previa.
        $filas = collect($request->input('evaluaciones', []))
            ->map(function ($fila) {
                $tipo = $fila['tipo'] ?? 'examen';

                return [
                    'parcial' => $fila['parcial'] ?? null,
                    'tipo' => $tipo,
                    'fecha_examen' => in_array($tipo, ['examen', 'ambos'], true)
                        ? ($fila['fecha_examen'] ?? null)
                        : null,
                    'fecha_entrega_proyecto' => in_array($tipo, ['proyecto', 'ambos'], true)
                        ? ($fila['fecha_entrega_proyecto'] ?? null)
                        : null,
                ];
            })
            ->values()
            ->all();
        $request->merge(['evaluaciones' => $filas]);

        $validator = Validator::make($request->all(), [
            'evaluaciones' => ['required', 'array', 'size:3'],
            'evaluaciones.*.parcial' => ['required', 'integer', Rule::in([1, 2, 3])],
            'evaluaciones.*.tipo' => ['required', Rule::in(['examen', 'proyecto', 'ambos'])],
            'evaluaciones.*.fecha_examen' => ['nullable', 'date'],
            'evaluaciones.*.fecha_entrega_proyecto' => ['nullable', 'date'],
        ], [
            'evaluaciones.size' => 'Deben configurarse las tres evaluaciones.',
            'evaluaciones.*.tipo.in' => 'El tipo de evaluación no es válido.',
        ]);

        $validator->after(function ($validator) use ($filas) {
            foreach ($filas as $indice => $fila) {
                if (in_array($fila['tipo'], ['examen', 'ambos'], true) && empty($fila['fecha_examen'])) {
                    $validator->errors()->add(
                        'evaluaciones.'.$indice.'.fecha_examen',
                        'Selecciona la fecha de examen de esta evaluación.'
                    );
                }
                if (in_array($fila['tipo'], ['proyecto', 'ambos'], true) && empty($fila['fecha_entrega_proyecto'])) {
                    $validator->errors()->add(
                        'evaluaciones.'.$indice.'.fecha_entrega_proyecto',
                        'Selecciona la fecha de entrega del proyecto de esta evaluación.'
                    );
                }
            }
        });
        $validator->validate();

        $parciales = collect($request->input('evaluaciones'))
            ->pluck('parcial')
            ->map(fn ($parcial) => (int) $parcial)
            ->all();
        abort_unless(count(array_unique($parciales)) === 3, 422, 'Cada evaluación debe tener un parcial distinto.');

        DB::transaction(function () use ($request, $clase, $docente) {
            foreach ($request->input('evaluaciones') as $fila) {
                Evaluacion::updateOrCreate(
                    [
                        'id_clase' => $clase->id_clase,
                        'parcial' => (int) $fila['parcial'],
                    ],
                    [
                        'tipo' => $fila['tipo'],
                        'fecha_examen' => $fila['fecha_examen'] ?: null,
                        'fecha_entrega_proyecto' => $fila['fecha_entrega_proyecto'] ?: null,
                    ]
                );
            }
        });

        BitacoraService::registrar(
            'evaluaciones',
            'EDICION',
            'Fechas de exámenes y entregas actualizadas para la clase '.$clase->id_clase.'.',
            $clase->id_clase
        );

        return back()->with('status', 'Fechas de evaluación guardadas correctamente.');
    }

    public function guardarCriterios(Request $request, Clase $clase, Evaluacion $evaluacion)
    {
        $docente = $this->docente();
        $clase = $this->clase($docente, $clase);
        $this->evaluacion($clase, $evaluacion);

        // Solo se consideran los criterios marcados en el banco; las filas sin
        // seleccionar se ignoran aunque el navegador envíe sus campos.
        $filas = collect($request->input('criterios', []))
            ->filter(fn ($fila) => ! empty($fila['seleccionado']))
            ->map(fn ($fila) => [
                'nombre_rubro' => $fila['nombre_rubro'] ?? $fila['tipo_evidencia'] ?? '',
                'tipo_evidencia' => $fila['tipo_evidencia'] ?? '',
                'porcentaje' => $fila['porcentaje'] ?? '',
                'fecha_entrega' => $fila['fecha_entrega'] ?? '',
            ])
            ->values()
            ->all();
        $request->merge(['criterios' => $filas]);

        $validator = Validator::make($request->all(), [
            'criterios' => ['required', 'array', 'min:1'],
            'criterios.*.nombre_rubro' => ['required', 'string', 'max:60'],
            'criterios.*.tipo_evidencia' => ['required', 'string', 'max:60', Rule::in(self::BANCO_EVIDENCIAS)],
            'criterios.*.porcentaje' => ['required', 'numeric', 'min:1'],
            'criterios.*.fecha_entrega' => ['nullable', 'date'],
        ], [
            'criterios.required' => 'Selecciona al menos un criterio del banco de evaluación.',
            'criterios.min' => 'Selecciona al menos un criterio del banco de evaluación.',
            'criterios.*.porcentaje.min' => 'Cada criterio debe tener un porcentaje mínimo de 1.',
            'criterios.*.tipo_evidencia.in' => 'Selecciona una evidencia del banco institucional.',
        ]);

        $validator->after(function ($validator) use ($filas) {
            $total = collect($filas)->sum(fn ($fila) => (float) ($fila['porcentaje'] ?? 0));
            if ($total > self::PORCENTAJE_MAXIMO_EVALUACION + 0.009) {
                $validator->errors()->add('criterios', 'La suma de los porcentajes no puede ser mayor a 100.');
            }

            $rubros = collect($filas)->pluck('nombre_rubro')->map(fn ($rubro) => mb_strtolower(trim((string) $rubro)));
            if ($rubros->count() !== $rubros->unique()->count()) {
                $validator->errors()->add('criterios', 'No se puede repetir el mismo rubro dentro de una evaluación.');
            }
        });
        $validator->validate();

        DB::transaction(function () use ($request, $clase, $evaluacion) {
            $ids = $evaluacion->criterios()->pluck('id_criterio')->all();
            if ($ids !== []) {
                Calificacion::whereIn('id_criterio', $ids)->delete();
                CriterioEvaluacion::whereIn('id_criterio', $ids)->delete();
            }

            foreach ($request->input('criterios') as $fila) {
                CriterioEvaluacion::create([
                    'id_clase' => $clase->id_clase,
                    'parcial' => $evaluacion->parcial,
                    'id_evaluacion' => $evaluacion->id_evaluacion,
                    'nombre_rubro' => trim($fila['nombre_rubro']),
                    'tipo_evidencia' => $fila['tipo_evidencia'],
                    'porcentaje' => (float) $fila['porcentaje'],
                    'fecha_entrega' => $fila['fecha_entrega'] ?: null,
                ]);
            }
        });

        BitacoraService::registrar(
            'criterios_evaluacion',
            'EDICION',
            'Criterios de la evaluación '.$evaluacion->parcial.' actualizados para la clase '.$clase->id_clase.'.',
            $clase->id_clase
        );

        return back()->with('status', 'Criterios de evaluación guardados correctamente.');
    }

    public function guardarCalificaciones(Request $request, Clase $clase, Evaluacion $evaluacion)
    {
        $docente = $this->docente();
        $clase = $this->clase($docente, $clase);
        $this->evaluacion($clase, $evaluacion);

        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get();
        abort_unless($criterios->isNotEmpty(), 422, 'Primero captura los criterios de esta evaluación.');
        abort_if(
            (float) $criterios->sum('porcentaje') > self::PORCENTAJE_MAXIMO_EVALUACION + 0.009,
            422,
            'La suma de los porcentajes de la evaluación no puede ser mayor a 100.'
        );

        $alumnos = $clase->alumnosActivos();
        $reglas = [
            'calificaciones' => ['required', 'array', 'min:1'],
        ];
        foreach ($alumnos as $alumno) {
            foreach ($criterios as $criterio) {
                $reglas['calificaciones.'.$alumno->id_alumno.'.'.$criterio->id_criterio] = ['required', 'numeric', 'min:0', 'max:100'];
            }
        }

        Validator::make($request->all(), $reglas, [
            'calificaciones.required' => 'Captura las calificaciones de la evaluación.',
            'calificaciones.*.*.min' => 'El porcentaje obtenido no puede ser menor a 0.',
            'calificaciones.*.*.max' => 'El porcentaje obtenido no puede ser mayor a 100.',
        ])->validate();

        $idsAlumnos = $alumnos->pluck('id_alumno');
        foreach (array_keys($request->input('calificaciones', [])) as $idAlumno) {
            abort_unless($idsAlumnos->contains((int) $idAlumno), 422, 'El alumno no pertenece al grupo de esta clase.');
        }

        $ponderacionTotal = CalificacionService::ponderacionTotal($criterios);

        DB::transaction(function () use ($request, $criterios, $ponderacionTotal) {
            foreach ($request->input('calificaciones') as $idAlumno => $filas) {
                foreach ($criterios as $criterio) {
                    $porcentaje = (float) $filas[$criterio->id_criterio];

                    Calificacion::updateOrCreate(
                        [
                            'id_criterio' => $criterio->id_criterio,
                            'id_alumno' => (int) $idAlumno,
                        ],
                        [
                            'porcentaje_obtenido' => $porcentaje,
                            'puntaje_obtenido' => CalificacionService::aportacionCriterio(
                                CalificacionService::notaCriterio($porcentaje),
                                (float) $criterio->porcentaje,
                                $ponderacionTotal
                            ),
                        ]
                    );
                }
            }
        });

        BitacoraService::registrar(
            'calificaciones',
            'EDICION',
            'Calificaciones de la evaluación '.$evaluacion->parcial.' capturadas para la clase '.$clase->id_clase.'.',
            $clase->id_clase
        );

        return back()->with('status', 'Calificaciones guardadas correctamente.');
    }

    public function exportaciones()
    {
        $docente = $this->docente();

        $exportaciones = $docente->reincoExportaciones()
            ->with(['clase.materia', 'clase.grupo'])
            ->orderByDesc('id_exportacion')
            ->get();

        return view('docente.reinco.index', [
            'titulo' => 'Formatos REINCO',
            'subtitulo' => 'Formatos de control escolar generados desde tus evaluaciones.',
            'icono' => 'fa-file-excel',
            'etiqueta' => 'Docente',
            'exportaciones' => $exportaciones,
        ]);
    }

    public function generarReinco(Request $request, Clase $clase, Evaluacion $evaluacion)
    {
        $docente = $this->docente();
        $clase = $this->clase($docente, $clase);
        $this->evaluacion($clase, $evaluacion);

        Validator::make($request->all(), [
            'destino' => ['required', Rule::in(['descargar', 'guardar'])],
            'estatus' => ['sometimes', 'required', Rule::in(self::ESTATUS_REINCO)],
            'formato_calificacion' => ['sometimes', 'required', Rule::in(self::FORMATO_CALIFICACION)],
            'turno' => ['sometimes', 'nullable', 'string', 'max:20'],
            'periodo_escolar' => ['sometimes', 'nullable', 'string', 'max:16'],
            'cct' => ['sometimes', 'nullable', 'string', 'max:16'],
            'plantel' => ['sometimes', 'nullable', 'string', 'max:150'],
        ], [
            'destino.in' => 'Selecciona un destino válido para el formato REINCO.',
            'estatus.in' => 'Selecciona un estatus válido para el formato REINCO.',
            'formato_calificacion.in' => 'Selecciona un formato de calificación válido.',
        ])->validate();

        $opciones = array_filter(
            $request->only(['estatus', 'formato_calificacion', 'turno', 'periodo_escolar', 'cct', 'plantel']),
            fn ($valor) => $valor !== null && $valor !== ''
        );

        $nombre = $this->nombreArchivo($clase, $evaluacion);
        $temporal = tempnam(sys_get_temp_dir(), 'reinco_');
        abort_if($temporal === false, 500, 'No se pudo preparar el archivo REINCO.');
        $contenido = null;

        try {
            app(\App\Services\ReincoService::class)->generar($clase, $evaluacion, $temporal, $opciones);
            $contenido = (string) file_get_contents($temporal);

            if ($request->input('destino') !== 'descargar') {
                $ruta = 'reinco/'.now()->year.'/'.$nombre;
                Storage::disk('local')->put($ruta, $contenido, 'private');

                $configuracion = config('reinco');
                ReincoExportacion::create([
                    'id_docente' => $docente->id_docente,
                    'id_clase' => $clase->id_clase,
                    'id_evaluacion' => $evaluacion->id_evaluacion,
                    'parcial' => $evaluacion->parcial,
                    'estatus' => $opciones['estatus'] ?? ($evaluacion->estatus ?: $configuracion['estatus']),
                    'formato_calificacion' => $opciones['formato_calificacion'] ?? ($evaluacion->formato_calificacion ?: $configuracion['formato_calificacion']),
                    'turno' => $opciones['turno'] ?? $configuracion['turno'],
                    'periodo_escolar' => $opciones['periodo_escolar'] ?? null,
                    'cct' => $opciones['cct'] ?? $configuracion['cct'],
                    'plantel' => $opciones['plantel'] ?? $configuracion['plantel'],
                    'hojas' => 5,
                    'titulo' => 'REINCO '.$evaluacion->parcial.' · '.$clase->materia->nombre.' · '.$clase->grupo->etiqueta(),
                    'archivo_ruta' => $ruta,
                    'tamano_bytes' => strlen($contenido),
                ]);

                BitacoraService::registrar(
                    'reinco_exportaciones',
                    'ALTA',
                    'Formato REINCO generado y guardado: '.$ruta,
                    $clase->id_clase
                );
            }
        } finally {
            if (is_file($temporal)) {
                @unlink($temporal);
            }
        }

        if ($request->input('destino') === 'descargar') {
            return response()->streamDownload(function () use ($contenido) {
                echo $contenido;
            }, $nombre, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return back()->with('status', 'Formato REINCO guardado en el gestor de archivos.');
    }

    public function descargarReinco(ReincoExportacion $exportacion)
    {
        $docente = $this->docente();
        abort_unless($exportacion->id_docente === $docente->id_docente, 404);
        abort_unless(Storage::disk('local')->fileExists($exportacion->archivo_ruta), 404);

        return Storage::disk('local')->download(
            $exportacion->archivo_ruta,
            basename($exportacion->archivo_ruta)
        );
    }

    /**
     * Asocia una fecha de asistencia al parcial cuya evaluación ya ocurrió o
     * ocurre en esa fecha. Si aún no hay fechas capturadas, queda sin parcial
     * y se reporta en el concentrado general.
     */
    private function parcialParaFecha(Clase $clase, string $fecha): ?int
    {
        $evaluaciones = $clase->evaluaciones()->orderBy('parcial')->get();
        foreach ($evaluaciones as $evaluacion) {
            $limite = $evaluacion->fecha_examen ?? $evaluacion->fecha_entrega_proyecto;
            if ($limite && $fecha <= $limite->toDateString()) {
                return (int) $evaluacion->parcial;
            }
        }

        return null;
    }

    private function nombreArchivo(Clase $clase, Evaluacion $evaluacion): string
    {
        $materia = \Illuminate\Support\Str::of($clase->materia->clave)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9_-]+/', '_')
            ->toString();
        $grupo = \Illuminate\Support\Str::of($clase->grupo->etiqueta())
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9_-]+/', '_')
            ->toString();

        return sprintf(
            'REINCO_%s_%s_P%d_%s.xlsx',
            $materia,
            $grupo,
            $evaluacion->parcial,
            now()->format('Ymd_His')
        );
    }
}
