@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.salones.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-plus"></i> Nuevo salón
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar salones</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.salones.index') }}" class="filtros">
            <div class="filtro">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Clave, nombre o edificio">
            </div>

            <div class="filtro">
                <label for="estatus">Estatus</label>
                <select id="estatus" name="estatus">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filtros['estatus'] ?? '') === '1')>Activos</option>
                    <option value="0" @selected(($filtros['estatus'] ?? '') === '0')>Inactivos</option>
                </select>
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
                <h2><i class="fas fa-door-open"></i> Salones registrados</h2>
                <p>{{ $salones->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Nombre</th>
                        <th>Edificio</th>
                        <th>Capacidad</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salones as $salon)
                        <tr>
                            <td><strong>{{ $salon->clave }}</strong></td>
                            <td>{{ $salon->nombre }}</td>
                            <td class="texto-sutil">{{ $salon->edificio ?? '—' }}</td>
                            <td>{{ $salon->capacidad }}</td>
                            <td>
                                <span class="estatus {{ $salon->is_active ? 'estatus-activo' : 'estatus-inactivo' }}">
                                    {{ $salon->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.salones.edit', $salon) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($salon->is_active)
                                        <form method="POST" action="{{ route('admin.salones.destroy', $salon) }}"
                                              onsubmit="return confirm('¿Dar de baja el salón {{ $salon->clave }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-trash"></i> Dar de baja</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="vacio">No hay salones que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($salones->hasPages())
            <div class="paginacion">{{ $salones->links() }}</div>
        @endif
    </section>
@endsection
