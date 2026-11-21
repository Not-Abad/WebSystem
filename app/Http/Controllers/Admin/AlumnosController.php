<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Closure;
use App\Models\Alumno;
use App\Models\AreaPropedeutica;
use App\Models\Capacitacion;
use App\Models\EstadoAlumno;
use App\Models\Grupo;
use App\Models\PlanEstudio;
use App\Models\Rol;
use App\Models\Semestre;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AlumnosController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $alumnos = Alumno::with(['usuario', 'tutor', 'grupo', 'estado', 'plan', 'semestre'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('curp', 'like', $like)
                        ->orWhere('correo', 'like', $like)
                        ->orWhereHas('usuario', fn ($q) => $q->where('username', 'like', $like))
                        ->orWhereHas('grupo', fn ($q) => $q->where('nombre', 'like', $like));
                });
            })
            ->when($request->input('id_estado_alumno'), fn ($query, $valor) => $query->where('id_estado_alumno', $valor))
            ->when($request->input('id_semestre'), fn ($query, $valor) => $query->where('id_semestre', $valor))
            ->orderByDesc('id_alumno')
            ->paginate(20)
            ->withQueryString();

        return view('admin.alumnos.index', array_merge([
            'titulo' => 'Alumnos',
            'subtitulo' => 'Expedientes, grupos, estatus escolar y tutores asignados.',
            'icono' => 'fa-user-graduate',
            'etiqueta' => 'Administrador',
            'alumnos' => $alumnos,
            'busqueda' => $busqueda,
            'filtros' => $request->only(['id_estado_alumno', 'id_semestre']),
        ], $this->catalogos()));
    }

    public function create()
    {
        return view('admin.alumnos.create', array_merge([
            'titulo' => 'Nuevo alumno',
            'subtitulo' => 'Se creará su cuenta de acceso con el rol Alumno.',
            'icono' => 'fa-user-plus',
            'etiqueta' => 'Administrador',
        ], $this->catalogos()));
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->rules($request), $this->mensajes())->validate();

        $camposSemestre = $this->camposPorSemestre($request);

        DB::transaction(function () use ($request, $camposSemestre) {
            $usuario = User::create([
                'id_rol' => Rol::ALUMNO,
                'username' => $request->input('username'),
                'password_hash' => $request->input('password'),
                'is_active' => true,
            ]);

            $alumno = Alumno::create([
                'id_usuario' => $usuario->id_usuario,
                'id_tutor' => null,
                'id_grupo' => $request->input('id_grupo'),
                'id_estado_alumno' => $request->input('id_estado_alumno'),
                'id_plan' => $request->input('id_plan'),
                'id_semestre' => $request->input('id_semestre'),
                'id_capacitacion' => $request->input('id_capacitacion') ?: null,
                'id_area_propedeutica' => $camposSemestre['id_area_propedeutica'],
                'actividad_escolar_apa' => $camposSemestre['actividad_escolar_apa'],
                'nombre' => $request->input('nombre'),
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'curp' => strtoupper((string) $request->input('curp')),
                'acta_nacimiento_ruta' => $request->input('acta_nacimiento_ruta'),
                'boleta_secundaria_ruta' => $request->input('boleta_secundaria_ruta'),
                'correo' => $request->input('correo'),
                'promedio_general' => $request->input('promedio_general'),
            ]);

            BitacoraService::registrar(
                'alumnos',
                'ALTA',
                sprintf('Alta de Alumno: %s %s (%s).', $alumno->nombre, $alumno->apellido_paterno, $usuario->username),
                $alumno->id_alumno
            );
        });

        return redirect()->route('admin.alumnos.index')
            ->with('status', 'Alumno creado correctamente.');
    }

    public function edit(Alumno $alumno)
    {
        $alumno->load('usuario');

        return view('admin.alumnos.edit', array_merge([
            'titulo' => 'Editar alumno',
            'subtitulo' => 'Actualización del expediente y la cuenta de acceso.',
            'icono' => 'fa-user-pen',
            'etiqueta' => 'Administrador',
            'alumno' => $alumno,
            'usuario' => $alumno->usuario,
        ], $this->catalogos()));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $usuario = $alumno->usuario;

        Validator::make($request->all(), $this->rules($request, $usuario, $alumno), $this->mensajes())->validate();

        if ($usuario->id_usuario === Auth::id() && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'No puedes desactivar tu propia cuenta.'])->withInput();
        }

        $camposSemestre = $this->camposPorSemestre($request);

        DB::transaction(function () use ($request, $alumno, $usuario, $camposSemestre) {
            $datosUsuario = [
                'username' => $request->input('username'),
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->filled('password')) {
                $datosUsuario['password_hash'] = $request->input('password');
            }

            $usuario->update($datosUsuario);

            $alumno->update([
                'id_grupo' => $request->input('id_grupo'),
                'id_estado_alumno' => $request->input('id_estado_alumno'),
                'id_plan' => $request->input('id_plan'),
                'id_semestre' => $request->input('id_semestre'),
                'id_capacitacion' => $request->input('id_capacitacion') ?: null,
                'id_area_propedeutica' => $camposSemestre['id_area_propedeutica'],
                'actividad_escolar_apa' => $camposSemestre['actividad_escolar_apa'],
                'nombre' => $request->input('nombre'),
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'curp' => strtoupper((string) $request->input('curp')),
                'acta_nacimiento_ruta' => $request->input('acta_nacimiento_ruta'),
                'boleta_secundaria_ruta' => $request->input('boleta_secundaria_ruta'),
                'correo' => $request->input('correo'),
                'promedio_general' => $request->input('promedio_general'),
            ]);

            BitacoraService::registrar(
                'alumnos',
                $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION',
                sprintf('Actualización de Alumno: %s %s (%s).', $alumno->nombre, $alumno->apellido_paterno, $usuario->username),
                $alumno->id_alumno
            );
        });

        return redirect()->route('admin.alumnos.index')
            ->with('status', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $usuario = $alumno->usuario;

        if ($usuario->id_usuario === Auth::id()) {
            return back()->withErrors(['is_active' => 'No puedes dar de baja tu propia cuenta.']);
        }

        DB::transaction(function () use ($alumno, $usuario) {
            $usuario->update(['is_active' => false]);

            // alumnos no tiene is_active: la baja se refleja en estados_alumno.
            $alumno->update(['id_estado_alumno' => EstadoAlumno::BAJA_DEFINITIVA]);

            BitacoraService::registrar(
                'alumnos',
                'BAJA',
                sprintf('Baja definitiva de Alumno: %s %s (%s).', $alumno->nombre, $alumno->apellido_paterno, $usuario->username),
                $alumno->id_alumno
            );
        });

        return redirect()->route('admin.alumnos.index')
            ->with('status', "Baja definitiva aplicada a alumno {$alumno->nombre} {$alumno->apellido_paterno}.");
    }

    private function numeroSemestre(Request $request): ?int
    {
        $idSemestre = $request->input('id_semestre');

        if (! is_numeric($idSemestre)) {
            return null;
        }

        return Semestre::whereKey((int) $idSemestre)->value('numero_semestre');
    }

    private function grupoPerteneceASemestre(Request $request): bool
    {
        $idGrupo = $request->input('id_grupo');
        $idSemestre = $request->input('id_semestre');

        if (! is_numeric($idGrupo) || ! is_numeric($idSemestre)) {
            return false;
        }

        return Grupo::whereKey((int) $idGrupo)
            ->where('id_semestre', (int) $idSemestre)
            ->exists();
    }

    /**
     * @return array<string, int|string|null>
     */
    private function camposPorSemestre(Request $request): array
    {
        $numero = $this->numeroSemestre($request);

        return [
            'id_area_propedeutica' => in_array($numero, [5, 6], true)
                ? ($request->input('id_area_propedeutica') ?: null)
                : null,
            'actividad_escolar_apa' => in_array($numero, [1, 2, 3, 4], true)
                ? trim((string) $request->input('actividad_escolar_apa'))
                : null,
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(Request $request, ?User $usuario = null, ?Alumno $alumno = null): array
    {
        $numeroSemestre = $this->numeroSemestre($request);
        $requiereArea = in_array($numeroSemestre, [5, 6], true);
        $requiereApa = in_array($numeroSemestre, [1, 2, 3, 4], true);

        $usernameRule = Rule::unique('usuarios', 'username');
        if ($usuario) {
            $usernameRule->ignore($usuario->id_usuario, 'id_usuario');
        }

        $curpRule = Rule::unique('alumnos', 'curp');
        $correoRule = Rule::unique('alumnos', 'correo');
        if ($alumno) {
            $curpRule->ignore($alumno->id_alumno, 'id_alumno');
            $correoRule->ignore($alumno->id_alumno, 'id_alumno');
        }

        return [
            'id_grupo' => [
                'required',
                'exists:grupos,id_grupo',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    if (! $this->grupoPerteneceASemestre($request)) {
                        $fail('El grupo seleccionado no corresponde al semestre elegido.');
                    }
                },
            ],
            'id_estado_alumno' => ['required', 'exists:estados_alumno,id_estado_alumno'],
            'id_plan' => ['required', 'exists:planes_estudio,id_plan'],
            'id_semestre' => ['required', 'exists:semestres,id_semestre'],
            'id_capacitacion' => ['nullable', 'exists:capacitaciones,id_capacitacion'],
            'id_area_propedeutica' => [
                'nullable',
                Rule::requiredIf($requiereArea),
                'exists:areas_propedeuticas,id_area_propedeutica',
            ],
            'actividad_escolar_apa' => [
                'nullable',
                Rule::requiredIf($requiereApa),
                'string',
                'max:120',
            ],
            'nombre' => ['required', 'string', 'max:60'],
            'apellido_paterno' => ['required', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'curp' => [
                'required', 'string', 'size:18', 'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2}$/', $curpRule,
            ],
            'acta_nacimiento_ruta' => ['required', 'string', 'max:255'],
            'boleta_secundaria_ruta' => ['required', 'string', 'max:255'],
            'correo' => ['required', 'email', 'max:120', $correoRule],
            'promedio_general' => ['required', 'numeric', 'min:0', 'max:10'],
            'username' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9._-]+$/', $usernameRule],
            'password' => $usuario
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mensajes(): array
    {
        return [
            'id_grupo.required' => 'Selecciona un grupo.',
            'id_grupo.exists' => 'Selecciona un grupo válido.',
            'id_estado_alumno.required' => 'Selecciona un estatus escolar.',
            'id_plan.required' => 'Selecciona un plan de estudios.',
            'id_semestre.required' => 'Selecciona un semestre.',
            'id_area_propedeutica.required' => 'Selecciona el área propedéutica desde 5to semestre.',
            'actividad_escolar_apa.required' => 'Ingresa la actividad para escolar (APA).',
            'actividad_escolar_apa.max' => 'La actividad APA no puede superar los 120 caracteres.',
            'exists' => 'Selecciona una opción válida.',
            'nombre.required' => 'Ingresa el nombre del alumno.',
            'apellido_paterno.required' => 'Ingresa el apellido paterno.',
            'curp.required' => 'Ingresa la CURP.',
            'curp.size' => 'La CURP debe tener 18 caracteres.',
            'curp.regex' => 'Ingresa una CURP válida en mayúsculas.',
            'curp.unique' => 'Esa CURP ya está registrada.',
            'acta_nacimiento_ruta.required' => 'Ingresa la ruta del acta de nacimiento.',
            'boleta_secundaria_ruta.required' => 'Ingresa la ruta de la boleta de secundaria.',
            'correo.required' => 'Ingresa el correo.',
            'correo.email' => 'Ingresa un correo válido.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'promedio_general.required' => 'Ingresa el promedio.',
            'promedio_general.numeric' => 'El promedio debe ser un número.',
            'promedio_general.min' => 'El promedio mínimo es 0.',
            'promedio_general.max' => 'El promedio máximo es 10.',
            'username.required' => 'Ingresa el usuario de acceso.',
            'username.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'username.unique' => 'Ese usuario ya está registrado.',
            'password.required' => 'Ingresa una contraseña temporal.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }

    /**
     * @return array<string, \Illuminate\Support\Collection|array<int, mixed>>
     */
    private function catalogos(): array
    {
        return [
            'grupos' => Grupo::with('semestre')
                ->orderBy('id_semestre')
                ->orderBy('nombre')
                ->get()
                ->map(fn (Grupo $grupo) => [
                    'id' => $grupo->id_grupo,
                    'id_semestre' => $grupo->id_semestre,
                    'etiqueta' => $grupo->etiqueta(),
                ])
                ->values(),
            'estados' => EstadoAlumno::orderBy('nombre')->pluck('nombre', 'id_estado_alumno'),
            'planes' => PlanEstudio::orderBy('nombre')->pluck('nombre', 'id_plan'),
            'semestres' => Semestre::orderBy('numero_semestre')->pluck('nombre', 'id_semestre'),
            'semestresArea' => Semestre::whereIn('numero_semestre', [5, 6])->pluck('id_semestre')->all(),
            'semestresApa' => Semestre::whereIn('numero_semestre', [1, 2, 3, 4])->pluck('id_semestre')->all(),
            'capacitaciones' => Capacitacion::orderBy('nombre')->pluck('nombre', 'id_capacitacion'),
            'areas' => AreaPropedeutica::orderBy('nombre')->pluck('nombre', 'id_area_propedeutica'),
        ];
    }
}
