@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.portal.galeria.crear') }}" class="btn-modulo btn-primario">
        <i class="fas fa-plus"></i> Nueva imagen
    </a>
@endsection

@section('content')
    @include('admin.portal._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar imágenes</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.portal.galeria') }}" class="filtros">
            <div class="filtro" style="grid-column: span 3;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Título o descripción">
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
                <h2><i class="fas fa-images"></i> Imágenes publicadas</h2>
                <p>{{ $imagenes->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Ruta de la imagen</th>
                        <th>Fecha de subida</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($imagenes as $imagen)
                        <tr>
                            <td><strong>{{ $imagen->titulo }}</strong></td>
                            <td class="texto-sutil">{{ $imagen->descripcion ?? '—' }}</td>
                            <td class="texto-sutil">{{ $imagen->imagen_ruta }}</td>
                            <td class="texto-sutil">{{ $imagen->fecha_subida?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($imagen->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Visible</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-eye-slash"></i> Oculta</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.portal.galeria.editar', $imagen) }}"><i class="fas fa-pen"></i> Editar</a>
                                    @if ($imagen->is_active)
                                        <form method="POST" action="{{ route('admin.portal.galeria.eliminar', $imagen) }}"
                                              onsubmit="return confirm('¿Dar de baja la imagen {{ $imagen->titulo }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-eye-slash"></i> Ocultar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="vacio">No hay imágenes registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($imagenes->hasPages())
            <div class="paginacion">{{ $imagenes->links() }}</div>
        @endif
    </section>
@endsection
