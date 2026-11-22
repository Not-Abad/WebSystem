@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.catalogos.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-file-circle-plus"></i> Nuevo plan
    </a>
@endsection

@section('content')
    @include('admin.catalogos._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar planes</h2>
                <p>Filtra por clave o nombre del plan de estudios.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.catalogos.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Clave o nombre del plan">
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
                <h2><i class="fas fa-list-alt"></i> Planes registrados</h2>
                <p>{{ $planes->count() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Plan de estudios</th>
                        <th>Año de inicio</th>
                        <th>Materias</th>
                        <th>Grupos</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($planes as $item)
                        <tr>
                            <td><strong>{{ $item->clave }}</strong></td>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->anio_inicio }}</td>
                            <td>{{ $item->materias_count }}</td>
                            <td>{{ $item->grupos_count }}</td>
                            <td>
                                <span class="estado {{ $item->is_active ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ $item->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.catalogos.materias.plan', $item) }}">
                                        <i class="fas fa-book-bookmark"></i> Materias
                                    </a>
                                    <a href="{{ route('admin.catalogos.edit', $item) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($item->is_active)
                                        <form method="POST" action="{{ route('admin.catalogos.destroy', $item) }}"
                                              onsubmit="return confirm('¿Dar de baja el plan {{ $item->clave }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-ban"></i> Baja</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="vacio">No hay planes que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
