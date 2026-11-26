<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    private function tutorAutenticado(): Tutor
    {
        $tutor = Auth::user()?->tutor;

        if (! $tutor) {
            abort(403, 'Tu cuenta no tiene un perfil de tutor.');
        }

        return $tutor;
    }

    public function index()
    {
        $tutor = $this->tutorAutenticado()->load('usuario');

        return view('tutor.perfil.index', [
            'titulo' => 'Mis datos',
            'subtitulo' => 'Consulta tu información personal y de contacto como tutor.',
            'icono' => 'fa-id-card',
            'etiqueta' => 'Tutor',
            'tutor' => $tutor,
            'totalAlumnos' => $tutor->alumnos()->count(),
            'iniciales' => $this->iniciales($this->nombreCompleto($tutor)),
        ]);
    }

    private function nombreCompleto(Tutor $tutor): string
    {
        return trim($tutor->nombre.' '.$tutor->apellido_paterno.' '.$tutor->apellido_materno);
    }

    private function iniciales(string $nombre): string
    {
        return collect(preg_split('/\s+/', trim($nombre)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($palabra) => mb_strtoupper(mb_substr($palabra, 0, 1)))
            ->implode('');
    }
}
