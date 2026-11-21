@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.portal.noticias.crear') }}" class="btn-modulo btn-primario">
        <i class="fas fa-plus"></i> Nueva noticia
    </a>
@endsection

@section('content')
    @include('admin.portal._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar noticias</h2>
                <p>Filtra por título o contenido.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.portal.noticias') }}" class="filtros">
            <div class="filtro" style="grid-column: span 3;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Título o contenido">
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
                <h2><i class="fas fa-newspaper"></i> Publicaciones</h2>
                <p>{{ $noticias->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha de publicación</th>
                        <th>Publicada por</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($noticias as $noticia)
                        <tr>
                            <td><strong>{{ $noticia->titulo }}</strong></td>
                            <td class="texto-sutil">{{ $noticia->fecha_publicacion?->format('d/m/Y H:i') }}</td>
                            <td class="texto-sutil">
                                {{ $noticia->administrador ? trim($noticia->administrador->nombre.' '.$noticia->administrador->apellido_paterno) : '—' }}
                            </td>
                            <td>
                                @if ($noticia->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Visible</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-eye-slash"></i> Oculta</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.portal.noticias.editar', $noticia) }}"><i class="fas fa-pen"></i> Editar</a>
                                    @if ($noticia->is_active)
                                        <form method="POST" action="{{ route('admin.portal.noticias.eliminar', $noticia) }}"
                                              onsubmit="return confirm('¿Dar de baja la noticia {{ $noticia->titulo }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-eye-slash"></i> Ocultar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="vacio">No hay noticias registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($noticias->hasPages())
            <div class="paginacion">{{ $noticias->links() }}</div>
        @endif
    </section>
@endsection
