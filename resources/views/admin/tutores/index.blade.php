@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.tutores.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-user-plus"></i> Nuevo tutor
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar tutores</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.tutores.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Nombre, correo o usuario">
            </div>

            <div class="filtro">
                <label for="estado">Estatus</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="activo" @selected($estado === 'activo')>Activo</option>
                    <option value="inactivo" @selected($estado === 'inactivo')>Inactivo</option>
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
                <h2><i class="fas fa-users"></i> Tutores registrados</h2>
                <p>{{ $tutores->total() }} registro(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Alumnos</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tutores as $tutor)
                        <tr>
                            <td><strong>{{ $tutor->nombre }} {{ $tutor->apellido_paterno }} {{ $tutor->apellido_materno }}</strong></td>
                            <td>{{ $tutor->usuario?->username ?? '—' }}</td>
                            <td>{{ $tutor->telefono }}</td>
                            <td class="texto-sutil">{{ $tutor->correo }}</td>
                            <td>
                                @forelse ($tutor->alumnos as $alumno)
                                    <div class="texto-sutil">
                                        {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}
                                    </div>
                                @empty
                                    <span class="texto-sutil">Sin alumnos</span>
                                @endforelse
                                <a href="{{ route('admin.tutores.alumnos', $tutor) }}" class="vinculo">
                                    <i class="fas fa-link"></i> Gestionar vínculo ({{ $tutor->alumnos->count() }})
                                </a>
                            </td>
                            <td>
                                @if ($tutor->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Activo</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-circle-pause"></i> Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.tutores.edit', $tutor) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($tutor->is_active && $tutor->id_usuario !== Auth::id())
                                        <form method="POST" action="{{ route('admin.tutores.destroy', $tutor) }}"
                                              onsubmit="return confirm('¿Dar de baja a {{ $tutor->nombre }} {{ $tutor->apellido_paterno }}?');">
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
                            <td colspan="7" class="vacio">No hay tutores que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tutores->hasPages())
            <div class="paginacion">{{ $tutores->links() }}</div>
        @endif
    </section>
@endsection
