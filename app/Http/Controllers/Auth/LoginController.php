<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_INTENTOS = 5;
    private const MINUTOS_BLOQUEO = 15;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Ingresa tu usuario.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        $usuario = User::with('rol')
            ->where('username', $credentials['username'])
            ->first();

        if ($usuario?->bloqueado_hasta?->isFuture()) {
            throw ValidationException::withMessages([
                'username' => 'Tu cuenta está bloqueada temporalmente. Intenta de nuevo en '.
                    $usuario->bloqueado_hasta->diffForHumans().'.',
            ]);
        }

        if (! $usuario || ! Hash::check($credentials['password'], $usuario->getAuthPassword())) {
            $this->registrarFallo($usuario);

            throw ValidationException::withMessages([
                'password' => 'Las credenciales no son correctas.',
            ]);
        }

        if (! $usuario->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Tu cuenta está inactiva. Solicita soporte al administrador.',
            ]);
        }

        if (! $usuario->rol) {
            throw ValidationException::withMessages([
                'username' => 'Tu cuenta no tiene un rol asignado.',
            ]);
        }

        Auth::login($usuario);

        $usuario->forceFill([
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'ultimo_acceso' => now(),
        ])->save();

        BitacoraService::registrar(
            'usuarios',
            'INGRESO',
            "Inicio de sesión de {$usuario->username}.",
            $usuario->id_usuario,
            $usuario
        );

        $request->session()->regenerate();

        // El acceso siempre abre "Mi panel", aunque exista una URL intentada en la sesión.
        return redirect()->route('panel')
            ->with('status', 'Bienvenido al portal institucional.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Cerraste sesión correctamente.');
    }

    private function registrarFallo(?User $usuario): void
    {
        if (! $usuario) {
            return;
        }

        $intentos = $usuario->intentos_fallidos + 1;
        $datos = ['intentos_fallidos' => $intentos];

        if ($intentos >= self::MAX_INTENTOS) {
            $datos['bloqueado_hasta'] = now()->addMinutes(self::MINUTOS_BLOQUEO);
        }

        $usuario->forceFill($datos)->save();

        BitacoraService::registrar(
            'usuarios',
            'INGRESO_FALLIDO',
            "Intento fallido de inicio de sesión para {$usuario->username}.",
            $usuario->id_usuario,
            $usuario
        );
    }
}
