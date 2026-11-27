@extends('layouts.archivo')

@section('content')
    <div class="archivos-picker">
        <header class="archivos-picker-head">
            <div>
                <h2><i class="fas fa-folder-open"></i> Seleccionar archivo</h2>
            </div>
            <button type="button" class="btn-modulo btn-secundario btn-mini" onclick="window.parent.cerrarGestorArchivos()">
                <i class="fas fa-xmark"></i> Cerrar
            </button>
        </header>

        <nav class="archivos-migas" aria-label="Ruta de carpetas">
            @foreach ($migas as $miga)
                @if (! $loop->last)
                    <a href="{{ route('admin.archivos.picker', ['campo' => $campo, 'ruta' => $miga['ruta']]) }}">{{ $miga['nombre'] }}</a>
                    <span>/</span>
                @else
                    <strong>{{ $miga['nombre'] }}</strong>
                @endif
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.archivos.subir') }}" enctype="multipart/form-data" class="archivos-form">
            @csrf
            <input type="hidden" name="ruta" value="{{ $ruta }}">
            <label for="archivo"><i class="fas fa-file-arrow-up"></i> Subir y usar</label>
            <input type="file" id="archivo" name="archivo" required
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.txt,.csv,.zip">
            <button type="submit" class="btn-modulo btn-primario btn-mini">
                <i class="fas fa-upload"></i> Cargar
            </button>
        </form>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tamaño</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($directorios as $directorio)
                        <tr>
                            <td>
                                <a class="vinculo" href="{{ route('admin.archivos.picker', ['campo' => $campo, 'ruta' => $directorio['ruta']]) }}">
                                    <i class="fas fa-folder"></i> {{ $directorio['nombre'] }}
                                </a>
                            </td>
                            <td class="texto-sutil">—</td>
                            <td class="texto-sutil">Abrir</td>
                        </tr>
                    @empty
                    @endforelse

                    @forelse ($archivos as $archivo)
                        <tr>
                            <td><i class="fas fa-file"></i> {{ $archivo['nombre'] }}</td>
                            <td class="texto-sutil">{{ number_format($archivo['tamano'] / 1024, 1) }} KB</td>
                            <td>
                                <button type="button" class="btn-modulo btn-primario btn-mini"
                                        onclick="window.parent.seleccionarArchivo('{{ $campo }}', @js($archivo['ruta']))">
                                    <i class="fas fa-check"></i> Seleccionar
                                </button>
                            </td>
                        </tr>
                    @empty
                        @if ($directorios->isEmpty())
                            <tr>
                                <td colspan="3" class="vacio">No hay archivos en esta carpeta. Puedes subir uno aquí mismo.</td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
