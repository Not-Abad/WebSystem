@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.grupos.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-plus"></i> Nuevo grupo
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar grupos</h2>
                <p>Filtra por grupo, plan, semestre o área propedéutica.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.grupos.index') }}" class="filtros">
            <div class="filtro">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Grupo, plan, semestre o área">
            </div>

            <div class="filtro">
                <label for="id_plan">Plan</label>
                <select id="id_plan" name="id_plan">
                    <option value="">Todos</option>
                    @foreach ($planes as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_plan'] ?? '') == $id)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

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
                <label for="id_area_propedeutica">Área propedéutica</label>
                <select id="id_area_propedeutica" name="id_area_propedeutica">
                    <option value="">Todas</option>
                    @foreach ($areas as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_area_propedeutica'] ?? '') == $id)>{{ $nombre }}</option>
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
                <h2><i class="fas fa-layer-group"></i> Grupos registrados</h2>
                <p>{{ $grupos->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Plan</th>
                        <th>Semestre</th>
                        <th>Área propedéutica</th>
                        <th>Cupo</th>
                        <th>Alumnos</th>
                        <th>Clases</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grupos as $grupo)
                        <tr>
                            <td><strong>{{ $grupo->area ? $grupo->nombre.' de '.$grupo->area->nombre : $grupo->nombre }}</strong></td>
                            <td class="texto-sutil">{{ $grupo->plan?->clave ?? '—' }}</td>
                            <td>{{ $grupo->semestre?->numero_semestre ?? '—' }}</td>
                            <td class="texto-sutil">{{ $grupo->area?->nombre ?? 'No aplica' }}</td>
                            <td>{{ $grupo->cupo_maximo }}</td>
                            <td>{{ $grupo->alumnos_count }}</td>
                            <td>{{ $grupo->clases_count }}</td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.horarios.index', ['id_plan' => $grupo->id_plan, 'id_grupo' => $grupo->id_grupo]) }}">
                                        <i class="fas fa-calendar-day"></i> Horario
                                    </a>
                                    <a href="{{ route('admin.grupos.edit', $grupo) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($grupo->alumnos_count === 0 && $grupo->clases_count === 0)
                                        <form method="POST" action="{{ route('admin.grupos.destroy', $grupo) }}"
                                              onsubmit="return confirm('¿Eliminar el grupo {{ $grupo->nombre }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-trash"></i> Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="vacio">No hay grupos que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($grupos->hasPages())
            <div class="paginacion">{{ $grupos->links() }}</div>
        @endif
    </section>
@endsection
