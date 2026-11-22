<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\AreaPropedeutica;
use App\Models\Capacitacion;
use App\Models\Docente;
use App\Models\PuestoAdministrativo;
use App\Models\Rol;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    private const TIPOS = ['Administrador', 'Docente'];

    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $administradores = Administrador::with(['usuario', 'puesto'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('matricula_rfc', 'like', $like)
                        ->orWhere('correo_institucional', 'like', $like)
                        ->orWhereHas('usuario', fn ($q) => $q->where('username', 'like', $like));
                });
            })
            ->orderByDesc('id_administrador')
            ->get();

        $docentes = Docente::with('usuario')
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('matricula_rfc', 'like', $like)
                        ->orWhere('correo_institucional', 'like', $like)
                        ->orWhereHas('usuario', fn ($q) => $q->where('username', 'like', $like));
                });
            })
            ->orderByDesc('id_docente')
            ->get();

        return view('admin.personal.index', [
            'titulo' => 'Control de personal',
            'subtitulo' => 'Altas, bajas lógicas y suspensión de cuentas de administradores y docentes.',
            'icono' => 'fa-user-shield',
            'etiqueta' => 'SuperUsuario',
            'administradores' => $administradores,
            'docentes' => $docentes,
            'busqueda' => $busqueda,
        ]);
    }

    public function create()
    {
        return view('admin.personal.create', array_merge([
            'titulo' => 'Nuevo personal',
            'subtitulo' => 'La cuenta de acceso se crea junto con el perfil del empleado.',
            'icono' => 'fa-user-plus',
            'etiqueta' => 'SuperUsuario',
        ], $this->catalogos()));
    }

    public function store(Request $request)
    {
        $tipo = (string) $request->input('tipo');

        if (! in_array($tipo, self::TIPOS, true)) {
            return back()->withErrors(['tipo' => 'Selecciona un tipo de personal válido.'])->withInput();
        }

        Validator::make($request->all(), $this->rules($tipo), $this->mensajes())
            ->validate();

        DB::transaction(function () use ($request, $tipo) {
            $usuario = User::create([
                'id_rol' => $tipo === 'Administrador' ? Rol::ADMINISTRADOR : Rol::DOCENTE,
                'username' => $request->input('username'),
                // El cast "hashed" del modelo User cifra la contraseña antes de guardarla.
                'password_hash' => $request->input('password'),
                'is_active' => true,
            ]);

            $datosPerfil = $this->datosPerfil($request, $tipo, true);
            $datosPerfil['id_usuario'] = $usuario->id_usuario;

            if ($tipo === 'Administrador') {
                $datosPerfil['id_puesto'] = $request->input('id_puesto');
                $perfil = Administrador::create($datosPerfil);
            } else {
                $perfil = Docente::create($datosPerfil);
            }

            BitacoraService::registrar(
                'usuarios',
                'ALTA',
                sprintf(
                    'Alta de %s: %s %s (%s).',
                    $tipo,
                    $perfil->nombre,
                    $perfil->apellido_paterno,
                    $usuario->username
                ),
                $usuario->id_usuario
            );
        });

        return redirect()->route('admin.personal.index')
            ->with('status', "Personal {$tipo} creado correctamente.");
    }

    public function edit(User $usuario)
    {
        [$tipo, $perfil] = $this->perfilDeUsuario($usuario);

        return view('admin.personal.edit', array_merge([
            'titulo' => 'Editar personal',
            'subtitulo' => "Actualización de la cuenta y el perfil de {$tipo}.",
            'icono' => 'fa-user-pen',
            'etiqueta' => 'SuperUsuario',
            'usuario' => $usuario,
            'perfil' => $perfil,
            'tipo' => $tipo,
        ], $this->catalogos()));
    }

    public function update(Request $request, User $usuario)
    {
        [$tipo, $perfil] = $this->perfilDeUsuario($usuario);

        Validator::make($request->all(), $this->rules($tipo, $usuario, $perfil), $this->mensajes())
            ->validate();

        if ($usuario->id_usuario === Auth::id() && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'No puedes desactivar tu propia cuenta.'])->withInput();
        }

        DB::transaction(function () use ($request, $usuario, $perfil, $tipo) {
            $datosUsuario = [
                'username' => $request->input('username'),
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->filled('password')) {
                // El cast "hashed" del modelo User cifra la contraseña antes de guardarla.
                $datosUsuario['password_hash'] = $request->input('password');
            }

            $usuario->update($datosUsuario);

            $datosPerfil = $this->datosPerfil($request, $tipo, $request->boolean('is_active'));

            if ($tipo === 'Administrador') {
                $datosPerfil['id_puesto'] = $request->input('id_puesto');
            }

            $perfil->update($datosPerfil);

            BitacoraService::registrar(
                'usuarios',
                $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION',
                sprintf(
                    'Actualización de %s: %s %s (%s).',
                    $tipo,
                    $perfil->nombre,
                    $perfil->apellido_paterno,
                    $usuario->username
                ),
                $usuario->id_usuario
            );
        });

        return redirect()->route('admin.personal.index')
            ->with('status', "Personal {$tipo} actualizado correctamente.");
    }

    public function destroy(User $usuario)
    {
        [$tipo, $perfil] = $this->perfilDeUsuario($usuario);

        if ($usuario->id_usuario === Auth::id()) {
            return back()->withErrors(['is_active' => 'No puedes dar de baja tu propia cuenta.']);
        }

        DB::transaction(function () use ($usuario, $perfil, $tipo) {
            $usuario->update(['is_active' => false]);
            $perfil->update(['is_active' => false]);

            BitacoraService::registrar(
                'usuarios',
                'BAJA',
                sprintf(
                    'Baja lógica de %s: %s %s (%s).',
                    $tipo,
                    $perfil->nombre,
                    $perfil->apellido_paterno,
                    $usuario->username
                ),
                $usuario->id_usuario
            );
        });

        return redirect()->route('admin.personal.index')
            ->with('status', "Baja lógica aplicada a {$tipo} {$perfil->nombre} {$perfil->apellido_paterno}.");
    }

    /**
     * @return array{0: string, 1: \App\Models\Administrador|\App\Models\Docente}
     */
    private function perfilDeUsuario(User $usuario): array
    {
        if ($usuario->esRol('Administrador')) {
            $perfil = $usuario->administrador;
            $tipo = 'Administrador';
        } elseif ($usuario->esRol('Docente')) {
            $perfil = $usuario->docente;
            $tipo = 'Docente';
        } else {
            abort(404);
        }

        if (! $perfil) {
            abort(404);
        }

        return [$tipo, $perfil];
    }

    /**
     * @return array<string, mixed>
     */
    private function datosPerfil(Request $request, string $tipo, bool $activo): array
    {
        $datos = [
            'nombre' => trim((string) $request->input('nombre')),
            'apellido_paterno' => trim((string) $request->input('apellido_paterno')),
            'apellido_materno' => trim((string) $request->input('apellido_materno')) ?: null,
            'correo_institucional' => mb_strtolower(trim((string) $request->input('correo_institucional'))),
            'is_active' => $activo,
        ];

        if ($tipo === 'Administrador') {
            $datos['matricula_rfc'] = $request->input('matricula_rfc');

            return $datos;
        }

        $rfc = mb_strtoupper(trim((string) $request->input('rfc')));

        return array_merge($datos, [
            'curp' => mb_strtoupper(trim((string) $request->input('curp'))),
            'rfc' => $rfc,
            'matricula_rfc' => $rfc,
            'horas_semanales' => (int) $request->input('horas_semanales'),
            'licenciatura' => trim((string) $request->input('licenciatura')),
            'especialidad' => trim((string) $request->input('especialidad')) ?: null,
            'maestria' => trim((string) $request->input('maestria')) ?: null,
            'doctorado' => trim((string) $request->input('doctorado')) ?: null,
            'id_capacitacion' => $request->input('id_capacitacion') ?: null,
            'id_area_propedeutica' => $request->input('id_area_propedeutica') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(string $tipo, ?User $usuario = null, ?object $perfil = null): array
    {
        $usernameRule = Rule::unique('usuarios', 'username');
        if ($usuario) {
            $usernameRule->ignore($usuario->id_usuario, 'id_usuario');
        }

        $tabla = $tipo === 'Administrador' ? 'administradores' : 'docentes';
        $clavePerfil = $tipo === 'Administrador' ? 'id_administrador' : 'id_docente';

        $matriculaRule = Rule::unique($tabla, 'matricula_rfc');
        $curpRule = Rule::unique($tabla, 'curp');
        $rfcRule = Rule::unique($tabla, 'rfc');
        $correoRule = Rule::unique($tabla, 'correo_institucional');
        if ($perfil) {
            $matriculaRule->ignore($perfil->getKey(), $clavePerfil);
            $curpRule->ignore($perfil->getKey(), $clavePerfil);
            $rfcRule->ignore($perfil->getKey(), $clavePerfil);
            $correoRule->ignore($perfil->getKey(), $clavePerfil);
        }

        $rules = [
            'nombre' => ['required', 'string', 'max:60'],
            'apellido_paterno' => ['required', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'correo_institucional' => ['required', 'email', 'max:120', $correoRule],
            'username' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9._-]+$/', $usernameRule],
            'password' => $usuario
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ];

        if ($tipo === 'Administrador') {
            $rules['matricula_rfc'] = ['required', 'string', 'max:20', $matriculaRule];
            $rules['id_puesto'] = ['required', 'exists:puestos_administrativos,id_puesto'];
        } else {
            $rules['curp'] = ['required', 'string', 'size:18', 'regex:/^[A-Z0-9]{18}$/i', $curpRule];
            $rules['rfc'] = ['required', 'string', 'max:20', 'regex:/^[A-Z0-9&.\-\s]{12,20}$/i', $rfcRule];
            $rules['horas_semanales'] = ['required', 'integer', 'min:1', 'max:35'];
            $rules['licenciatura'] = ['required', 'string', 'max:120'];
            $rules['especialidad'] = ['nullable', 'string', 'max:120'];
            $rules['maestria'] = ['nullable', 'string', 'max:120'];
            $rules['doctorado'] = ['nullable', 'string', 'max:120'];
            $rules['id_capacitacion'] = ['nullable', 'exists:capacitaciones,id_capacitacion'];
            $rules['id_area_propedeutica'] = ['nullable', 'exists:areas_propedeuticas,id_area_propedeutica'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function mensajes(): array
    {
        return [
            'nombre.required' => 'Ingresa el nombre.',
            'apellido_paterno.required' => 'Ingresa el apellido paterno.',
            'matricula_rfc.required' => 'Ingresa la matrícula o RFC.',
            'matricula_rfc.unique' => 'Esa matrícula o RFC ya está registrada.',
            'curp.required' => 'Ingresa la CURP del docente.',
            'curp.size' => 'La CURP debe tener 18 caracteres.',
            'curp.unique' => 'Esa CURP ya está registrada.',
            'rfc.required' => 'Ingresa el RFC del docente.',
            'rfc.unique' => 'Ese RFC ya está registrado.',
            'horas_semanales.required' => 'Ingresa el tope de horas semanales.',
            'horas_semanales.max' => 'El horario permite hasta 35 bloques por semana.',
            'licenciatura.required' => 'Ingresa la licenciatura del docente.',
            'correo_institucional.required' => 'Ingresa el correo institucional.',
            'correo_institucional.email' => 'Ingresa un correo válido.',
            'correo_institucional.unique' => 'Ese correo ya está registrado.',
            'username.required' => 'Ingresa el usuario de acceso.',
            'username.unique' => 'Ese usuario ya está registrado.',
            'username.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'password.required' => 'Ingresa una contraseña temporal.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'id_puesto.required' => 'Selecciona un puesto.',
            'exists' => 'Selecciona una opción válida.',
        ];
    }

    /**
     * @return array<string, \Illuminate\Support\Collection>
     */
    private function catalogos(): array
    {
        return [
            'puestos' => PuestoAdministrativo::orderBy('nombre')->pluck('nombre', 'id_puesto'),
            'capacitaciones' => Capacitacion::orderBy('nombre')->pluck('nombre', 'id_capacitacion'),
            'areas' => AreaPropedeutica::orderBy('nombre')->pluck('nombre', 'id_area_propedeutica'),
        ];
    }
}
