<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\AceptacionCriterio;
use App\Models\Alumno;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\EstadoAlumno;
use App\Models\Evaluacion;
use App\Models\KardexMateria;
use App\Services\BitacoraService;
use App\Services\KardexService;
use App\Support\CalificacionService;
use App\Services\PlanEstudioPdfService;
use App\Support\HorariosPortal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MateriasController extends Controller
{
    private function alumno(): Alumno
    {
        $alumno = Auth::user()->alumno()->firstOrFail();
        abort_unless($alumno->id_estado_alumno !== EstadoAlumno::BAJA_DEFINITIVA, 403, 'Tu expediente no está activo.');

        return $alumno;
    }

    private function claseAutorizada(Clase $clase, Alumno $alumno): Clase
    {
        abort_unless($clase->id_grupo === $alumno->id_grupo && $clase->is_active, 404);
        abort_if((int) $alumno->id_estado_alumno === EstadoAlumno::GRADUADO, 404);

        $clase->loadMissing('materia.semestre');
        $numeroSemestre = (int) ($clase->materia?->semestre?->numero_semestre ?? 0);
        abort_if(app(KardexService::class)->semestreYaCursado($alumno, $numeroSemestre), 404);

        return $clase;
    }

    public function index()
    {
        $alumno = $this->alumno();

        $clases = Clase::where('id_grupo', $alumno->id_grupo)
            ->where('is_active', true)
            ->with([
                'materia.semestre',
                'grupo.semestre',
                'docente',
                'horarios.salon',
                'evaluaciones' => fn ($q) => $q->orderBy('parcial'),
            ])
            ->orderBy('id_clase')
            ->get();

        $semestreMax = (int) (KardexMateria::where('id_alumno', $alumno->id_alumno)->max('semestre') ?? 0);
        if ($semestreMax > 0) {
            $clases = $clases->filter(function (Clase $clase) use ($semestreMax) {
                return (int) ($clase->materia?->semestre?->numero_semestre ?? 0) > $semestreMax;
            })->values();
        }

        $asignaciones = [];
        foreach ($clases as $clase) {
            foreach ($clase->horarios as $horario) {
                $clave = HorariosPortal::claveCelda(
                    $horario->dia_semana,
                    $horario->hora_inicio,
                    $horario->hora_fin
                );
                $asignaciones[$clave] = [
                    'clase' => $clase,
                    'horario' => $horario,
                ];
            }
        }

        return view('alumno.materias.index', [
            'titulo' => 'Mis materias',
            'subtitulo' => 'Horario de tu grupo, criterios, fechas de evaluación y calificaciones publicadas.',
            'icono' => 'fa-book-open',
            'etiqueta' => 'Alumno',
            'alumno' => $alumno,
            'clases' => $clases,
            'dias' => HorariosPortal::DIAS,
            'bloques' => HorariosPortal::BLOQUES,
            'asignaciones' => $asignaciones,
        ]);
    }

    public function show(Clase $clase)
    {
        $alumno = $this->alumno();
        $clase = $this->claseAutorizada($clase, $alumno);

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
            $aceptacion = $aceptaciones->get($evaluacion->id_evaluacion);

            return array_merge(
                [
                    'evaluacion' => $evaluacion,
                    'aceptacion' => $aceptacion,
                ],
                CalificacionService::resumenEvaluacion($evaluacion, $alumno, $aceptacion)
            );
        });

        $calificacionFinal = CalificacionService::calificacionFinal($parciales);

        $asistencias = $clase->asistencias()
            ->where('id_alumno', $alumno->id_alumno)
            ->orderByDesc('fecha')
            ->get();

        return view('alumno.materias.show', [
            'titulo' => $clase->materia->nombre,
            'subtitulo' => $clase->materia->clave.' · '.$clase->docenteNombre(),
            'icono' => 'fa-book-open',
            'etiqueta' => 'Alumno',
            'alumno' => $alumno,
            'clase' => $clase,
            'parciales' => $parciales,
            'calificacionFinal' => $calificacionFinal,
            'asistencias' => $asistencias,
        ]);
    }

    public function aceptarCriterios(Request $request, Clase $clase, Evaluacion $evaluacion)
    {
        $alumno = $this->alumno();
        $clase = $this->claseAutorizada($clase, $alumno);
        abort_unless($evaluacion->id_clase === $clase->id_clase, 404);

        abort_if($evaluacion->criterios()->count() === 0, 422, 'Esta evaluación aún no tiene criterios publicados.');

        $request->validate([
            'acepto' => ['required', 'accepted'],
        ], [
            'acepto.required' => 'Debes aceptar los criterios de evaluación.',
            'acepto.accepted' => 'Debes aceptar los criterios de evaluación.',
        ]);

        if ($evaluacion->aceptaciones()->where('id_alumno', $alumno->id_alumno)->exists()) {
            return back()->with('status', 'Ya aceptaste los criterios de esta evaluación.');
        }

        $criterios = $evaluacion->criterios()
            ->orderBy('id_criterio')
            ->get()
            ->map(fn (CriterioEvaluacion $criterio) => CalificacionService::criterioArreglo($criterio))
            ->all();

        DB::transaction(function () use ($alumno, $clase, $evaluacion, $criterios) {
            AceptacionCriterio::create([
                'id_alumno' => $alumno->id_alumno,
                'id_clase' => $clase->id_clase,
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'criterios' => $criterios,
                'fecha_aceptacion' => now(),
            ]);

            BitacoraService::registrar(
                'aceptaciones_criterios',
                'ALTA',
                'El alumno aceptó los criterios de '.$evaluacion->etiqueta().'.',
                $evaluacion->id_evaluacion
            );
        });

        return back()->with('status', 'Aceptaste los criterios de evaluación. Ya puedes consultar tus calificaciones.');
    }

    public function programaPdf(Clase $clase)
    {
        $alumno = $this->alumno();
        $clase = $this->claseAutorizada($clase, $alumno);

        $ruta = $this->rutaPublicaSegura($clase->materia->programa_pdf_ruta);
        $nombre = 'programa-'.$clase->materia->clave.'.pdf';

        if ($ruta !== null && Storage::disk('public')->exists($ruta)) {
            return Storage::disk('public')->response(
                $ruta,
                $nombre,
                ['Content-Type' => 'application/pdf']
            );
        }

        $pdf = app(PlanEstudioPdfService::class)->generar(
            $clase->loadMissing(['materia.plan', 'materia.semestre', 'grupo.semestre', 'docente'])
        );

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$nombre.'"');
    }

    private function rutaPublicaSegura(?string $ruta): ?string
    {
        $ruta = trim((string) $ruta);
        if ($ruta === '') {
            return null;
        }

        $ruta = str_replace('\\', '/', $ruta);
        if (str_starts_with($ruta, '/') || preg_match('/(^|\/)\.\.(\/|$)/', $ruta)) {
            return null;
        }

        return $ruta;
    }
}
