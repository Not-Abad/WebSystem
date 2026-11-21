<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlumnosController extends Controller
{
    private function tutorAutenticado(): Tutor
    {
        $tutor = Auth::user()?->tutor;

        if (! $tutor) {
            abort(403, 'Tu cuenta no tiene un perfil de tutor.');
        }

        return $tutor;
    }

    public function index(Request $request)
    {
        $tutor = $this->tutorAutenticado();
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $alumnos = Alumno::where('id_tutor', $tutor->id_tutor)
            ->with(['estado', 'plan', 'semestre'])
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('correo', 'like', $like)
                        ->orWhere('curp', 'like', $like);
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('tutor.alumnos.index', [
            'titulo' => 'Mis alumnos',
            'subtitulo' => 'Estudiantes bajo tu tutela: '.$tutor->nombre.' '.$tutor->apellido_paterno.'.',
            'icono' => 'fa-users',
            'etiqueta' => 'Tutor',
            'alumnos' => $alumnos,
            'busqueda' => $busqueda,
        ]);
    }

    public function show(Alumno $alumno)
    {
        $tutor = $this->tutorAutenticado();

        abort_unless($alumno->id_tutor === $tutor->id_tutor, 404);

        $alumno->load(['grupo', 'estado', 'plan', 'semestre', 'capacitacion', 'area']);

        return view('tutor.alumnos.show', [
            'titulo' => 'Expediente de '.$alumno->nombre,
            'subtitulo' => 'Información académica del estudiante bajo tu tutela.',
            'icono' => 'fa-address-card',
            'etiqueta' => 'Tutor',
            'alumno' => $alumno,
        ]);
    }
}
