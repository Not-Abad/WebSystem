<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Clase;
use App\Models\EstadoAlumno;
use Illuminate\Support\Facades\Auth;

class MateriasController extends Controller
{
    private function alumno(): Alumno
    {
        $alumno = Auth::user()->alumno()->firstOrFail();
        abort_unless($alumno->id_estado_alumno !== EstadoAlumno::BAJA_DEFINITIVA, 403, 'Tu expediente no está activo.');

        return $alumno;
    }

    public function index()
    {
        $alumno = $this->alumno();

        $clases = Clase::where('id_grupo', $alumno->id_grupo)
            ->where('is_active', true)
            ->with([
                'materia',
                'grupo.semestre',
                'horarios.salon',
                'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
            ])
            ->orderBy('id_clase')
            ->get();

        return view('alumno.materias.index', [
            'titulo' => 'Mis materias',
            'subtitulo' => 'Criterios, fechas de evaluación y calificaciones publicadas por tus docentes.',
            'icono' => 'fa-book-open',
            'etiqueta' => 'Alumno',
            'alumno' => $alumno,
            'clases' => $clases,
        ]);
    }

    public function show(Clase $clase)
    {
        $alumno = $this->alumno();
        abort_unless($clase->id_grupo === $alumno->id_grupo && $clase->is_active, 404);

        $clase->load([
            'materia',
            'grupo.semestre',
            'docente',
            'horarios.salon',
            'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
            'evaluaciones.criterios' => fn ($q) => $q->orderBy('id_criterio'),
            'evaluaciones.criterios.calificaciones' => fn ($q) => $q->where('id_alumno', $alumno->id_alumno),
        ]);

        $asistencias = $clase->asistencias()
            ->where('id_alumno', $alumno->id_alumno)
            ->orderByDesc('fecha')
            ->get();

        return view('alumno.materias.show', [
            'titulo' => $clase->materia->nombre,
            'subtitulo' => $clase->materia->clave.' · '.$clase->docente->nombreCompleto(),
            'icono' => 'fa-book-open',
            'etiqueta' => 'Alumno',
            'alumno' => $alumno,
            'clase' => $clase,
            'asistencias' => $asistencias,
        ]);
    }
}
