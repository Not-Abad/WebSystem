<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaPropedeutica;
use App\Models\Capacitacion;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\Semestre;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CatalogosController extends Controller
{
    private const TIPOS_MATERIA = ['Basica', 'Paraescolar', 'Capacitacion', 'Propedeutica'];

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $planes = PlanEstudio::withCount(['materias', 'grupos'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('clave', 'like', $like)
                        ->orWhere('nombre', 'like', $like);
                });
            })
            ->orderByDesc('id_plan')
            ->get();

        return view('admin.catalogos.index', [
            'titulo' => 'Catálogos académicos',
            'subtitulo' => 'Altas de planes de estudio y sus materias por semestre.',
            'icono' => 'fa-book',
            'etiqueta' => 'Administrador',
            'planes' => $planes,
            'busqueda' => $busqueda,
            'plan' => null,
        ]);
    }

    public function create()
    {
        return view('admin.catalogos.plan-form', [
            'titulo' => 'Nuevo plan de estudios',
            'subtitulo' => 'Se registrará un plan que podrá vincularse a grupos y materias.',
            'icono' => 'fa-file-circle-plus',
            'etiqueta' => 'Catálogos',
            'plan' => null,
        ]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->reglasPlan(), $this->mensajes())->validate();

        DB::transaction(function () use ($request) {
            $plan = PlanEstudio::create([
                'clave' => strtoupper(trim((string) $request->input('clave'))),
                'nombre' => trim((string) $request->input('nombre')),
                'anio_inicio' => (int) $request->input('anio_inicio'),
                'is_active' => true,
            ]);

            BitacoraService::registrar(
                'planes_estudio',
                'ALTA',
                "Alta de plan de estudios: {$plan->clave} · {$plan->nombre}.",
                $plan->id_plan
            );
        });

        return redirect()->route('admin.catalogos.index')
            ->with('status', 'Plan de estudios creado correctamente. Ahora puedes agregar sus materias.');
    }

    public function edit(PlanEstudio $plan)
    {
        return view('admin.catalogos.plan-form', [
            'titulo' => 'Editar plan de estudios',
            'subtitulo' => "Actualización del plan {$plan->clave}.",
            'icono' => 'fa-pen',
            'etiqueta' => 'Catálogos',
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, PlanEstudio $plan)
    {
        Validator::make($request->all(), $this->reglasPlan($plan), $this->mensajes())->validate();

        DB::transaction(function () use ($request, $plan) {
            $plan->update([
                'clave' => strtoupper(trim((string) $request->input('clave'))),
                'nombre' => trim((string) $request->input('nombre')),
                'anio_inicio' => (int) $request->input('anio_inicio'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar(
                'planes_estudio',
                'EDICION',
                "Edición de plan de estudios: {$plan->clave} · {$plan->nombre}.",
                $plan->id_plan
            );
        });

        return redirect()->route('admin.catalogos.index')
            ->with('status', 'Plan de estudios actualizado correctamente.');
    }

    public function destroy(PlanEstudio $plan)
    {
        $plan->update(['is_active' => false]);

        BitacoraService::registrar(
            'planes_estudio',
            'BAJA',
            "Baja lógica de plan de estudios: {$plan->clave} · {$plan->nombre}.",
            $plan->id_plan
        );

        return redirect()->route('admin.catalogos.index')
            ->with('status', 'Plan de estudios dado de baja lógica. Puedes reactivarlo desde Editar.');
    }

    public function materias(Request $request)
    {
        return $this->listadoMaterias($request, null);
    }

    public function materiasDePlan(Request $request, PlanEstudio $plan)
    {
        return $this->listadoMaterias($request, $plan);
    }

    public function createMateria(PlanEstudio $plan)
    {
        return view('admin.catalogos.materia-form', array_merge([
            'titulo' => 'Nueva materia',
            'subtitulo' => "Materia del plan {$plan->clave}.",
            'icono' => 'fa-book-bookmark',
            'etiqueta' => 'Catálogos',
            'plan' => $plan,
            'materia' => null,
        ], $this->catalogos()));
    }

    public function storeMateria(Request $request, PlanEstudio $plan)
    {
        Validator::make($request->all(), $this->reglasMateria($request, $plan), $this->mensajes())->validate();

        DB::transaction(function () use ($request, $plan) {
            $materia = $this->guardarMateria($request, $plan, new Materia());

            BitacoraService::registrar(
                'materias',
                'ALTA',
                "Alta de materia: {$materia->clave} · {$materia->nombre} (plan {$plan->clave}).",
                $materia->id_materia
            );
        });

        return redirect()->route('admin.catalogos.materias.plan', $plan)
            ->with('status', 'Materia agregada al plan de estudios.');
    }

    public function editMateria(Materia $materia)
    {
        return view('admin.catalogos.materia-form', array_merge([
            'titulo' => 'Editar materia',
            'subtitulo' => "Materia del plan {$materia->plan?->clave}.",
            'icono' => 'fa-pen',
            'etiqueta' => 'Catálogos',
            'plan' => $materia->plan,
            'materia' => $materia,
        ], $this->catalogos()));
    }

    public function updateMateria(Request $request, Materia $materia)
    {
        Validator::make($request->all(), $this->reglasMateria($request, $materia->plan, $materia), $this->mensajes())->validate();

        DB::transaction(function () use ($request, $materia) {
            $this->guardarMateria($request, $materia->plan, $materia);

            BitacoraService::registrar(
                'materias',
                'EDICION',
                "Edición de materia: {$materia->clave} · {$materia->nombre}.",
                $materia->id_materia
            );
        });

        return redirect()->route('admin.catalogos.materias.plan', $materia->plan)
            ->with('status', 'Materia actualizada correctamente.');
    }

    public function destroyMateria(Materia $materia)
    {
        $plan = $materia->plan;
        $materia->update(['is_active' => false]);

        BitacoraService::registrar(
            'materias',
            'BAJA',
            "Baja lógica de materia: {$materia->clave} · {$materia->nombre}.",
            $materia->id_materia
        );

        return redirect()->route('admin.catalogos.materias.plan', $plan)
            ->with('status', 'Materia dada de baja lógica. Puedes reactivarla desde Editar.');
    }

    private function listadoMaterias(Request $request, ?PlanEstudio $plan)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $materias = Materia::with(['plan', 'semestre', 'area'])
            ->when($plan !== null, fn ($query) => $query->where('id_plan', $plan->id_plan))
            ->when($request->input('id_plan'), fn ($query, $id) => $query->where('id_plan', $id))
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('clave', 'like', $like);
                });
            })
            ->when($request->input('id_semestre'), fn ($query, $id) => $query->where('id_semestre', $id))
            ->orderBy('id_plan')
            ->orderBy('id_semestre')
            ->orderBy('nombre')
            ->get();

        return view('admin.catalogos.materias', array_merge([
            'titulo' => $plan ? "Materias · {$plan->clave}" : 'Materias por plan',
            'subtitulo' => 'Catálogo de materias organizado por plan de estudios y semestre.',
            'icono' => 'fa-book-bookmark',
            'etiqueta' => 'Catálogos',
            'materias' => $materias,
            'plan' => $plan,
            'planes' => PlanEstudio::orderBy('clave')->pluck('nombre', 'id_plan'),
            'semestres' => Semestre::orderBy('numero_semestre')->pluck('nombre', 'id_semestre'),
            'busqueda' => $busqueda,
            'filtros' => $request->only(['q', 'id_plan', 'id_semestre']),
        ], []));
    }

    private function guardarMateria(Request $request, PlanEstudio $plan, Materia $materia): Materia
    {
        $tipo = (string) $request->input('tipo_materia');
        $esTroncoComun = $request->boolean('es_tronco_comun');
        $numeroSemestre = Semestre::whereKey((int) $request->input('id_semestre'))->value('numero_semestre');
        $requiereArea = in_array($numeroSemestre, [5, 6], true) && ! $esTroncoComun;

        $materia->fill([
            'id_plan' => $plan->id_plan,
            'id_semestre' => (int) $request->input('id_semestre'),
            'id_capacitacion' => $tipo === 'Capacitacion' ? ($request->input('id_capacitacion') ?: null) : null,
            'id_area_propedeutica' => $requiereArea ? ($request->input('id_area_propedeutica') ?: null) : null,
            'clave' => strtoupper(trim((string) $request->input('clave'))),
            'nombre' => trim((string) $request->input('nombre')),
            'tipo_materia' => $tipo,
            'es_tronco_comun' => $esTroncoComun,
            'horas_semana' => (int) $request->input('horas_semana'),
            'descripcion' => $request->input('descripcion') ?: null,
            'objetivo' => $request->input('objetivo') ?: null,
            'programa_pdf_ruta' => $request->input('programa_pdf_ruta') ?: null,
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return $materia;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglasPlan(?PlanEstudio $plan = null): array
    {
        $clave = Rule::unique('planes_estudio', 'clave');
        if ($plan) {
            $clave->ignore($plan->id_plan, 'id_plan');
        }

        return [
            'clave' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/', $clave],
            'nombre' => ['required', 'string', 'max:120'],
            'anio_inicio' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglasMateria(Request $request, PlanEstudio $plan, ?Materia $materia = null): array
    {
        $clave = Rule::unique('materias', 'clave');
        if ($materia) {
            $clave->ignore($materia->id_materia, 'id_materia');
        }

        $numeroSemestre = is_numeric($request->input('id_semestre'))
            ? Semestre::whereKey((int) $request->input('id_semestre'))->value('numero_semestre')
            : null;
        $requiereArea = in_array($numeroSemestre, [5, 6], true) && ! $request->boolean('es_tronco_comun');

        return [
            'id_semestre' => ['required', 'exists:semestres,id_semestre'],
            'clave' => ['required', 'string', 'max:25', 'regex:/^[A-Za-z0-9\-]+$/', $clave],
            'nombre' => ['required', 'string', 'max:120'],
            'tipo_materia' => ['required', Rule::in(self::TIPOS_MATERIA)],
            'es_tronco_comun' => ['boolean'],
            'horas_semana' => ['required', 'integer', 'min:1', 'max:40'],
            'descripcion' => ['nullable', 'string'],
            'objetivo' => ['nullable', 'string'],
            'programa_pdf_ruta' => ['nullable', 'string', 'max:255'],
            'id_capacitacion' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('tipo_materia') === 'Capacitacion'),
                'exists:capacitaciones,id_capacitacion',
            ],
            'id_area_propedeutica' => [
                'nullable',
                Rule::requiredIf($requiereArea),
                'exists:areas_propedeuticas,id_area_propedeutica',
            ],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, \Illuminate\Support\Collection>
     */
    private function catalogos(): array
    {
        return [
            'semestres' => Semestre::orderBy('numero_semestre')->pluck('nombre', 'id_semestre'),
            'semestresArea' => Semestre::whereIn('numero_semestre', [5, 6])->pluck('id_semestre')->all(),
            'capacitaciones' => Capacitacion::orderBy('nombre')->pluck('nombre', 'id_capacitacion'),
            'areas' => AreaPropedeutica::orderBy('nombre')->pluck('nombre', 'id_area_propedeutica'),
            'tipos' => self::TIPOS_MATERIA,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mensajes(): array
    {
        return [
            'clave.unique' => 'Esa clave ya está registrada.',
            'clave.regex' => 'La clave solo puede contener letras, números y guiones.',
            'anio_inicio.integer' => 'Ingresa un año válido.',
            'id_semestre.required' => 'Selecciona el semestre de la materia.',
            'tipo_materia.required' => 'Selecciona el tipo de materia.',
            'id_capacitacion.required' => 'Selecciona la capacitación correspondiente.',
            'id_area_propedeutica.required' => 'Desde 5to semestre selecciona el área o marca la materia como tronco común.',
            'horas_semana.required' => 'Ingresa las horas semanales.',
            'horas_semana.min' => 'La materia debe tener al menos 1 hora semanal.',
        ];
    }
}
