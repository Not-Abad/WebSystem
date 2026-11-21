@extends('layouts.modulo')

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-folder-tree"></i> Explorador de documentos</h2>
                <p>Sube, organiza y descarga boletas, certificados y cualquier documento institucional.</p>
            </div>
        </div>

        <nav class="archivos-migas" aria-label="Ruta de carpetas">
            @foreach ($migas as $miga)
                @if (! $loop->last)
                    <a href="{{ route('admin.archivos.index', ['ruta' => $miga['ruta']]) }}">{{ $miga['nombre'] }}</a>
                    <span>/</span>
                @else
                    <strong>{{ $miga['nombre'] }}</strong>
                @endif
            @endforeach
        </nav>

        <div class="archivos-acciones">
            <form method="POST" action="{{ route('admin.archivos.subir') }}" enctype="multipart/form-data" class="archivos-form">
                @csrf
                <input type="hidden" name="ruta" value="{{ $ruta }}">
                <label for="archivo"><i class="fas fa-file-arrow-up"></i> Subir archivo</label>
                <input type="file" id="archivo" name="archivo" required
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.txt,.csv,.zip">
                <button type="submit" class="btn-modulo btn-primario btn-mini">
                    <i class="fas fa-upload"></i> Cargar
                </button>
                <small>PDF, Office, imágenes, texto o ZIP · máximo 10 MB</small>
            </form>

            <form method="POST" action="{{ route('admin.archivos.carpeta') }}" class="archivos-form">
                @csrf
                <input type="hidden" name="ruta" value="{{ $ruta }}">
                <label for="nombre"><i class="fas fa-folder-plus"></i> Nueva carpeta</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="60"
                       placeholder="Ej. Boletas 2026">
                <button type="submit" class="btn-modulo btn-secundario btn-mini">
                    <i class="fas fa-plus"></i> Crear
                </button>
            </form>
        </div>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-list-check"></i> Contenido de esta carpeta</h2>
                <p>{{ $directorios->count() }} carpeta(s) · {{ $archivos->count() }} archivo(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($directorios as $directorio)
                        <tr>
                            <td>
                                <a class="vinculo" href="{{ route('admin.archivos.index', ['ruta' => $directorio['ruta']]) }}">
                                    <i class="fas fa-folder"></i> {{ $directorio['nombre'] }}
                                </a>
                            </td>
                            <td>Carpeta</td>
                            <td class="texto-sutil">—</td>
                            <td>
                                <form method="POST" action="{{ route('admin.archivos.eliminar') }}"
                                      onsubmit="return confirm('¿Eliminar la carpeta {{ $directorio['nombre'] }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tipo" value="carpeta">
                                    <input type="hidden" name="ruta" value="{{ $directorio['ruta'] }}">
                                    <button type="submit" class="btn-modulo btn-peligro btn-mini">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                    @endforelse

                    @forelse ($archivos as $archivo)
                        <tr>
                            <td><i class="fas fa-file"></i> {{ $archivo['nombre'] }}</td>
                            <td class="texto-sutil">Archivo</td>
                            <td class="texto-sutil">{{ number_format($archivo['tamano'] / 1024, 1) }} KB</td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.archivos.descargar', ['archivo' => $archivo['ruta']]) }}">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <form method="POST" action="{{ route('admin.archivos.eliminar') }}"
                                          onsubmit="return confirm('¿Eliminar el archivo {{ $archivo['nombre'] }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="tipo" value="archivo">
                                        <input type="hidden" name="ruta" value="{{ $archivo['ruta'] }}">
                                        <button type="submit"><i class="fas fa-trash"></i> Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @if ($directorios->isEmpty())
                            <tr>
                                <td colspan="4" class="vacio">Esta carpeta está vacía. Sube un archivo o crea una subcarpeta.</td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
