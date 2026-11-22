<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaPropedeutica;
use App\Models\Capacitacion;
use App\Models\Docente;
use App\Models\Rol;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocentesController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $docentes = Docente::with('usuario')
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('curp', 'like', $like)
                        ->orWhere('rfc', 'like', $like)
                        ->orWhere('matricula_rfc', 'like', $like)
                        ->orWhere('licenciatura', 'like', $like)
                        ->orWhere('especialidad', 'like', $like)
                        ->orWhere('maestria', 'like', $like)
                        ->orWhere('doctorado', 'like', $like)
                        ->orWhere('numero_personal_sep', 'like', $like)
                        ->orWhere('contacto', 'like', $like)
                        ->orWhere('nombramiento', 'like', $like)
                        ->orWhere('categoria', 'like', $like)
                        ->orWhere('plaza', 'like', $like)
                        ->orWhere('sindicato', 'like', $like)
                        ->orWhere('titulo', 'like', $like)
                        ->orWhere('cedula_profesional', 'like', $like)
                        ->orWhere('correo_institucional', 'like', $like)
                        ->orWhereHas('usuario', fn ($q) => $q->where('username', 'like', $like));
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get();

        return view('admin.docentes.index', array_merge([
            'titulo' => 'Docentes',
            'subtitulo' => 'Perfil profesional, carga horaria y formación académica del personal docente.',
            'icono' => 'fa-chalkboard-user',
            'etiqueta' => 'Administrador',
            'docentes' => $docentes,
            'busqueda' => $busqueda,
        ], $this->catalogos()));
    }

    public function create()
    {
        return view('admin.docentes.create', array_merge([
            'titulo' => 'Nuevo docente',
            'subtitulo' => 'La cuenta de acceso se crea junto con el perfil del docente.',
            'icono' => 'fa-user-plus',
            'etiqueta' => 'Administrador',
        ], $this->catalogos()));
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->rules(), $this->mensajes())->validate();

        DB::transaction(function () use ($request) {
            $usuario = User::create([
                'id_rol' => Rol::DOCENTE,
                'username' => $request->input('username'),
                // El cast "hashed" del modelo User cifra la contraseña antes de guardarla.
                'password_hash' => $request->input('password'),
                'is_active' => true,
            ]);

            $docente = Docente::create(array_merge(
                $this->datosPerfil($request, true),
                ['id_usuario' => $usuario->id_usuario]
            ));

            BitacoraService::registrar(
                'docentes',
                'ALTA',
                sprintf(
                    'Alta de docente: %s %s (%s).',
                    $docente->nombre,
                    $docente->apellido_paterno,
                    $usuario->username
                ),
                $docente->id_docente
            );
        });

        return redirect()->route('admin.docentes.index')
            ->with('status', 'Docente creado correctamente.');
    }

    public function edit(Docente $docente)
    {
        if (! $docente->usuario) {
            abort(404);
        }

        return view('admin.docentes.edit', array_merge([
            'titulo' => 'Editar docente',
            'subtitulo' => 'Actualización del perfil profesional y de la cuenta de acceso.',
            'icono' => 'fa-user-pen',
            'etiqueta' => 'Administrador',
            'docente' => $docente,
            'usuario' => $docente->usuario,
        ], $this->catalogos()));
    }

    public function update(Request $request, Docente $docente)
    {
        $usuario = $docente->usuario;
        if (! $usuario) {
            abort(404);
        }

        Validator::make($request->all(), $this->rules($docente, $usuario), $this->mensajes())->validate();

        DB::transaction(function () use ($request, $docente, $usuario) {
            $datosUsuario = [
                'username' => $request->input('username'),
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->filled('password')) {
                // El cast "hashed" del modelo User cifra la contraseña antes de guardarla.
                $datosUsuario['password_hash'] = $request->input('password');
            }

            $usuario->update($datosUsuario);
            $docente->update($this->datosPerfil($request, $request->boolean('is_active')));

            BitacoraService::registrar(
                'docentes',
                $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION',
                sprintf(
                    'Actualización de docente: %s %s (%s).',
                    $docente->nombre,
                    $docente->apellido_paterno,
                    $usuario->username
                ),
                $docente->id_docente
            );
        });

        return redirect()->route('admin.docentes.index')
            ->with('status', 'Docente actualizado correctamente.');
    }

    public function destroy(Docente $docente)
    {
        $usuario = $docente->usuario;
        if (! $usuario) {
            abort(404);
        }

        DB::transaction(function () use ($docente, $usuario) {
            $usuario->update(['is_active' => false]);
            $docente->update(['is_active' => false]);

            BitacoraService::registrar(
                'docentes',
                'BAJA',
                sprintf(
                    'Baja lógica de docente: %s %s (%s).',
                    $docente->nombre,
                    $docente->apellido_paterno,
                    $usuario->username
                ),
                $docente->id_docente
            );
        });

        return redirect()->route('admin.docentes.index')
            ->with('status', 'Baja lógica aplicada al docente '.$docente->nombreCompleto().'.');
    }

    /**
     * @return array<string, string>
     */
    private function datosPerfil(Request $request, bool $activo): array
    {
        $rfc = mb_strtoupper(trim((string) $request->input('rfc')));

        return [
            'nombre' => trim((string) $request->input('nombre')),
            'apellido_paterno' => trim((string) $request->input('apellido_paterno')),
            'apellido_materno' => trim((string) $request->input('apellido_materno')) ?: null,
            'curp' => mb_strtoupper(trim((string) $request->input('curp'))),
            'rfc' => $rfc,
            // Se mantiene sincronizado con el campo histórico del esquema.
            'matricula_rfc' => $rfc,
            'correo_institucional' => mb_strtolower(trim((string) $request->input('correo_institucional'))),
            'horas_semanales' => (int) $request->input('horas_semanales'),
            'licenciatura' => trim((string) $request->input('licenciatura')),
            'especialidad' => trim((string) $request->input('especialidad')) ?: null,
            'maestria' => trim((string) $request->input('maestria')) ?: null,
            'doctorado' => trim((string) $request->input('doctorado')) ?: null,
            'numero_personal_sep' => mb_strtoupper(trim((string) $request->input('numero_personal_sep'))) ?: null,
            'contacto' => trim((string) $request->input('contacto')) ?: null,
            'nombramiento' => trim((string) $request->input('nombramiento')) ?: null,
            'categoria' => trim((string) $request->input('categoria')) ?: null,
            'plaza' => trim((string) $request->input('plaza')) ?: null,
            'sindicato' => trim((string) $request->input('sindicato')) ?: null,
            'titulo' => trim((string) $request->input('titulo')) ?: null,
            'cedula_profesional' => mb_strtoupper(trim((string) $request->input('cedula_profesional'))) ?: null,
            'id_capacitacion' => $request->input('id_capacitacion') ?: null,
            'id_area_propedeutica' => $request->input('id_area_propedeutica') ?: null,
            'is_active' => $activo,
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Docente $docente = null, ?User $usuario = null): array
    {
        $usernameRule = Rule::unique('usuarios', 'username');
        $curpRule = Rule::unique('docentes', 'curp');
        $rfcRule = Rule::unique('docentes', 'rfc');
        $correoRule = Rule::unique('docentes', 'correo_institucional');

        if ($docente) {
            $curpRule->ignore($docente->id_docente, 'id_docente');
            $rfcRule->ignore($docente->id_docente, 'id_docente');
            $correoRule->ignore($docente->id_docente, 'id_docente');
        }

        if ($usuario) {
            $usernameRule->ignore($usuario->id_usuario, 'id_usuario');
        }

        return [
            'nombre' => ['required', 'string', 'max:60'],
            'apellido_paterno' => ['required', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'curp' => ['required', 'string', 'size:18', 'regex:/^[A-Z0-9]{18}$/i', $curpRule],
            'rfc' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9&.\-\s]{12,20}$/i', $rfcRule],
            'correo_institucional' => ['required', 'email', 'max:120', $correoRule],
            'horas_semanales' => ['required', 'integer', 'min:1', 'max:35'],
            'licenciatura' => ['required', 'string', 'max:120'],
            'especialidad' => ['nullable', 'string', 'max:120'],
            'maestria' => ['nullable', 'string', 'max:120'],
            'doctorado' => ['nullable', 'string', 'max:120'],
            'numero_personal_sep' => ['nullable', 'string', 'max:30'],
            'contacto' => ['nullable', 'string', 'max:60'],
            'nombramiento' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'plaza' => ['nullable', 'string', 'max:60'],
            'sindicato' => ['nullable', 'string', 'max:100'],
            'titulo' => ['nullable', 'string', 'max:120'],
            'cedula_profesional' => ['nullable', 'string', 'max:40'],
            'id_capacitacion' => ['nullable', 'exists:capacitaciones,id_capacitacion'],
            'id_area_propedeutica' => ['nullable', 'exists:areas_propedeuticas,id_area_propedeutica'],
            'username' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9._-]+$/', $usernameRule],
            'password' => $docente
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
            'nombre.required' => 'Ingresa el nombre del docente.',
            'apellido_paterno.required' => 'Ingresa el apellido paterno.',
            'curp.required' => 'Ingresa la CURP del docente.',
            'curp.size' => 'La CURP debe tener 18 caracteres.',
            'curp.regex' => 'La CURP solo puede contener letras y números.',
            'curp.unique' => 'Esa CURP ya está registrada.',
            'rfc.required' => 'Ingresa el RFC del docente.',
            'rfc.regex' => 'Ingresa un RFC válido.',
            'rfc.unique' => 'Ese RFC ya está registrado.',
            'correo_institucional.required' => 'Ingresa el correo institucional.',
            'correo_institucional.email' => 'Ingresa un correo válido.',
            'correo_institucional.unique' => 'Ese correo ya está registrado.',
            'horas_semanales.required' => 'Ingresa el tope de horas semanales.',
            'horas_semanales.min' => 'El tope debe ser al menos 1 hora.',
            'horas_semanales.max' => 'El horario permite hasta 35 bloques por semana.',
            'licenciatura.required' => 'Ingresa la licenciatura del docente.',
            'numero_personal_sep.max' => 'El número de personal SEP no puede superar 30 caracteres.',
            'contacto.max' => 'El contacto no puede superar 60 caracteres.',
            'nombramiento.max' => 'El nombramiento no puede superar 100 caracteres.',
            'categoria.max' => 'La categoría no puede superar 100 caracteres.',
            'plaza.max' => 'La plaza no puede superar 60 caracteres.',
            'sindicato.max' => 'El sindicato no puede superar 100 caracteres.',
            'titulo.max' => 'El título no puede superar 120 caracteres.',
            'cedula_profesional.max' => 'La cédula profesional no puede superar 40 caracteres.',
            'username.required' => 'Ingresa el usuario de acceso.',
            'username.unique' => 'Ese usuario ya está registrado.',
            'username.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'password.required' => 'Ingresa una contraseña temporal.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'exists' => 'Selecciona una opción válida.',
        ];
    }

    /**
     * @return array<string, \Illuminate\Support\Collection>
     */
    private function catalogos(): array
    {
        return [
            'capacitaciones' => Capacitacion::orderBy('nombre')->pluck('nombre', 'id_capacitacion'),
            'areas' => AreaPropedeutica::orderBy('nombre')->pluck('nombre', 'id_area_propedeutica'),
        ];
    }
}
