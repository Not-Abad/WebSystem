<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\EstadoAlumno;
use App\Models\KardexMateria;
use App\Services\KardexService;
use Illuminate\Support\Facades\Auth;

class KardexController extends Controller
{
    public function index(KardexService $kardex)
    {
        $alumno = Auth::user()->alumno()->firstOrFail();
        abort_unless($alumno->id_estado_alumno !== EstadoAlumno::BAJA_DEFINITIVA, 403, 'Tu expediente no está activo.');

        $reticula = $kardex->reticula($alumno);
        $graduado = (int) $alumno->id_estado_alumno === EstadoAlumno::GRADUADO;

        $cursadas = $reticula->whereIn('estatus', KardexMateria::estatusAprobados());
        $notas = $cursadas->pluck('calificacion_final')->filter(fn ($nota) => $nota !== null);
        $recursamientos = $alumno->recursamientos()
            ->with('materia')
            ->orderBy('id_materia')
            ->orderBy('ronda')
            ->get();

        return view('alumno.kardex.index', [
            'titulo' => 'Kardex escolar',
            'subtitulo' => 'Retícula del plan de estudios y progreso acumulado.',
            'icono' => 'fa-file-archive',
            'etiqueta' => 'Alumno',
            'alumno' => $alumno,
            'reticula' => $reticula,
            'recursamientos' => $recursamientos,
            'graduado' => $graduado,
            'promedioFinal' => $graduado ? (float) $alumno->promedio_general : null,
            'promedioParcial' => $notas->isNotEmpty() ? round((float) $notas->avg(), 2) : null,
            'totales' => [
                'cursadas' => $cursadas->count(),
                'en_curso' => $reticula->where('estatus', 'en_curso')->count(),
                'pendientes' => $reticula->where('estatus', 'pendiente')->count(),
            ],
        ]);
    }
}
