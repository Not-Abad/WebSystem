<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Documento;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Noticia;
use App\Models\ReporteFinanciero;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Contenido público del portal: portada, avisos y documentos descargables.
 * Solo publica registros activos y nunca expone rutas del gestor interno.
 */
final class PortalPublicoController extends Controller
{
    private const ESTADO_ALUMNO_ACTIVO = 2;

    /** Categorías permitidas por el módulo administrativo de documentos. */
    private const CATEGORIAS = ['Convocatorias', 'Formatos', 'Manuales', 'Normatividad', 'Recursos'];

    public function index()
    {
        $avisos = Noticia::query()
            ->where('is_active', true)
            ->orderByDesc('fecha_publicacion')
            ->limit(3)
            ->get();

        $documentos = $this->documentosActivos()
            ->orderByDesc('fecha_subida')
            ->limit(5)
            ->get()
            ->map(fn (Documento $documento) => $this->documentoArreglo($documento));

        $cifras = [
            ['numero' => Alumno::where('id_estado_alumno', self::ESTADO_ALUMNO_ACTIVO)->count(), 'etiqueta' => 'Estudiantes activos', 'icono' => 'fa-users'],
            ['numero' => Docente::where('is_active', true)->count(), 'etiqueta' => 'Docentes', 'icono' => 'fa-chalkboard-user'],
            ['numero' => Tutor::where('is_active', true)->count(), 'etiqueta' => 'Tutores', 'icono' => 'fa-user-tie'],
            ['numero' => Grupo::count(), 'etiqueta' => 'Grupos escolares', 'icono' => 'fa-school'],
            ['numero' => Materia::count(), 'etiqueta' => 'Unidades de aprendizaje', 'icono' => 'fa-book-open'],
        ];

        return view('index', [
            'avisos' => $avisos,
            'documentos' => $documentos,
            'cifras' => $cifras,
        ]);
    }

    public function avisos(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $avisos = Noticia::query()
            ->where('is_active', true)
            ->when($busqueda !== '', function ($query) use ($like) {
                $query->where(function ($subconsulta) use ($like) {
                    $subconsulta->where('titulo', 'like', $like)
                        ->orWhere('contenido', 'like', $like);
                });
            })
            ->orderByDesc('fecha_publicacion')
            ->paginate(9)
            ->withQueryString();

        return view('avisos', [
            'avisos' => $avisos,
            'busqueda' => $busqueda,
        ]);
    }

    public function aviso(Noticia $noticia)
    {
        abort_unless($noticia->is_active, 404);

        return view('aviso', ['aviso' => $noticia]);
    }

    public function documentos(Request $request)
    {
        $categoria = trim((string) $request->input('categoria', ''));
        abort_unless($categoria === '' || in_array($categoria, self::CATEGORIAS, true), 404);

        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $documentos = $this->documentosActivos()
            ->when($categoria !== '', fn ($query) => $query->where('categoria', $categoria))
            ->when($busqueda !== '', fn ($query) => $query->where('titulo', 'like', $like))
            ->orderByDesc('fecha_subida')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Documento $documento) => $this->documentoArreglo($documento));

        $reportes = ReporteFinanciero::query()
            ->where('is_active', true)
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->get()
            ->map(fn (ReporteFinanciero $reporte) => [
                'id_reporte' => (int) $reporte->id_reporte,
                'titulo' => $reporte->periodo(),
                'fecha_publicacion' => $reporte->fecha_publicacion,
                'nombre_archivo' => basename(str_replace('\\', '/', (string) $reporte->archivo_pdf_ruta)),
                'disponible' => $this->rutaSegura($reporte->archivo_pdf_ruta) !== null,
                'url' => route('documentos.reporte.descargar', $reporte),
            ]);

        return view('documentos', [
            'documentos' => $documentos,
            'reportes' => $reportes,
            'categorias' => self::CATEGORIAS,
            'categoria' => $categoria,
            'busqueda' => $busqueda,
        ]);
    }

    public function descargarDocumento(Documento $documento)
    {
        abort_unless($documento->is_active, 404);

        $ruta = $this->rutaSegura($documento->archivo_ruta);
        abort_unless($ruta !== null, 404);

        return Storage::disk('local')->download($ruta, basename($ruta));
    }

    public function descargarReporte(ReporteFinanciero $reporte)
    {
        abort_unless($reporte->is_active, 404);

        $ruta = $this->rutaSegura($reporte->archivo_pdf_ruta);
        abort_unless($ruta !== null, 404);

        return Storage::disk('local')->download($ruta, basename($ruta));
    }

    private function documentosActivos()
    {
        return Documento::query()->where('is_active', true);
    }

    /** @return array<string, mixed> */
    private function documentoArreglo(Documento $documento): array
    {
        return [
            'id_documento' => (int) $documento->id_documento,
            'titulo' => (string) $documento->titulo,
            'categoria' => (string) $documento->categoria,
            'fecha_subida' => $documento->fecha_subida,
            'nombre_archivo' => basename(str_replace('\\', '/', (string) $documento->archivo_ruta)),
            'disponible' => $this->rutaSegura($documento->archivo_ruta) !== null,
            'url' => route('documentos.descargar', $documento),
        ];
    }

    private function rutaSegura(?string $ruta): ?string
    {
        $segmentos = [];

        foreach (explode('/', str_replace('\\', '/', (string) $ruta)) as $segmento) {
            $segmento = trim($segmento);
            if ($segmento === '' || $segmento === '.' || $segmento === '..') {
                continue;
            }
            $segmentos[] = $segmento;
        }

        $limpia = implode('/', $segmentos);

        return $limpia !== '' && Storage::disk('local')->fileExists($limpia) ? $limpia : null;
    }
}
