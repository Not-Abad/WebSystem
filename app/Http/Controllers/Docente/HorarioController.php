<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Support\HorariosPortal;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    /** @var array<int, string> */
    private const DIAS = HorariosPortal::DIAS;

    /** @var array<int, array{inicio: string, fin: string, tipo: string, etiqueta?: string}> */
    private const BLOQUES = HorariosPortal::BLOQUES;

    public function index()
    {
        $docente = Auth::user()->docente()->firstOrFail();

        $clases = $docente->clases()
            ->with([
                'materia',
                'grupo.semestre',
                'horarios.salon',
                'evaluaciones',
                'reincoExportaciones',
            ])
            ->orderBy('id_clase')
            ->get();

        $asignaciones = [];
        foreach ($clases as $clase) {
            foreach ($clase->horarios as $horario) {
                $clave = $horario->dia_semana.'|'.$horario->hora_inicio.'|'.$horario->hora_fin;
                $asignaciones[$clave] = [
                    'clase' => $clase,
                    'horario' => $horario,
                ];
            }
        }

        return view('docente.horario.index', [
            'titulo' => 'Mi horario',
            'subtitulo' => 'Selecciona una materia para ver el grupo, los alumnos y su evaluación.',
            'icono' => 'fa-clock',
            'etiqueta' => 'Docente',
            'docente' => $docente,
            'clases' => $clases,
            'dias' => self::DIAS,
            'bloques' => self::BLOQUES,
            'asignaciones' => $asignaciones,
            'totalBloques' => $docente->clases()->withCount('horarios')->get()->sum('horarios_count'),
        ]);
    }

    public static function claveCelda(string $dia, string $inicio, string $fin): string
    {
        return HorariosPortal::claveCelda($dia, $inicio, $fin);
    }
}
