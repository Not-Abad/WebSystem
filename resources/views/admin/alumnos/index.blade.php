@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.alumnos.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-user-plus"></i> Nuevo alumno
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar alumnos</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.alumnos.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Nombre, matrícula, CURP o usuario">
            </div>

            <div class="filtro">
                <label for="id_estado_alumno">Estatus escolar</label>
                <select id="id_estado_alumno" name="id_estado_alumno">
                    <option value="">Todos</option>
                    @foreach ($estados as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_estado_alumno'] ?? '') == $id)>{{ $nombre }}</option>
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
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-graduate"></i> Alumnos registrados</h2>
                <p>{{ $alumnos->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Tutor</th>
                        <th>Plan</th>
                        <th>Semestre</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td><strong>{{ $alumno->grupo?->nombre ?? '—' }}</strong></td>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                            <td>{{ $alumno->usuario?->username ?? '—' }}</td>
                            <td class="texto-sutil">
                                {{ $alumno->tutor ? trim($alumno->tutor->nombre.' '.$alumno->tutor->apellido_paterno) : '—' }}
                            </td>
                            <td class="texto-sutil">{{ $alumno->plan?->nombre ?? '—' }}</td>
                            <td>{{ $alumno->semestre?->numero_semestre ?? '—' }}</td>
                            <td>
                                @if ($alumno->id_estado_alumno === \App\Models\EstadoAlumno::BAJA_DEFINITIVA)
                                    <span class="estado estado-inactivo">{{ $alumno->estado?->nombre ?? 'Baja definitiva' }}</span>
                                @else
                                    <span class="estado estado-activo">{{ $alumno->estado?->nombre ?? '—' }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.alumnos.edit', $alumno) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($alumno->id_estado_alumno !== \App\Models\EstadoAlumno::BAJA_DEFINITIVA && $alumno->id_usuario !== Auth::id())
                                        <form method="POST" action="{{ route('admin.alumnos.destroy', $alumno) }}"
                                              onsubmit="return confirm('¿Dar de baja definitiva a {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-user-slash"></i> Dar de baja</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="vacio">No hay alumnos que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($alumnos->hasPages())
            <div class="paginacion">{{ $alumnos->links() }}</div>
        @endif
    </section>
@endsection
