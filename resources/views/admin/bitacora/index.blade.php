@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.bitacora.export', request()->query()) }}" class="btn-modulo btn-secundario">
        <i class="fas fa-file-csv"></i> Exportar CSV
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-filter"></i> Filtros</h2>
                <p>Los filtros se conservan al paginar y al exportar.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.bitacora.index') }}" class="filtros">
            <div class="filtro">
                <label for="tabla">Tabla afectada</label>
                <select id="tabla" name="tabla">
                    <option value="">Todas</option>
                    @foreach ($tablas as $tabla)
                        <option value="{{ $tabla }}" @selected(($filtros['tabla'] ?? '') === $tabla)>{{ $tabla }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtro">
                <label for="accion">Acción</label>
                <select id="accion" name="accion">
                    <option value="">Todas</option>
                    @foreach ($acciones as $accion)
                        <option value="{{ $accion }}" @selected(($filtros['accion'] ?? '') === $accion)>{{ $accion }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtro">
                <label for="id_usuario">Usuario</label>
                <select id="id_usuario" name="id_usuario">
                    <option value="">Todos</option>
                    @foreach ($usuarios as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_usuario'] ?? '') == $id)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtro">
                <label for="desde">Desde</label>
                <input type="date" id="desde" name="desde" value="{{ $filtros['desde'] ?? '' }}">
            </div>

            <div class="filtro">
                <label for="hasta">Hasta</label>
                <input type="date" id="hasta" name="hasta" value="{{ $filtros['hasta'] ?? '' }}">
            </div>

            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Buscar en descripción o IP</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Texto, usuario o IP">
            </div>

            <div class="filtro">
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-magnifying-glass"></i> Buscar
                </button>
            </div>
        </form>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-file-circle-check"></i> Eventos registrados</h2>
                <p>{{ $registros->total() }} registro(s) encontrado(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Tabla</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registros as $registro)
                        <tr>
                            <td class="texto-sutil">{{ $registro->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $registro->usuario?->username ?? 'Sistema' }}</td>
                            <td>{{ $registro->tabla_afectada }}</td>
                            <td>
                                <span @class([
                                    'estado',
                                    'estado-activo' => in_array($registro->accion, ['ALTA', 'EDICION', 'INGRESO'], true),
                                    'estado-alerta' => in_array($registro->accion, ['BAJA', 'SUSPENSION', 'INGRESO_FALLIDO'], true),
                                ])>
                                    {{ $registro->accion }}
                                </span>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($registro->descripcion, 80) }}</td>
                            <td class="texto-sutil">{{ $registro->ip_origen ?? '—' }}</td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.bitacora.show', $registro) }}">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="vacio">No hay eventos que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registros->hasPages())
            <div class="paginacion">
                {{ $registros->links() }}
            </div>
        @endif
    </section>
@endsection
