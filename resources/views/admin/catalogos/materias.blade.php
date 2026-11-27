@extends('layouts.modulo')

@section('hero-actions')
    @if ($plan)
        <a href="{{ route('admin.catalogos.materias.crear', $plan) }}" class="btn-modulo btn-primario">
            <i class="fas fa-book-bookmark"></i> Nueva materia
        </a>
    @endif
@endsection

@section('content')
    @include('admin.catalogos._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar materias</h2>
            </div>
        </div>

        <form method="GET" action="{{ $plan ? route('admin.catalogos.materias.plan', $plan) : route('admin.catalogos.materias') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Nombre o clave de la materia">
            </div>

            @unless ($plan)
                <div class="filtro">
                    <label for="id_plan">Plan de estudios</label>
                    <select id="id_plan" name="id_plan">
                        <option value="">Todos</option>
                        @foreach ($planes as $id => $nombre)
                            <option value="{{ $id }}" @selected(($filtros['id_plan'] ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div class="filtro">
                <label for="id_semestre">Semestre</label>
                <select id="id_semestre" name="id_semestre">
                    <option value="">Todos</option>
                    @foreach ($semestres as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_semestre'] ?? '') == $id)>{{ $nombre }}</option>
                    @endforeach
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
                <h2><i class="fas fa-book-bookmark"></i> Materias registradas</h2>
                <p>{{ $materias->count() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Materia</th>
                        <th>Plan</th>
                        <th>Semestre</th>
                        <th>Tipo</th>
                        <th>Tronco común</th>
                        <th>Área</th>
                        <th>Horas</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materias as $materia)
                        <tr>
                            <td><strong>{{ $materia->clave }}</strong></td>
                            <td>{{ $materia->nombre }}</td>
                            <td class="texto-sutil">{{ $materia->plan?->clave }}</td>
                            <td>{{ $materia->semestre?->numero_semestre }}</td>
                            <td>{{ $materia->tipo_materia }}</td>
                            <td>{{ $materia->es_tronco_comun ? 'Sí' : 'No' }}</td>
                            <td class="texto-sutil">{{ $materia->area?->nombre ?? '—' }}</td>
                            <td>{{ $materia->horas_semana }}</td>
                            <td>
                                <span class="estado {{ $materia->is_active ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ $materia->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.catalogos.materias.editar', $materia) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($materia->is_active)
                                        <form method="POST" action="{{ route('admin.catalogos.materias.eliminar', $materia) }}"
                                              onsubmit="return confirm('¿Dar de baja la materia {{ $materia->clave }}?');">
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
                            <td colspan="10" class="vacio">
                                @if ($plan)
                                    Este plan aún no tiene materias. Da de alta la primera.
                                @else
                                    No hay materias que coincidan con los filtros.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
