<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraAuditoria;
use App\Models\Rol;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeguridadController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['q', 'estado', 'id_rol']);
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $usuarios = User::with(['rol', 'administrador', 'docente', 'alumno', 'tutor'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('username', 'like', $like)
                        ->orWhereHas('administrador', fn ($q) => $q->where('nombre', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('correo_institucional', 'like', $like))
                        ->orWhereHas('docente', fn ($q) => $q->where('nombre', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('correo_institucional', 'like', $like))
                        ->orWhereHas('alumno', fn ($q) => $q->where('nombre', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('correo', 'like', $like))
                        ->orWhereHas('tutor', fn ($q) => $q->where('nombre', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('correo', 'like', $like));
                });
            })
            ->when($request->input('id_rol'), fn ($query, $rol) => $query->where('id_rol', $rol))
            ->when($request->input('estado') === 'activo', function ($query) {
                $query->where('is_active', true)->where(function ($query) {
                    $query->whereNull('bloqueado_hasta')->orWhere('bloqueado_hasta', '<=', now());
                });
            })
            ->when($request->input('estado') === 'inactivo', fn ($query) => $query->where('is_active', false))
            ->when($request->input('estado') === 'bloqueado', function ($query) {
                $query->where('is_active', true)->where('bloqueado_hasta', '>', now());
            })
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        return view('admin.seguridad.index', [
            'titulo' => 'Seguridad de accesos',
            'subtitulo' => 'Revisión de intentos fallidos, bloqueos temporales y estatus de las cuentas.',
            'icono' => 'fa-key',
            'etiqueta' => 'SuperUsuario',
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('nombre')->pluck('nombre', 'id_rol'),
            'filtros' => $filtros,
        ]);
    }

    public function show(User $usuario)
    {
        $usuario->load(['rol', 'administrador', 'docente', 'alumno', 'tutor']);

        $eventos = BitacoraAuditoria::where('id_usuario', $usuario->id_usuario)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.seguridad.show', [
            'titulo' => 'Detalle de acceso',
            'subtitulo' => "Cuenta de acceso: {$usuario->username}.",
            'icono' => 'fa-user-lock',
            'etiqueta' => 'SuperUsuario',
            'usuario' => $usuario,
            'eventos' => $eventos,
        ]);
    }

    public function desbloquear(User $usuario)
    {
        if ($this->esCuentaPropia($usuario)) {
            return back()->withErrors(['seguridad' => 'No puedes desbloquear tu propia cuenta.']);
        }

        DB::transaction(function () use ($usuario) {
            $usuario->forceFill([
                'bloqueado_hasta' => null,
                'intentos_fallidos' => 0,
            ])->save();

            BitacoraService::registrar(
                'usuarios',
                'DESBLOQUEO',
                "Desbloqueo de cuenta: {$usuario->username}.",
                $usuario->id_usuario
            );
        });

        return back()->with('status', "Cuenta de {$usuario->username} desbloqueada.");
    }

    public function reiniciarIntentos(User $usuario)
    {
        if ($this->esCuentaPropia($usuario)) {
            return back()->withErrors(['seguridad' => 'No puedes modificar los intentos de tu propia cuenta.']);
        }

        DB::transaction(function () use ($usuario) {
            $usuario->forceFill(['intentos_fallidos' => 0])->save();

            BitacoraService::registrar(
                'usuarios',
                'REINICIO_INTENTOS',
                "Reinicio de intentos fallidos: {$usuario->username}.",
                $usuario->id_usuario
            );
        });

        return back()->with('status', "Intentos fallidos de {$usuario->username} reiniciados.");
    }

    public function cambiarEstatus(Request $request, User $usuario)
    {
        if ($this->esCuentaPropia($usuario)) {
            return back()->withErrors(['seguridad' => 'No puedes cambiar el estatus de tu propia cuenta.']);
        }

        $activar = $request->boolean('is_active');

        DB::transaction(function () use ($usuario, $activar) {
            $usuario->update(['is_active' => $activar]);

            $this->sincronizarPerfil($usuario, $activar);

            BitacoraService::registrar(
                'usuarios',
                $activar ? 'ACTIVACION' : 'SUSPENSION',
                ($activar ? 'Activación' : 'Suspensión')." de cuenta desde seguridad de accesos: {$usuario->username}.",
                $usuario->id_usuario
            );
        });

        return back()->with('status', $activar
            ? "Cuenta de {$usuario->username} activada."
            : "Cuenta de {$usuario->username} suspendida.");
    }

    private function esCuentaPropia(User $usuario): bool
    {
        return $usuario->id_usuario === Auth::id();
    }

    private function sincronizarPerfil(User $usuario, bool $activo): void
    {
        // alumnos no tiene columna is_active; su estatus se controla en estados_alumno.
        $perfil = match ($usuario->id_rol) {
            Rol::ADMINISTRADOR => $usuario->administrador,
            Rol::DOCENTE => $usuario->docente,
            Rol::TUTOR => $usuario->tutor,
            default => null,
        };

        if ($perfil) {
            $perfil->update(['is_active' => $activo]);
        }
    }
}
