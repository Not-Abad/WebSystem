<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Rol;
use App\Models\Tutor;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TutoresController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $tutores = Tutor::with(['usuario', 'alumnos'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('correo', 'like', $like)
                        ->orWhereHas('usuario', fn ($q) => $q->where('username', 'like', $like));
                });
            })
            ->when($request->input('estado') === 'activo', fn ($query) => $query->where('is_active', true))
            ->when($request->input('estado') === 'inactivo', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id_tutor')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tutores.index', [
            'titulo' => 'Tutores',
            'subtitulo' => 'Vinculación de tutores con estudiantes y control de sus cuentas de acceso.',
            'icono' => 'fa-users',
            'etiqueta' => 'Administrador',
            'tutores' => $tutores,
            'busqueda' => $busqueda,
            'estado' => $request->input('estado', ''),
        ]);
    }

    public function create()
    {
        return view('admin.tutores.create', [
            'titulo' => 'Nuevo tutor',
            'subtitulo' => 'Se creará su cuenta de acceso con el rol Tutor.',
            'icono' => 'fa-user-plus',
            'etiqueta' => 'Administrador',
        ]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->rules(), $this->mensajes())->validate();

        DB::transaction(function () use ($request) {
            $usuario = User::create([
                'id_rol' => Rol::TUTOR,
                'username' => $request->input('username'),
                'password_hash' => $request->input('password'),
                'is_active' => true,
            ]);

            $tutor = Tutor::create([
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $request->input('nombre'),
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'telefono' => $request->input('telefono'),
                'correo' => $request->input('correo'),
                'ine_ruta' => $request->input('ine_ruta'),
                'is_active' => true,
            ]);

            BitacoraService::registrar(
                'tutores',
                'ALTA',
                sprintf('Alta de Tutor: %s %s (%s).', $tutor->nombre, $tutor->apellido_paterno, $usuario->username),
                $tutor->id_tutor
            );
        });

        return redirect()->route('admin.tutores.index')
            ->with('status', 'Tutor creado correctamente.');
    }

    public function edit(Tutor $tutor)
    {
        $tutor->load('usuario');

        return view('admin.tutores.edit', [
            'titulo' => 'Editar tutor',
            'subtitulo' => 'Actualización del perfil y la cuenta de acceso.',
            'icono' => 'fa-user-pen',
            'etiqueta' => 'Administrador',
            'tutor' => $tutor,
            'usuario' => $tutor->usuario,
        ]);
    }

    public function update(Request $request, Tutor $tutor)
    {
        $usuario = $tutor->usuario;

        Validator::make($request->all(), $this->rules($usuario, $tutor), $this->mensajes())->validate();

        if ($usuario->id_usuario === Auth::id() && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'No puedes desactivar tu propia cuenta.'])->withInput();
        }

        DB::transaction(function () use ($request, $tutor, $usuario) {
            $datosUsuario = [
                'username' => $request->input('username'),
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->filled('password')) {
                $datosUsuario['password_hash'] = $request->input('password');
            }

            $usuario->update($datosUsuario);

            $tutor->update([
                'nombre' => $request->input('nombre'),
                'apellido_paterno' => $request->input('apellido_paterno'),
                'apellido_materno' => $request->input('apellido_materno'),
                'telefono' => $request->input('telefono'),
                'correo' => $request->input('correo'),
                'ine_ruta' => $request->input('ine_ruta'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar(
                'tutores',
                $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION',
                sprintf('Actualización de Tutor: %s %s (%s).', $tutor->nombre, $tutor->apellido_paterno, $usuario->username),
                $tutor->id_tutor
            );
        });

        return redirect()->route('admin.tutores.index')
            ->with('status', 'Tutor actualizado correctamente.');
    }

    public function destroy(Tutor $tutor)
    {
        $usuario = $tutor->usuario;

        if ($usuario->id_usuario === Auth::id()) {
            return back()->withErrors(['is_active' => 'No puedes dar de baja tu propia cuenta.']);
        }

        DB::transaction(function () use ($tutor, $usuario) {
            $usuario->update(['is_active' => false]);
            $tutor->update(['is_active' => false]);

            BitacoraService::registrar(
                'tutores',
                'BAJA',
                sprintf('Baja lógica de Tutor: %s %s (%s).', $tutor->nombre, $tutor->apellido_paterno, $usuario->username),
                $tutor->id_tutor
            );
        });

        return redirect()->route('admin.tutores.index')
            ->with('status', "Baja lógica aplicada a tutor {$tutor->nombre} {$tutor->apellido_paterno}.");
    }

    public function alumnos(Request $request, Tutor $tutor)
    {
        $tutor->load(['usuario', 'alumnos.estado', 'alumnos.semestre']);

        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $disponibles = Alumno::with('tutor')
            ->where(function ($query) use ($tutor) {
                $query->where('id_tutor', '!=', $tutor->id_tutor)
                    ->orWhereNull('id_tutor');
            })
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('curp', 'like', $like)
                        ->orWhere('correo', 'like', $like);
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tutores.alumnos', [
            'titulo' => 'Alumnos de '.$tutor->nombre.' '.$tutor->apellido_paterno,
            'subtitulo' => 'Vinculación entre el tutor y los estudiantes bajo su tutela.',
            'icono' => 'fa-link',
            'etiqueta' => 'Administrador',
            'tutor' => $tutor,
            'disponibles' => $disponibles,
            'busqueda' => $busqueda,
        ]);
    }

    public function vincularAlumno(Request $request, Tutor $tutor)
    {
        Validator::make($request->all(), [
            'id_alumno' => ['required', 'exists:alumnos,id_alumno'],
        ], [
            'id_alumno.required' => 'Selecciona un alumno.',
            'id_alumno.exists' => 'Selecciona un alumno válido.',
        ])->validate();

        $alumno = Alumno::findOrFail($request->input('id_alumno'));

        if ($alumno->id_tutor === $tutor->id_tutor) {
            return back()->withErrors(['id_alumno' => 'Ese alumno ya está vinculado a este tutor.']);
        }

        $tutorAnterior = $alumno->tutor;

        DB::transaction(function () use ($alumno, $tutor) {
            $alumno->update(['id_tutor' => $tutor->id_tutor]);

            BitacoraService::registrar(
                'alumnos',
                'VINCULACION',
                sprintf(
                    'Alumno %s %s vinculado al tutor %s %s.',
                    $alumno->nombre,
                    $alumno->apellido_paterno,
                    $tutor->nombre,
                    $tutor->apellido_paterno
                ),
                $alumno->id_alumno
            );
        });

        $detalle = $tutorAnterior
            ? ' Antes estaba con '.$tutorAnterior->nombre.' '.$tutorAnterior->apellido_paterno.'.'
            : '';

        return back()->with('status', 'Alumno vinculado correctamente.'.$detalle);
    }

    public function desasignarAlumno(Tutor $tutor, Alumno $alumno)
    {
        if ($alumno->id_tutor !== $tutor->id_tutor) {
            abort(404);
        }

        DB::transaction(function () use ($alumno, $tutor) {
            $alumno->update(['id_tutor' => null]);

            BitacoraService::registrar(
                'alumnos',
                'DESASIGNACION',
                sprintf(
                    'Alumno %s %s desasignado del tutor %s %s.',
                    $alumno->nombre,
                    $alumno->apellido_paterno,
                    $tutor->nombre,
                    $tutor->apellido_paterno
                ),
                $alumno->id_alumno
            );
        });

        return back()->with('status', 'Alumno desasignado correctamente. Ahora aparece como disponible para vincular.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?User $usuario = null, ?Tutor $tutor = null): array
    {
        $usernameRule = Rule::unique('usuarios', 'username');
        if ($usuario) {
            $usernameRule->ignore($usuario->id_usuario, 'id_usuario');
        }

        $correoRule = Rule::unique('tutores', 'correo');
        if ($tutor) {
            $correoRule->ignore($tutor->id_tutor, 'id_tutor');
        }

        return [
            'nombre' => ['required', 'string', 'max:60'],
            'apellido_paterno' => ['required', 'string', 'max:60'],
            'apellido_materno' => ['nullable', 'string', 'max:60'],
            'telefono' => ['required', 'string', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
            'correo' => ['required', 'email', 'max:120', $correoRule],
            'ine_ruta' => ['required', 'string', 'max:255'],
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
            'nombre.required' => 'Ingresa el nombre del tutor.',
            'apellido_paterno.required' => 'Ingresa el apellido paterno.',
            'telefono.required' => 'Ingresa el teléfono.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, + o -.',
            'correo.required' => 'Ingresa el correo.',
            'correo.email' => 'Ingresa un correo válido.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'ine_ruta.required' => 'Ingresa la ruta o identificador del INE.',
            'username.required' => 'Ingresa el usuario de acceso.',
            'username.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'username.unique' => 'Ese usuario ya está registrado.',
            'password.required' => 'Ingresa una contraseña temporal.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
