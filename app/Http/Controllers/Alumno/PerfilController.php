<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\EstadoAlumno;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    private function alumno(): Alumno
    {
        $alumno = Auth::user()->alumno()->firstOrFail();
        abort_unless($alumno->id_estado_alumno !== EstadoAlumno::BAJA_DEFINITIVA, 403, 'Tu expediente no está activo.');

        return $alumno;
    }

    public function index()
    {
        $alumno = $this->alumno()->load([
            'usuario',
            'estado',
            'grupo.semestre',
            'grupo.plan',
            'plan',
            'semestre',
            'tutor',
            'capacitacion',
            'area',
        ]);

        return view('alumno.perfil.index', [
            'titulo' => 'Mis datos',
            'subtitulo' => 'Consulta tu expediente y tus datos escolares.',
            'icono' => 'fa-id-card',
            'etiqueta' => 'Alumno',
            'alumno' => $alumno,
            'iniciales' => $this->iniciales($alumno->nombreCompleto()),
        ]);
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
