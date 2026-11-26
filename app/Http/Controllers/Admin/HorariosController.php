<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Docente;
use App\Models\EstadoAlumno;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\Salon;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HorariosController extends Controller
{
    private const DIAS = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

    private const BLOQUES = [
        ['inicio' => '15:00:00', 'fin' => '15:50:00', 'tipo' => 'clase'],
        ['inicio' => '15:50:00', 'fin' => '16:40:00', 'tipo' => 'clase'],
        ['inicio' => '16:40:00', 'fin' => '17:30:00', 'tipo' => 'clase'],
        ['inicio' => '17:30:00', 'fin' => '17:50:00', 'tipo' => 'receso', 'etiqueta' => 'Receso 20 minutos'],
        ['inicio' => '17:50:00', 'fin' => '18:40:00', 'tipo' => 'clase'],
        ['inicio' => '18:40:00', 'fin' => '19:30:00', 'tipo' => 'clase'],
        ['inicio' => '19:30:00', 'fin' => '19:40:00', 'tipo' => 'receso', 'etiqueta' => 'Receso 10 minutos'],
        ['inicio' => '19:40:00', 'fin' => '20:30:00', 'tipo' => 'clase'],
        ['inicio' => '20:30:00', 'fin' => '21:20:00', 'tipo' => 'clase'],
    ];

    public function index(Request $request)
    {
        $filtros = $request->only(['id_plan', 'id_grupo']);

        $planes = PlanEstudio::orderBy('clave')->pluck('nombre', 'id_plan');

        $grupos = Grupo::with(['semestre', 'area'])
            ->when($filtros['id_plan'] ?? null, fn ($query, $id) => $query->where('id_plan', $id))
            ->orderBy('id_semestre')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn (Grupo $grupo) => [
                $grupo->id_grupo => $grupo->etiqueta(),
            ]);

        $grupo = null;
        if (! empty($filtros['id_grupo'])) {
            $grupo = Grupo::with(['plan', 'semestre', 'area'])->find($filtros['id_grupo']);
        }

        $materias = collect();
        if ($grupo) {
            $materias = $this->materiasDisponibles($grupo)
                ->get(['id_materia', 'clave', 'nombre', 'horas_semana', 'es_tronco_comun']);
        }

        $docentes = Docente::where('is_active', true)
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get([
                'id_docente', 'nombre', 'apellido_paterno', 'apellido_materno',
                'horas_semanales', 'licenciatura', 'especialidad', 'maestria', 'doctorado',
            ])
            ->map(fn (Docente $docente) => [
                'id' => $docente->id_docente,
                'nombre' => $docente->nombreCompleto(),
                'formacion' => $this->formacionDocente($docente),
                'horas_semanales' => $docente->horas_semanales,
                'carga' => $this->cargaDocente($docente->id_docente),
            ])
            ->values();

        $salones = Salon::where('is_active', true)
            ->orderBy('clave')
            ->get(['id_salon', 'clave', 'nombre', 'capacidad'])
            ->map(fn (Salon $salon) => [
                'id' => $salon->id_salon,
                'nombre' => $salon->clave.' · '.$salon->nombre,
                'referencia' => 'Capacidad: '.$salon->capacidad,
            ])
            ->values();

        $asignaciones = $grupo ? $this->asignacionesDeGrupo($grupo) : [];
        $cantidades = collect($asignaciones)->countBy(fn ($asignacion) => $asignacion['id_materia'])->all();
        $alumnosGrupo = $grupo ? $this->alumnosDelGrupo($grupo) : 0;

        [$ocupacionesDocentes, $ocupacionesSalones] = $this->ocupacionesOtrosGrupos($grupo);

        return view('admin.horarios.index', [
            'titulo' => 'Horarios',
            'subtitulo' => 'Elige materia, docente y salón; luego haz clic en la celda correspondiente.',
            'icono' => 'fa-calendar-day',
            'etiqueta' => 'Administrador',
            'planes' => $planes,
            'grupos' => $grupos,
            'grupo' => $grupo,
            'materias' => $materias,
            'docentes' => $docentes,
            'salones' => $salones,
            'asignaciones' => $asignaciones,
            'cantidades' => $cantidades,
            'alumnos_grupo' => $alumnosGrupo,
            'ocupaciones_docentes' => $ocupacionesDocentes,
            'ocupaciones_salones' => $ocupacionesSalones,
            'dias' => self::DIAS,
            'bloques' => self::BLOQUES,
            'filtros' => $filtros,
        ]);
    }

    public function guardar(Request $request)
    {
        $grupo = Grupo::with('semestre')->find($request->input('id_grupo'));
        $bloquesClase = array_values(array_filter(self::BLOQUES, fn ($bloque) => $bloque['tipo'] === 'clase'));
        $inicios = array_column($bloquesClase, 'inicio');
        $fines = array_column($bloquesClase, 'fin');

        $validator = Validator::make($request->all(), [
            'id_plan' => ['required', 'exists:planes_estudio,id_plan'],
            'id_grupo' => ['required', 'exists:grupos,id_grupo'],
            'celdas' => ['nullable', 'array'],
            'celdas.*.dia' => ['required', Rule::in(self::DIAS)],
            'celdas.*.inicio' => ['required', Rule::in($inicios)],
            'celdas.*.fin' => ['required', Rule::in($fines)],
            'celdas.*.id_materia' => ['required', 'exists:materias,id_materia'],
            'celdas.*.id_docente' => ['required', 'exists:docentes,id_docente'],
            'celdas.*.id_salon' => [
                'required',
                Rule::exists('salones', 'id_salon')->where('is_active', true),
            ],
        ], [
            'id_plan.required' => 'Selecciona el plan de estudios.',
            'id_grupo.required' => 'Selecciona el grupo.',
            'celdas.*.dia.required' => 'Cada celda necesita un día.',
            'celdas.*.inicio.required' => 'Cada celda necesita una hora de inicio.',
            'celdas.*.fin.required' => 'Cada celda necesita una hora de fin.',
            'celdas.*.id_materia.required' => 'Selecciona una materia para cada celda.',
            'celdas.*.id_docente.required' => 'Selecciona un docente para cada celda.',
            'celdas.*.id_salon.required' => 'Selecciona un salón para cada celda.',
            'celdas.*.id_salon.exists' => 'El salón seleccionado no está activo.',
        ]);

        $validator->after(function ($validator) use ($request, $grupo, $inicios, $fines) {
            if (! $grupo) {
                return;
            }

            $alumnosGrupo = $this->alumnosDelGrupo($grupo);

            if ((int) $request->input('id_plan') !== $grupo->id_plan) {
                $validator->errors()->add('id_plan', 'El plan seleccionado no corresponde al grupo.');
            }

            $celdas = is_array($request->input('celdas')) ? $request->input('celdas') : [];

            $claves = collect($celdas)->map(fn ($celda) => implode('|', [
                $celda['dia'] ?? '',
                $celda['inicio'] ?? '',
                $celda['fin'] ?? '',
            ]));

            if ($claves->count() !== $claves->unique()->count()) {
                $validator->errors()->add('celdas', 'Hay celdas repetidas en el horario.');
            }

            $clavesDocentesEnviadas = [];
            $clavesSalonesEnviadas = [];

            foreach ($celdas as $index => $celda) {
                $inicio = $celda['inicio'] ?? null;
                $fin = $celda['fin'] ?? null;

                if ($inicio && $fin && array_search($inicio, $inicios, true) !== array_search($fin, $fines, true)) {
                    $validator->errors()->add("celdas.{$index}.fin", 'La hora de fin no corresponde al bloque.');
                }

                $idMateria = $celda['id_materia'] ?? null;
                if ($idMateria && ! $this->materiasDisponibles($grupo)->whereKey($idMateria)->exists()) {
                    $validator->errors()->add(
                        "celdas.{$index}.id_materia",
                        'La materia no pertenece al plan, semestre y área del grupo.'
                    );
                }

                $idDocente = $celda['id_docente'] ?? null;
                if ($idDocente && ! Docente::whereKey($idDocente)->where('is_active', true)->exists()) {
                    $validator->errors()->add("celdas.{$index}.id_docente", 'El docente seleccionado no está activo.');
                }

                if ($idDocente && ($celda['dia'] ?? null) && $inicio) {
                    $claveDocente = $idDocente.'@'.$claves[$index];
                    if (isset($clavesDocentesEnviadas[$claveDocente])) {
                        $validator->errors()->add(
                            "celdas.{$index}.id_docente",
                            'El mismo docente no puede impartir dos clases en ese día y hora.'
                        );
                    }
                    $clavesDocentesEnviadas[$claveDocente] = true;

                    $solapado = Horario::where('dia_semana', $celda['dia'])
                        ->where('hora_inicio', $inicio)
                        ->whereHas('clase', function ($query) use ($idDocente, $grupo) {
                            $query->where('id_docente', $idDocente)
                                ->where('id_grupo', '!=', $grupo->id_grupo)
                                ->where('is_active', true);
                        })
                        ->exists();

                    if ($solapado) {
                        $validator->errors()->add(
                            "celdas.{$index}.id_docente",
                            'El docente ya tiene clase asignada en ese día y hora en otro horario.'
                        );
                    }
                }

                $idSalon = $celda['id_salon'] ?? null;
                if ($idSalon) {
                    $salon = Salon::find($idSalon);
                    if ($salon && $salon->capacidad < $alumnosGrupo) {
                        $validator->errors()->add(
                            "celdas.{$index}.id_salon",
                            "El salón solo tiene capacidad para {$salon->capacidad} alumnos y el grupo tiene "
                                ."{$alumnosGrupo} alumnos activos."
                        );
                    }

                    if ($salon && ($celda['dia'] ?? null) && $inicio) {
                        $claveSalon = $idSalon.'@'.$claves[$index];
                        if (isset($clavesSalonesEnviadas[$claveSalon])) {
                            $validator->errors()->add(
                                "celdas.{$index}.id_salon",
                                'El mismo salón no puede asignarse a dos clases en ese día y hora.'
                            );
                        }
                        $clavesSalonesEnviadas[$claveSalon] = true;

                        $ocupado = Horario::where('dia_semana', $celda['dia'])
                            ->where('hora_inicio', $inicio)
                            ->where('id_salon', $idSalon)
                            ->whereHas('clase', function ($query) use ($grupo) {
                                $query->where('id_grupo', '!=', $grupo->id_grupo)
                                    ->where('is_active', true);
                            })
                            ->exists();

                        if ($ocupado) {
                            $validator->errors()->add(
                                "celdas.{$index}.id_salon",
                                'El salón ya está ocupado en ese día y hora en otro horario.'
                            );
                        }
                    }
                }
            }

            $conteoDocentes = collect($celdas)->countBy(fn ($celda) => $celda['id_docente'] ?? null);
            foreach ($conteoDocentes as $idDocente => $total) {
                if (! $idDocente) {
                    continue;
                }

                $docente = Docente::find($idDocente);
                if (! $docente) {
                    continue;
                }

                $externas = Horario::whereHas('clase', function ($query) use ($idDocente, $grupo) {
                    $query->where('id_docente', $idDocente)
                        ->where('id_grupo', '!=', $grupo->id_grupo)
                        ->where('is_active', true);
                })->count();

                if ($externas + $total > $docente->horas_semanales) {
                    $validator->errors()->add(
                        'celdas',
                        $docente->nombreCompleto().' supera su tope de '.$docente->horas_semanales
                            .' horas semanales ('.$total.' en este horario + '.$externas.' en otros grupos).'
                    );
                }
            }

            $conteo = collect($celdas)->countBy(fn ($celda) => $celda['id_materia'] ?? null);
            foreach ($conteo as $idMateria => $total) {
                if (! $idMateria) {
                    continue;
                }

                $materia = Materia::find($idMateria);
                if ($materia && $total > $materia->horas_semana) {
                    $validator->errors()->add(
                        'celdas',
                        "{$materia->nombre} supera el límite de {$materia->horas_semana} horas semanales ({$total} asignadas)."
                    );
                }
            }

            // Una materia solo puede tener un docente en este horario: todas sus
            // horas deben impartirlas el mismo profesor.
            $docentesPorMateria = collect($celdas)
                ->filter(fn ($celda) => ! empty($celda['id_materia']) && ! empty($celda['id_docente']))
                ->groupBy(fn ($celda) => (int) $celda['id_materia'])
                ->map(fn ($filas) => $filas->pluck('id_docente')->map(fn ($id) => (int) $id)->unique());

            foreach ($docentesPorMateria as $idMateria => $idsDocente) {
                if ($idsDocente->count() <= 1) {
                    continue;
                }

                $materia = Materia::find($idMateria);
                $validator->errors()->add(
                    'celdas',
                    'La materia '.($materia?->nombre ?? 'seleccionada').' solo puede tener un docente en este horario.'
                );
            }
        });

        $validator->validate();

        $celdas = is_array($request->input('celdas')) ? $request->input('celdas') : [];

        DB::transaction(function () use ($celdas, $grupo) {
            $clasesAnteriores = Clase::where('id_grupo', $grupo->id_grupo)->get();

            if ($clasesAnteriores->isNotEmpty()) {
                $ids = $clasesAnteriores->pluck('id_clase')->all();
                Horario::whereIn('id_clase', $ids)->delete();

                $clasesAnteriores->each(function (Clase $clase) {
                    if (DB::table('criterios_evaluacion')->where('id_clase', $clase->id_clase)->exists()) {
                        $clase->update(['is_active' => false]);
                    } else {
                        $clase->delete();
                    }
                });
            }

            foreach ($celdas as $celda) {
                $clase = Clase::firstOrCreate([
                    'id_materia' => (int) $celda['id_materia'],
                    'id_docente' => (int) $celda['id_docente'],
                    'id_grupo' => $grupo->id_grupo,
                ], ['is_active' => true]);

                if (! $clase->is_active) {
                    $clase->update(['is_active' => true]);
                }

                Horario::create([
                    'id_clase' => $clase->id_clase,
                    'dia_semana' => $celda['dia'],
                    'hora_inicio' => $celda['inicio'],
                    'hora_fin' => $celda['fin'],
                    'id_salon' => (int) $celda['id_salon'],
                ]);
            }

            BitacoraService::registrar(
                'horarios',
                'EDICION',
                'Horario guardado para el grupo '.$grupo->nombre.' ('.count($celdas).' celdas).',
                $grupo->id_grupo
            );
        });

        return redirect()->route('admin.horarios.index', [
            'id_plan' => $grupo->id_plan,
            'id_grupo' => $grupo->id_grupo,
        ])->with('status', 'Horario guardado correctamente.');
    }

    private function materiasDisponibles(Grupo $grupo)
    {
        $consulta = Materia::where('id_plan', $grupo->id_plan)
            ->where('id_semestre', $grupo->id_semestre)
            ->where('is_active', true);

        $numeroSemestre = $grupo->semestre?->numero_semestre;
        if (in_array($numeroSemestre, [5, 6], true)) {
            if ($grupo->id_area_propedeutica) {
                $consulta->where(function ($query) use ($grupo) {
                    $query->where('es_tronco_comun', true)
                        ->orWhere('id_area_propedeutica', $grupo->id_area_propedeutica);
                });
            } else {
                $consulta->where('es_tronco_comun', true);
            }
        }

        return $consulta->orderBy('nombre');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function asignacionesDeGrupo(Grupo $grupo): array
    {
        return Horario::with(['clase.materia', 'clase.docente', 'salon'])
            ->whereHas('clase', function ($query) use ($grupo) {
                $query->where('id_grupo', $grupo->id_grupo)
                    ->where('is_active', true);
            })
            ->get()
            ->mapWithKeys(function (Horario $horario) {
                $clave = implode('|', [
                    $horario->dia_semana,
                    substr((string) $horario->hora_inicio, 0, 8),
                    substr((string) $horario->hora_fin, 0, 8),
                ]);

                return [$clave => [
                    'id_materia' => $horario->clase->id_materia,
                    'id_docente' => $horario->clase->id_docente,
                    'id_salon' => $horario->id_salon,
                    'materia' => $horario->clase->materia?->nombre ?? '—',
                    'docente' => $horario->clase->docente?->nombreCompleto() ?? '—',
                    'salon' => $horario->salon?->clave ?? '—',
                ]];
            })
            ->all();
    }

    private function formacionDocente(Docente $docente): string
    {
        $partes = [
            'Licenciatura: '.($docente->licenciatura ?: 'No registrada'),
            'Especialidad: '.($docente->especialidad ?: 'No reporta'),
            'Maestría: '.($docente->maestria ?: 'No reporta'),
            'Doctorado: '.($docente->doctorado ?: 'No reporta'),
            'Tope: '.$docente->horas_semanales.' h',
        ];

        return implode(' · ', $partes);
    }

    /**
     * Alumnos efectivamente asignados al grupo. Las bajas definitivas y los graduados no cuentan.
     */
    private function alumnosDelGrupo(Grupo $grupo): int
    {
        return Alumno::where('id_grupo', $grupo->id_grupo)
            ->whereNotIn('id_estado_alumno', [EstadoAlumno::BAJA_DEFINITIVA, EstadoAlumno::GRADUADO])
            ->count();
    }

    private function cargaDocente(int $idDocente): int
    {
        return Horario::whereHas('clase', function ($query) use ($idDocente) {
            $query->where('id_docente', $idDocente)
                ->where('is_active', true);
        })->count();
    }

    /**
     * Celdas ya ocupadas por otros grupos, agrupadas por docente y por salón.
     * Se usan para bloquear en la interfaz los horarios que provocarían un empalme.
     *
     * @return array{0: array<int, array<int, string>>, 1: array<int, array<int, string>>}
     */
    private function ocupacionesOtrosGrupos(?Grupo $grupo): array
    {
        if (! $grupo) {
            return [[], []];
        }

        $docentes = [];
        $salones = [];

        Horario::with('clase')
            ->whereHas('clase', function ($query) use ($grupo) {
                $query->where('id_grupo', '!=', $grupo->id_grupo)
                    ->where('is_active', true);
            })
            ->get(['id_clase', 'dia_semana', 'hora_inicio', 'hora_fin', 'id_salon'])
            ->each(function (Horario $horario) use (&$docentes, &$salones) {
                $clave = implode('|', [
                    $horario->dia_semana,
                    substr((string) $horario->hora_inicio, 0, 8),
                    substr((string) $horario->hora_fin, 0, 8),
                ]);

                $idDocente = (int) ($horario->clase?->id_docente ?? 0);
                if ($idDocente) {
                    $docentes[$idDocente][] = $clave;
                }

                $idSalon = (int) $horario->id_salon;
                if ($idSalon) {
                    $salones[$idSalon][] = $clave;
                }
            });

        return [$docentes, $salones];
    }
}
