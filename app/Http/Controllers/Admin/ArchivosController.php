<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArchivosController extends Controller
{
    private const EXTENSIONES = 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,txt,csv,zip';

    private function rutaLimpia(?string $ruta): string
    {
        $ruta = str_replace('\\', '/', (string) $ruta);
        $segmentos = [];

        foreach (explode('/', $ruta) as $segmento) {
            $segmento = trim($segmento);
            if ($segmento === '' || $segmento === '.' || $segmento === '..') {
                continue;
            }
            $segmentos[] = $segmento;
        }

        return implode('/', $segmentos);
    }

    private function directorioSeguro(?string $ruta): string
    {
        $ruta = $this->rutaLimpia($ruta);

        if ($ruta !== '' && ! Storage::disk('local')->directoryExists($ruta)) {
            return '';
        }

        return $ruta;
    }

    private function archivoSeguro(?string $ruta): ?string
    {
        $ruta = $this->rutaLimpia($ruta);
        $disco = Storage::disk('local');

        if ($ruta === '' || ! $disco->fileExists($ruta)) {
            return null;
        }

        return $ruta;
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function contenido(string $ruta): array
    {
        $disco = Storage::disk('local');

        $directorios = collect($disco->directories($ruta))
            ->filter(fn (string $directorio) => ! Str::startsWith(basename($directorio), '.'))
            ->map(fn (string $directorio) => [
                'ruta' => $directorio,
                'nombre' => basename($directorio),
            ])
            ->sortBy('nombre')
            ->values();

        $archivos = collect($disco->files($ruta))
            ->filter(fn (string $archivo) => ! Str::startsWith(basename($archivo), '.'))
            ->map(fn (string $archivo) => [
                'ruta' => $archivo,
                'nombre' => basename($archivo),
                'tamano' => $disco->size($archivo),
                'modificado' => $disco->lastModified($archivo),
            ])
            ->sortBy('nombre')
            ->values();

        return [$directorios, $archivos];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function migas(string $ruta): array
    {
        $migas = [['nombre' => 'Documentos', 'ruta' => '']];
        $acumulado = '';

        foreach (explode('/', $ruta) as $segmento) {
            if ($segmento === '') {
                continue;
            }
            $acumulado = $acumulado === '' ? $segmento : $acumulado.'/'.$segmento;
            $migas[] = ['nombre' => $segmento, 'ruta' => $acumulado];
        }

        return $migas;
    }

    public function index(Request $request)
    {
        $ruta = $this->directorioSeguro($request->input('ruta'));
        [$directorios, $archivos] = $this->contenido($ruta);

        return view('archivos.index', [
            'titulo' => 'Gestor de archivos',
            'subtitulo' => 'Carpetas, boletas, certificados y documentos institucionales.',
            'icono' => 'fa-folder-open',
            'etiqueta' => 'Administrador',
            'ruta' => $ruta,
            'migas' => $this->migas($ruta),
            'directorios' => $directorios,
            'archivos' => $archivos,
        ]);
    }

    public function picker(Request $request)
    {
        $campo = (string) $request->input('campo', '');

        abort_unless(preg_match('/^[A-Za-z0-9_-]+$/', $campo) === 1, 422, 'Campo de destino inválido.');

        $ruta = $this->directorioSeguro($request->input('ruta'));
        [$directorios, $archivos] = $this->contenido($ruta);

        return view('archivos.picker', [
            'campo' => $campo,
            'ruta' => $ruta,
            'migas' => $this->migas($ruta),
            'directorios' => $directorios,
            'archivos' => $archivos,
        ]);
    }

    public function crearCarpeta(Request $request)
    {
        Validator::make($request->all(), [
            'ruta' => ['nullable', 'string', 'max:255'],
            'nombre' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9À-ÿ _-]+$/u'],
        ], [
            'nombre.required' => 'Escribe el nombre de la carpeta.',
            'nombre.regex' => 'El nombre solo puede tener letras, números, espacios, guiones y guiones bajos.',
        ])->validate();

        $nombre = trim((string) $request->input('nombre'));
        $carpeta = $this->directorioSeguro($request->input('ruta'));
        $nueva = $carpeta === '' ? $nombre : $carpeta.'/'.$nombre;

        if (Storage::disk('local')->exists($nueva)) {
            return back()->withErrors(['nombre' => 'Ya existe una carpeta con ese nombre.'])->withInput();
        }

        Storage::disk('local')->makeDirectory($nueva);

        BitacoraService::registrar('archivos', 'ALTA', 'Carpeta creada en el gestor de archivos: '.$nueva);

        return back()->with('status', 'Carpeta creada correctamente.');
    }

    public function subir(Request $request)
    {
        Validator::make($request->all(), [
            'ruta' => ['nullable', 'string', 'max:255'],
            'archivo' => ['required', 'file', 'max:10240', 'mimes:'.self::EXTENSIONES],
        ], [
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.file' => 'Selecciona un archivo válido.',
            'archivo.max' => 'El archivo no puede superar los 10 MB.',
            'archivo.mimes' => 'Formato no permitido. Usa PDF, Office, imágenes, texto o ZIP.',
        ])->validate();

        $carpeta = $this->directorioSeguro($request->input('ruta'));
        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $base = Str::of(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME))
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9_-]+/', '_')
            ->limit(60)
            ->toString();
        $nombre = trim($base, '_').'_'.date('Ymd_His').'_'.Str::random(5).'.'.$extension;
        $rutaCompleta = $carpeta === '' ? $nombre : $carpeta.'/'.$nombre;

        Storage::disk('local')->putFileAs($carpeta, $archivo, $nombre, 'private');

        BitacoraService::registrar('archivos', 'ALTA', 'Archivo cargado en el gestor: '.$rutaCompleta);

        return back()->with('status', 'Archivo cargado correctamente.');
    }

    public function descargar(Request $request)
    {
        $ruta = $this->archivoSeguro($request->input('archivo'));

        abort_unless($ruta !== null, 404);

        return Storage::disk('local')->download($ruta, basename($ruta));
    }

    public function eliminar(Request $request)
    {
        Validator::make($request->all(), [
            'tipo' => ['required', 'in:archivo,carpeta'],
            'ruta' => ['required', 'string', 'max:255'],
        ])->validate();

        $tipo = $request->input('tipo');
        $disco = Storage::disk('local');

        if ($tipo === 'archivo') {
            $ruta = $this->archivoSeguro($request->input('ruta'));
            abort_unless($ruta !== null, 404);

            $disco->delete($ruta);
            BitacoraService::registrar('archivos', 'BAJA', 'Archivo eliminado del gestor: '.$ruta);

            return back()->with('status', 'Archivo eliminado correctamente.');
        }

        $ruta = $this->rutaLimpia($request->input('ruta'));

        if ($ruta === '' || ! $disco->directoryExists($ruta)) {
            abort(404);
        }

        if (count($disco->allFiles($ruta)) > 0 || count($disco->allDirectories($ruta)) > 0) {
            return back()->withErrors(['ruta' => 'Solo se pueden eliminar carpetas vacías.']);
        }

        $disco->deleteDirectory($ruta);
        BitacoraService::registrar('archivos', 'BAJA', 'Carpeta eliminada del gestor: '.$ruta);

        return back()->with('status', 'Carpeta eliminada correctamente.');
    }
}
