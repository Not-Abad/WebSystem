<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\ImagenGaleria;
use App\Models\Noticia;
use App\Models\ReporteFinanciero;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PortalController extends Controller
{
    public function noticias(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $noticias = Noticia::query()
            ->when($busqueda !== '', fn ($query) => $query->where('titulo', 'like', $like)->orWhere('contenido', 'like', $like))
            ->orderByDesc('fecha_publicacion')
            ->paginate(20)
            ->withQueryString();

        return view('admin.portal.noticias', [
            'titulo' => 'Noticias del portal',
            'subtitulo' => 'Gestión de las publicaciones visibles en la página principal.',
            'icono' => 'fa-newspaper',
            'etiqueta' => 'Administrador',
            'noticias' => $noticias,
            'busqueda' => $busqueda,
        ]);
    }

    public function crearNoticia()
    {
        return $this->formNoticia(new Noticia(), 'Nueva noticia');
    }

    public function editarNoticia(Noticia $noticia)
    {
        return $this->formNoticia($noticia, 'Editar noticia');
    }

    public function guardarNoticia(Request $request)
    {
        Validator::make($request->all(), [
            'titulo' => ['required', 'string', 'max:160'],
            'contenido' => ['required', 'string', 'min:20'],
            'imagen_portada_ruta' => ['nullable', 'string', 'max:255'],
            'fecha_publicacion' => ['required', 'date'],
            'is_active' => ['boolean'],
        ], [
            'titulo.required' => 'Ingresa el título de la noticia.',
            'contenido.required' => 'Ingresa el contenido de la noticia.',
            'contenido.min' => 'El contenido debe tener al menos 20 caracteres.',
            'fecha_publicacion.required' => 'Ingresa la fecha de publicación.',
            'fecha_publicacion.date' => 'Ingresa una fecha válida.',
        ])->validate();

        DB::transaction(function () use ($request) {
            $noticia = Noticia::create([
                'id_administrador' => $this->idAdministrador(),
                'titulo' => $request->input('titulo'),
                'contenido' => $request->input('contenido'),
                'imagen_portada_ruta' => $request->input('imagen_portada_ruta') ?: null,
                'fecha_publicacion' => $request->input('fecha_publicacion'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('noticias', 'ALTA', "Alta de Noticia: {$noticia->titulo}.", $noticia->id_noticia);
        });

        return redirect()->route('admin.portal.noticias')->with('status', 'Noticia creada correctamente.');
    }

    public function actualizarNoticia(Request $request, Noticia $noticia)
    {
        Validator::make($request->all(), [
            'titulo' => ['required', 'string', 'max:160'],
            'contenido' => ['required', 'string', 'min:20'],
            'imagen_portada_ruta' => ['nullable', 'string', 'max:255'],
            'fecha_publicacion' => ['required', 'date'],
            'is_active' => ['boolean'],
        ])->validate();

        DB::transaction(function () use ($request, $noticia) {
            $noticia->update([
                'titulo' => $request->input('titulo'),
                'contenido' => $request->input('contenido'),
                'imagen_portada_ruta' => $request->input('imagen_portada_ruta') ?: null,
                'fecha_publicacion' => $request->input('fecha_publicacion'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('noticias', $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION', "Actualización de Noticia: {$noticia->titulo}.", $noticia->id_noticia);
        });

        return redirect()->route('admin.portal.noticias')->with('status', 'Noticia actualizada correctamente.');
    }

    public function eliminarNoticia(Noticia $noticia)
    {
        return $this->bajaLogica($noticia, 'noticias', 'Noticia', $noticia->titulo, 'admin.portal.noticias');
    }

    public function documentos(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $documentos = Documento::query()
            ->when($busqueda !== '', fn ($query) => $query->where('titulo', 'like', $like)->orWhere('categoria', 'like', $like))
            ->orderByDesc('fecha_subida')
            ->paginate(20)
            ->withQueryString();

        return view('admin.portal.documentos', [
            'titulo' => 'Documentos del portal',
            'subtitulo' => 'Publicación de formatos, convocatorias y materiales descargables.',
            'icono' => 'fa-file-lines',
            'etiqueta' => 'Administrador',
            'documentos' => $documentos,
            'busqueda' => $busqueda,
        ]);
    }

    public function crearDocumento()
    {
        return view('admin.portal.documento-form', [
            'titulo' => 'Nuevo documento',
            'subtitulo' => 'Registro de un archivo publicado en el portal.',
            'icono' => 'fa-file-circle-plus',
            'etiqueta' => 'Administrador',
            'documento' => new Documento(),
            'accion' => route('admin.portal.documentos.guardar'),
            'metodo' => 'POST',
            'categorias' => $this->categorias(),
        ]);
    }

    public function editarDocumento(Documento $documento)
    {
        return view('admin.portal.documento-form', [
            'titulo' => 'Editar documento',
            'subtitulo' => 'Actualización de los datos del archivo publicado.',
            'icono' => 'fa-pen',
            'etiqueta' => 'Administrador',
            'documento' => $documento,
            'accion' => route('admin.portal.documentos.actualizar', $documento),
            'metodo' => 'PUT',
            'categorias' => $this->categorias(),
        ]);
    }

    public function guardarDocumento(Request $request)
    {
        Validator::make($request->all(), $this->reglasDocumento(), $this->mensajesDocumento())->validate();

        DB::transaction(function () use ($request) {
            $documento = Documento::create([
                'id_administrador' => $this->idAdministrador(),
                'titulo' => $request->input('titulo'),
                'categoria' => $request->input('categoria'),
                'archivo_ruta' => $request->input('archivo_ruta'),
                'fecha_subida' => $request->input('fecha_subida'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('documentos', 'ALTA', "Alta de Documento: {$documento->titulo}.", $documento->id_documento);
        });

        return redirect()->route('admin.portal.documentos')->with('status', 'Documento creado correctamente.');
    }

    public function actualizarDocumento(Request $request, Documento $documento)
    {
        Validator::make($request->all(), $this->reglasDocumento(), $this->mensajesDocumento())->validate();

        DB::transaction(function () use ($request, $documento) {
            $documento->update([
                'titulo' => $request->input('titulo'),
                'categoria' => $request->input('categoria'),
                'archivo_ruta' => $request->input('archivo_ruta'),
                'fecha_subida' => $request->input('fecha_subida'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('documentos', $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION', "Actualización de Documento: {$documento->titulo}.", $documento->id_documento);
        });

        return redirect()->route('admin.portal.documentos')->with('status', 'Documento actualizado correctamente.');
    }

    public function eliminarDocumento(Documento $documento)
    {
        return $this->bajaLogica($documento, 'documentos', 'Documento', $documento->titulo, 'admin.portal.documentos');
    }

    public function reportes(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $reportes = ReporteFinanciero::query()
            ->when($busqueda !== '', fn ($query) => $query->where('titulo', 'like', $like))
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->paginate(20)
            ->withQueryString();

        return view('admin.portal.reportes', [
            'titulo' => 'Reportes financieros',
            'subtitulo' => 'Publicación mensual de los reportes financieros del plantel.',
            'icono' => 'fa-chart-column',
            'etiqueta' => 'Administrador',
            'reportes' => $reportes,
            'busqueda' => $busqueda,
        ]);
    }

    public function crearReporte()
    {
        return $this->formReporte(new ReporteFinanciero(), 'Nuevo reporte');
    }

    public function editarReporte(ReporteFinanciero $reporte)
    {
        return $this->formReporte($reporte, 'Editar reporte');
    }

    public function guardarReporte(Request $request)
    {
        Validator::make($request->all(), $this->reglasReporte(), $this->mensajesReporte())->validate();

        DB::transaction(function () use ($request) {
            $reporte = ReporteFinanciero::create([
                'id_administrador' => $this->idAdministrador(),
                'titulo' => $request->input('titulo'),
                'mes' => $request->input('mes'),
                'anio' => $request->input('anio'),
                'archivo_pdf_ruta' => $request->input('archivo_pdf_ruta'),
                'fecha_publicacion' => $request->input('fecha_publicacion'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('reportes_financieros', 'ALTA', "Alta de Reporte financiero: {$reporte->periodo()}.", $reporte->id_reporte);
        });

        return redirect()->route('admin.portal.reportes')->with('status', 'Reporte creado correctamente.');
    }

    public function actualizarReporte(Request $request, ReporteFinanciero $reporte)
    {
        Validator::make($request->all(), $this->reglasReporte($reporte), $this->mensajesReporte())->validate();

        DB::transaction(function () use ($request, $reporte) {
            $reporte->update([
                'titulo' => $request->input('titulo'),
                'mes' => $request->input('mes'),
                'anio' => $request->input('anio'),
                'archivo_pdf_ruta' => $request->input('archivo_pdf_ruta'),
                'fecha_publicacion' => $request->input('fecha_publicacion'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('reportes_financieros', $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION', "Actualización de Reporte financiero: {$reporte->periodo()}.", $reporte->id_reporte);
        });

        return redirect()->route('admin.portal.reportes')->with('status', 'Reporte actualizado correctamente.');
    }

    public function eliminarReporte(ReporteFinanciero $reporte)
    {
        return $this->bajaLogica($reporte, 'reportes_financieros', 'Reporte financiero', $reporte->periodo(), 'admin.portal.reportes');
    }

    public function galeria(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $like = '%'.addcslashes($busqueda, '%_\\').'%';

        $imagenes = ImagenGaleria::query()
            ->when($busqueda !== '', fn ($query) => $query->where('titulo', 'like', $like)->orWhere('descripcion', 'like', $like))
            ->orderByDesc('fecha_subida')
            ->paginate(20)
            ->withQueryString();

        return view('admin.portal.galeria', [
            'titulo' => 'Galería de imágenes',
            'subtitulo' => 'Administración de las imágenes publicadas en el portal.',
            'icono' => 'fa-images',
            'etiqueta' => 'Administrador',
            'imagenes' => $imagenes,
            'busqueda' => $busqueda,
        ]);
    }

    public function crearImagen()
    {
        return view('admin.portal.imagen-form', [
            'titulo' => 'Nueva imagen',
            'subtitulo' => 'Registro de una imagen publicada en la galería.',
            'icono' => 'fa-image',
            'etiqueta' => 'Administrador',
            'imagen' => new ImagenGaleria(),
            'accion' => route('admin.portal.galeria.guardar'),
            'metodo' => 'POST',
        ]);
    }

    public function editarImagen(ImagenGaleria $imagen)
    {
        return view('admin.portal.imagen-form', [
            'titulo' => 'Editar imagen',
            'subtitulo' => 'Actualización de los datos de la imagen publicada.',
            'icono' => 'fa-pen',
            'etiqueta' => 'Administrador',
            'imagen' => $imagen,
            'accion' => route('admin.portal.galeria.actualizar', $imagen),
            'metodo' => 'PUT',
        ]);
    }

    public function guardarImagen(Request $request)
    {
        Validator::make($request->all(), $this->reglasImagen(), $this->mensajesImagen())->validate();

        DB::transaction(function () use ($request) {
            $imagen = ImagenGaleria::create([
                'id_administrador' => $this->idAdministrador(),
                'titulo' => $request->input('titulo'),
                'descripcion' => $request->input('descripcion') ?: null,
                'imagen_ruta' => $request->input('imagen_ruta'),
                'fecha_subida' => $request->input('fecha_subida'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('imagenes_galeria', 'ALTA', "Alta de Imagen: {$imagen->titulo}.", $imagen->id_imagen);
        });

        return redirect()->route('admin.portal.galeria')->with('status', 'Imagen creada correctamente.');
    }

    public function actualizarImagen(Request $request, ImagenGaleria $imagen)
    {
        Validator::make($request->all(), $this->reglasImagen(), $this->mensajesImagen())->validate();

        DB::transaction(function () use ($request, $imagen) {
            $imagen->update([
                'titulo' => $request->input('titulo'),
                'descripcion' => $request->input('descripcion') ?: null,
                'imagen_ruta' => $request->input('imagen_ruta'),
                'fecha_subida' => $request->input('fecha_subida'),
                'is_active' => $request->boolean('is_active'),
            ]);

            BitacoraService::registrar('imagenes_galeria', $request->boolean('is_active') ? 'EDICION' : 'SUSPENSION', "Actualización de Imagen: {$imagen->titulo}.", $imagen->id_imagen);
        });

        return redirect()->route('admin.portal.galeria')->with('status', 'Imagen actualizada correctamente.');
    }

    public function eliminarImagen(ImagenGaleria $imagen)
    {
        return $this->bajaLogica($imagen, 'imagenes_galeria', 'Imagen', $imagen->titulo, 'admin.portal.galeria');
    }

    private function idAdministrador(): int
    {
        $id = Auth::user()?->administrador?->id_administrador;

        if (! $id) {
            abort(403, 'Tu cuenta no tiene un perfil de administrador.');
        }

        return (int) $id;
    }

    private function formNoticia(Noticia $noticia, string $titulo)
    {
        $nuevo = ! $noticia->exists;

        return view('admin.portal.noticia-form', [
            'titulo' => $titulo,
            'subtitulo' => $nuevo ? 'Registro de una nueva publicación.' : 'Actualización de la publicación.',
            'icono' => $nuevo ? 'fa-newspaper' : 'fa-pen',
            'etiqueta' => 'Administrador',
            'noticia' => $noticia,
            'accion' => $nuevo ? route('admin.portal.noticias.guardar') : route('admin.portal.noticias.actualizar', $noticia),
            'metodo' => $nuevo ? 'POST' : 'PUT',
        ]);
    }

    private function formReporte(ReporteFinanciero $reporte, string $titulo)
    {
        $nuevo = ! $reporte->exists;

        return view('admin.portal.reporte-form', [
            'titulo' => $titulo,
            'subtitulo' => $nuevo ? 'Registro de un nuevo reporte mensual.' : 'Actualización del reporte.',
            'icono' => $nuevo ? 'fa-chart-column' : 'fa-pen',
            'etiqueta' => 'Administrador',
            'reporte' => $reporte,
            'accion' => $nuevo ? route('admin.portal.reportes.guardar') : route('admin.portal.reportes.actualizar', $reporte),
            'metodo' => $nuevo ? 'POST' : 'PUT',
        ]);
    }

    private function bajaLogica($registro, string $tabla, string $entidad, string $nombre, string $ruta)
    {
        DB::transaction(function () use ($registro, $tabla, $entidad, $nombre) {
            $registro->update(['is_active' => false]);

            BitacoraService::registrar($tabla, 'BAJA', "Baja lógica de {$entidad}: {$nombre}.", $registro->getKey());
        });

        return redirect()->route($ruta)->with('status', "Baja lógica aplicada a {$nombre}.");
    }

    private function categorias(): array
    {
        return ['Convocatorias', 'Formatos', 'Manuales', 'Normatividad', 'Recursos'];
    }

    /** @return array<string, array<int, mixed>> */
    private function reglasDocumento(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'in:'.implode(',', $this->categorias())],
            'archivo_ruta' => ['required', 'string', 'max:255'],
            'fecha_subida' => ['required', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    private function mensajesDocumento(): array
    {
        return [
            'titulo.required' => 'Ingresa el título del documento.',
            'categoria.required' => 'Selecciona una categoría.',
            'archivo_ruta.required' => 'Ingresa la ruta del archivo.',
            'fecha_subida.required' => 'Ingresa la fecha de subida.',
            'fecha_subida.date' => 'Ingresa una fecha válida.',
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function reglasReporte(?ReporteFinanciero $reporte = null): array
    {
        $unico = 'unique:reportes_financieros,mes,NULL,id_reporte,anio,'.(int) request('anio');
        if ($reporte) {
            $unico = 'unique:reportes_financieros,mes,'.$reporte->id_reporte.',id_reporte,anio,'.(int) request('anio');
        }

        return [
            'titulo' => ['required', 'string', 'max:150'],
            'mes' => ['required', 'integer', 'between:1,12', $unico],
            'anio' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'archivo_pdf_ruta' => ['required', 'string', 'max:255'],
            'fecha_publicacion' => ['required', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    private function mensajesReporte(): array
    {
        return [
            'titulo.required' => 'Ingresa el título del reporte.',
            'mes.required' => 'Selecciona el mes.',
            'mes.between' => 'Selecciona un mes válido.',
            'mes.unique' => 'Ya existe un reporte para ese mes y año.',
            'anio.required' => 'Ingresa el año.',
            'anio.digits' => 'El año debe tener 4 dígitos.',
            'archivo_pdf_ruta.required' => 'Ingresa la ruta del PDF.',
            'fecha_publicacion.required' => 'Ingresa la fecha de publicación.',
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function reglasImagen(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'imagen_ruta' => ['required', 'string', 'max:255'],
            'fecha_subida' => ['required', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    private function mensajesImagen(): array
    {
        return [
            'titulo.required' => 'Ingresa el título de la imagen.',
            'imagen_ruta.required' => 'Ingresa la ruta de la imagen.',
            'fecha_subida.required' => 'Ingresa la fecha de subida.',
            'fecha_subida.date' => 'Ingresa una fecha válida.',
        ];
    }
}
