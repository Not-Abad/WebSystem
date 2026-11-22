<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    /** @var array<int, string> */
    private const DIAS = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

    /** @var array<int, array{inicio: string, fin: string, tipo: string, etiqueta?: string}> */
    private const BLOQUES = [
        ['inicio' => '15:00:00', 'fin' => '15:50:00', 'tipo' => 'clase'],
        ['inicio' => '15:50:00', 'fin' => '16:40:00', 'tipo' => 'clase'],
        ['inicio' => '16:40:00', 'fin' => '17:30:00', 'tipo' => 'clase'],
        ['inicio' => '17:30:00', 'fin' => '17:50:00', 'tipo' => 'receso', 'etiqueta' => 'Receso 20 minutos'],
        ['inicio' => '17:50:00', 'fin' => '18:40:00', 'tipo' => 'clase'],
        ['inicio' => '18:40:00', 'fin' => '19:30:00', 'tipo' => 'clase'],
        ['inicio' => '19:30:00', 'fin' => '19:40:00', 'tipo' => 'receso', 'etiqueta' => 'Receso 10 minutos'],
        ['inicio' => '19:40:00', 'fin' => '20:30:00', 'tipo' => 'clase'],
        ['inicio' => '20:30:00', 'fin' => '21:20:00', 'tipo' => 'clase'],
    ];

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
        return $dia.'|'.$inicio.'|'.$fin;
    }
}
