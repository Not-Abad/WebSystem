<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaPropedeutica;
use App\Models\Grupo;
use App\Models\PlanEstudio;
use App\Models\Semestre;
use App\Services\BitacoraService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GruposController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $grupos = Grupo::with(['plan', 'area', 'semestre'])
            ->withCount(['alumnos', 'clases'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhereHas('plan', fn ($q) => $q->where('clave', 'like', $like)->orWhere('nombre', 'like', $like))
                        ->orWhereHas('area', fn ($q) => $q->where('nombre', 'like', $like))
                        ->orWhereHas('semestre', fn ($q) => $q->where('nombre', 'like', $like));
                });
            })
            ->when($request->input('id_plan'), fn ($query, $id) => $query->where('id_plan', $id))
            ->when($request->input('id_semestre'), fn ($query, $id) => $query->where('id_semestre', $id))
            ->when($request->input('id_area_propedeutica'), fn ($query, $id) => $query->where('id_area_propedeutica', $id))
            ->orderBy('id_semestre')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('admin.grupos.index', array_merge([
            'titulo' => 'Grupos y clases',
            'subtitulo' => 'Crea grupos por plan y semestre; desde 5º semestre se organizan por área propedéutica.',
            'icono' => 'fa-layer-group',
            'etiqueta' => 'Administrador',
            'grupos' => $grupos,
            'busqueda' => $busqueda,
            'filtros' => $request->only(['q', 'id_plan', 'id_semestre', 'id_area_propedeutica']),
        ], $this->catalogos()));
    }

    public function create()
    {
        return view('admin.grupos.form', array_merge([
            'titulo' => 'Nuevo grupo',
            'subtitulo' => 'El plan define las materias disponibles; el área se requiere desde 5º semestre.',
            'icono' => 'fa-plus',
            'etiqueta' => 'Grupos y clases',
            'grupo' => null,
        ], $this->catalogos()));
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->reglas($request), $this->mensajes())->validate();

        $grupo = Grupo::create($this->datos($request));

        BitacoraService::registrar(
            'grupos',
            'ALTA',
            "Alta de grupo: {$grupo->etiqueta()} · plan {$grupo->plan?->clave}.",
            $grupo->id_grupo
        );

        return redirect()->route('admin.grupos.index')
            ->with('status', 'Grupo creado correctamente. Ya puedes asignarle horario.');
    }

    public function edit(Grupo $grupo)
    {
        return view('admin.grupos.form', array_merge([
            'titulo' => 'Editar grupo',
            'subtitulo' => "Actualización del grupo {$grupo->nombre}.",
            'icono' => 'fa-pen',
            'etiqueta' => 'Grupos y clases',
            'grupo' => $grupo,
        ], $this->catalogos()));
    }

    public function update(Request $request, Grupo $grupo)
    {
        Validator::make($request->all(), $this->reglas($request, $grupo), $this->mensajes())->validate();

        $grupo->update($this->datos($request));

        BitacoraService::registrar(
            'grupos',
            'EDICION',
            "Edición de grupo: {$grupo->etiqueta()}.",
            $grupo->id_grupo
        );

        return redirect()->route('admin.grupos.index')
            ->with('status', 'Grupo actualizado correctamente.');
    }

    public function destroy(Grupo $grupo)
    {
        if ($grupo->alumnos()->exists() || $grupo->clases()->exists()) {
            return back()->withErrors([
                'grupo' => 'No se puede eliminar un grupo con alumnos o clases asignadas.',
            ]);
        }

        $descripcion = "Eliminación de grupo: {$grupo->etiqueta()}.";
        $id = $grupo->id_grupo;
        $grupo->delete();

        BitacoraService::registrar('grupos', 'BAJA', $descripcion, $id);

        return redirect()->route('admin.grupos.index')
            ->with('status', 'Grupo eliminado correctamente.');
    }

    /**
     * @return array<string, int|string|null>
     */
    private function datos(Request $request): array
    {
        $numeroSemestre = $this->numeroSemestre($request);

        return [
            'id_plan' => (int) $request->input('id_plan'),
            'id_semestre' => (int) $request->input('id_semestre'),
            'id_area_propedeutica' => in_array($numeroSemestre, [5, 6], true)
                ? ($request->input('id_area_propedeutica') ?: null)
                : null,
            'nombre' => strtoupper(trim((string) $request->input('nombre'))),
            'cupo_maximo' => (int) $request->input('cupo_maximo'),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglas(Request $request, ?Grupo $grupo = null): array
    {
        $numeroSemestre = $this->numeroSemestre($request);
        $requiereArea = in_array($numeroSemestre, [5, 6], true);
        $areaEfectiva = $requiereArea ? ($request->input('id_area_propedeutica') ?: null) : null;

        return [
            'id_plan' => ['required', 'exists:planes_estudio,id_plan'],
            'id_semestre' => ['required', 'exists:semestres,id_semestre'],
            'id_area_propedeutica' => [
                'nullable',
                Rule::requiredIf($requiereArea),
                'exists:areas_propedeuticas,id_area_propedeutica',
            ],
            'nombre' => [
                'required', 'string', 'max:15', 'regex:/^[A-Za-z0-9\-]+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($request, $grupo, $areaEfectiva) {
                    $query = Grupo::where('id_plan', $request->input('id_plan'))
                        ->where('id_semestre', $request->input('id_semestre'))
                        ->where('nombre', strtoupper(trim((string) $value)));

                    if ($areaEfectiva) {
                        $query->where('id_area_propedeutica', $areaEfectiva);
                    } else {
                        $query->whereNull('id_area_propedeutica');
                    }

                    if ($grupo) {
                        $query->where('id_grupo', '!=', $grupo->id_grupo);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un grupo con ese nombre para el plan, semestre y área seleccionados.');
                    }
                },
            ],
            'cupo_maximo' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    private function numeroSemestre(Request $request): ?int
    {
        if (! is_numeric($request->input('id_semestre'))) {
            return null;
        }

        return Semestre::whereKey((int) $request->input('id_semestre'))->value('numero_semestre');
    }

    /**
     * @return array<string, \Illuminate\Support\Collection|array<int, int>>
     */
    private function catalogos(): array
    {
        return [
            'planes' => PlanEstudio::orderBy('clave')->pluck('nombre', 'id_plan'),
            'semestres' => Semestre::orderBy('numero_semestre')->pluck('nombre', 'id_semestre'),
            'areas' => AreaPropedeutica::where('is_active', true)->orderBy('nombre')->pluck('nombre', 'id_area_propedeutica'),
            'semestresArea' => Semestre::whereIn('numero_semestre', [5, 6])->pluck('id_semestre')->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mensajes(): array
    {
        return [
            'id_plan.required' => 'Selecciona el plan de estudios.',
            'id_semestre.required' => 'Selecciona el semestre.',
            'id_area_propedeutica.required' => 'Selecciona el área propedéutica desde 5to semestre.',
            'nombre.required' => 'Ingresa el nombre del grupo.',
            'nombre.regex' => 'El nombre solo puede contener letras, números y guiones.',
            'cupo_maximo.required' => 'Ingresa el cupo máximo.',
            'cupo_maximo.min' => 'El cupo debe ser al menos 1.',
        ];
    }
}
