<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\AceptacionCriterio;
use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Evaluacion;
use App\Models\Tutor;
use App\Support\CalificacionService;
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

    private function alumnoAutorizado(Alumno $alumno, Tutor $tutor): Alumno
    {
        abort_unless($alumno->id_tutor === $tutor->id_tutor, 404);

        return $alumno;
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
        $alumno = $this->alumnoAutorizado($alumno, $tutor);

        $alumno->load(['grupo', 'estado', 'plan', 'semestre', 'capacitacion', 'area', 'tutor']);

        $clases = Clase::where('id_grupo', $alumno->id_grupo)
            ->where('is_active', true)
            ->with([
                'materia',
                'docente',
                'grupo.semestre',
                'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
            ])
            ->orderBy('id_clase')
            ->get();

        return view('tutor.alumnos.show', [
            'titulo' => 'Expediente de '.$alumno->nombre,
            'subtitulo' => 'Información académica del estudiante bajo tu tutela.',
            'icono' => 'fa-address-card',
            'etiqueta' => 'Tutor',
            'alumno' => $alumno,
            'clases' => $clases,
        ]);
    }

    public function materia(Alumno $alumno, Clase $clase)
    {
        $tutor = $this->tutorAutenticado();
        $alumno = $this->alumnoAutorizado($alumno, $tutor);

        abort_unless($clase->id_grupo === $alumno->id_grupo && $clase->is_active, 404);

        $clase->load([
            'materia.plan',
            'materia.semestre',
            'grupo.semestre',
            'docente',
            'horarios.salon',
            'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
            'evaluaciones.criterios' => fn ($q) => $q->orderBy('id_criterio'),
            'evaluaciones.criterios.calificaciones' => fn ($q) => $q->where('id_alumno', $alumno->id_alumno),
        ]);

        $aceptaciones = AceptacionCriterio::query()
            ->where('id_alumno', $alumno->id_alumno)
            ->whereIn('id_evaluacion', $clase->evaluaciones->pluck('id_evaluacion'))
            ->get()
            ->keyBy('id_evaluacion');

        $parciales = $clase->evaluaciones->map(function (Evaluacion $evaluacion) use ($alumno, $aceptaciones) {
            return array_merge(
                ['evaluacion' => $evaluacion],
                CalificacionService::resumenEvaluacion(
                    $evaluacion,
                    $alumno,
                    $aceptaciones->get($evaluacion->id_evaluacion)
                )
            );
        });

        $calificacionFinal = CalificacionService::calificacionFinal($parciales, false);

        return view('tutor.alumnos.materia', [
            'titulo' => $clase->materia->nombre,
            'subtitulo' => 'Seguimiento de '.$alumno->nombreCompleto().' · '.$clase->materia->clave,
            'icono' => 'fa-book-open',
            'etiqueta' => 'Tutor',
            'tutor' => $tutor,
            'alumno' => $alumno,
            'clase' => $clase,
            'parciales' => $parciales,
            'calificacionFinal' => $calificacionFinal,
        ]);
    }
}
