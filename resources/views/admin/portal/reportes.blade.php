@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.portal.reportes.crear') }}" class="btn-modulo btn-primario">
        <i class="fas fa-plus"></i> Nuevo reporte
    </a>
@endsection

@section('content')
    @include('admin.portal._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar reportes</h2>
                <p>Filtra por título.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.portal.reportes') }}" class="filtros">
            <div class="filtro" style="grid-column: span 3;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Título del reporte">
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
                <h2><i class="fas fa-chart-column"></i> Reportes publicados</h2>
                <p>{{ $reportes->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Periodo</th>
                        <th>Ruta del PDF</th>
                        <th>Fecha de publicación</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $reporte)
                        <tr>
                            <td><strong>{{ $reporte->titulo }}</strong></td>
                            <td>{{ $reporte->periodo() }}</td>
                            <td class="texto-sutil">{{ $reporte->archivo_pdf_ruta }}</td>
                            <td class="texto-sutil">{{ $reporte->fecha_publicacion?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($reporte->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Visible</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-eye-slash"></i> Oculto</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.portal.reportes.editar', $reporte) }}"><i class="fas fa-pen"></i> Editar</a>
                                    @if ($reporte->is_active)
                                        <form method="POST" action="{{ route('admin.portal.reportes.eliminar', $reporte) }}"
                                              onsubmit="return confirm('¿Dar de baja el reporte {{ $reporte->periodo() }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-eye-slash"></i> Ocultar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="vacio">No hay reportes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reportes->hasPages())
            <div class="paginacion">{{ $reportes->links() }}</div>
        @endif
    </section>
@endsection
