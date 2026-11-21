<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Permite el paso solo a los roles indicados.
     *
     * Uso: ->middleware('role:SuperUsuario,Administrador')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        $roles = collect($roles)
            ->flatMap(fn (string $rol) => explode(',', $rol))
            ->map(fn (string $rol) => trim($rol))
            ->filter()
            ->all();

        foreach ($roles as $rol) {
            if ($usuario->esRol($rol)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
