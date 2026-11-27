<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Recursamiento;
use App\Services\RecursamientoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RecursamientosController extends Controller
{
    public function __construct(private readonly RecursamientoService $recursamientos)
    {
    }

    private function docente(): Docente
    {
        $docente = Auth::user()->docente()->firstOrFail();
        abort_unless($docente->is_active, 403, 'Tu perfil docente está inactivo.');

        return $docente;
    }

    private function recursamiento(Docente $docente, Recursamiento $recursamiento): Recursamiento
    {
        abort_unless($recursamiento->id_docente === $docente->id_docente, 404);

        return $recursamiento;
    }

    public function index(Request $request)
    {
        $docente = $this->docente();
        $estatus = $request->input('estatus', 'pendiente');

        $recursamientos = Recursamiento::where('id_docente', $docente->id_docente)
            ->when($estatus !== '', fn ($query) => $query->where('estatus', $estatus))
            ->with([
                'alumno.usuario',
                'materia.semestre',
                'grupoOriginal.semestre',
                'plan',
            ])
            ->orderBy('ronda')
            ->orderBy('id_recursamiento')
            ->get();

        return view('docente.recursamientos.index', [
            'titulo' => 'Recursamientos (R)',
            'subtitulo' => 'Exámenes de recuperación asignados a tus materias.',
            'icono' => 'fa-rotate-left',
            'etiqueta' => 'Docente',
            'recursamientos' => $recursamientos,
            'estatus' => $estatus,
        ]);
    }

    public function show(Recursamiento $recursamiento)
    {
        $docente = $this->docente();
        $recursamiento = $this->recursamiento($docente, $recursamiento);

        $recursamiento->load([
            'alumno.usuario',
            'alumno.estado',
            'alumno.tutores',
            'materia.semestre',
            'materia.plan',
            'grupoOriginal.semestre',
            'docente',
            'plan',
        ]);

        $historial = Recursamiento::where('id_alumno', $recursamiento->id_alumno)
            ->where('id_materia', $recursamiento->id_materia)
            ->with('materia')
            ->orderBy('ronda')
            ->get();

        return view('docente.recursamientos.show', [
            'titulo' => 'Examen de '.$recursamiento->etiquetaRonda(),
            'subtitulo' => $recursamiento->alumno?->nombreCompleto().' · '.$recursamiento->materia?->nombre,
            'icono' => 'fa-file-signature',
            'etiqueta' => 'Docente',
            'recursamiento' => $recursamiento,
            'historial' => $historial,
            'maximo' => $this->recursamientos->porcentajeMaximo((int) $recursamiento->ronda),
        ]);
    }

    public function calificar(Request $request, Recursamiento $recursamiento)
    {
        $docente = $this->docente();
        $recursamiento = $this->recursamiento($docente, $recursamiento);
        $maximo = $this->recursamientos->porcentajeMaximo((int) $recursamiento->ronda);

        Validator::make($request->all(), [
            'porcentaje' => [
                'required',
                'numeric',
                'min:0',
                'max:'.$maximo,
            ],
        ], [
            'porcentaje.required' => 'Captura la calificación del examen.',
            'porcentaje.numeric' => 'La calificación debe ser un número.',
            'porcentaje.min' => 'La calificación no puede ser negativa.',
            'porcentaje.max' => "En {$recursamiento->etiquetaRonda()} la calificación máxima es {$maximo} %.",
        ])->validate();

        try {
            $this->recursamientos->calificar($recursamiento, (float) $request->input('porcentaje'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['porcentaje' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('docente.recursamientos.show', $recursamiento)
            ->with('status', 'Calificación de recuperación registrada correctamente.');
    }
}
