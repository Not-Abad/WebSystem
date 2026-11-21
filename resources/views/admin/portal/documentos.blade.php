@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.portal.documentos.crear') }}" class="btn-modulo btn-primario">
        <i class="fas fa-plus"></i> Nuevo documento
    </a>
@endsection

@section('content')
    @include('admin.portal._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar documentos</h2>
                <p>Filtra por título o categoría.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.portal.documentos') }}" class="filtros">
            <div class="filtro" style="grid-column: span 3;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Título o categoría">
            </div>
            <div class="filtro">
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-file-lines"></i> Archivos publicados</h2>
                <p>{{ $documentos->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Ruta del archivo</th>
                        <th>Fecha de subida</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $documento)
                        <tr>
                            <td><strong>{{ $documento->titulo }}</strong></td>
                            <td>{{ $documento->categoria }}</td>
                            <td class="texto-sutil">{{ $documento->archivo_ruta }}</td>
                            <td class="texto-sutil">{{ $documento->fecha_subida?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($documento->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Visible</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-eye-slash"></i> Oculto</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.portal.documentos.editar', $documento) }}"><i class="fas fa-pen"></i> Editar</a>
                                    @if ($documento->is_active)
                                        <form method="POST" action="{{ route('admin.portal.documentos.eliminar', $documento) }}"
                                              onsubmit="return confirm('¿Dar de baja el documento {{ $documento->titulo }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-eye-slash"></i> Ocultar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="vacio">No hay documentos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documentos->hasPages())
            <div class="paginacion">{{ $documentos->links() }}</div>
        @endif
    </section>
@endsection
