@extends('layouts.modulo')

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar en mis alumnos</h2>
                <p>Solo puedes consultar a los estudiantes asignados a tu perfil de tutor.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('tutor.alumnos.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 3;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Nombre, CURP o correo">
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
                <h2><i class="fas fa-users"></i> Mis alumnos</h2>
                <p>{{ $alumnos->total() }} estudiante(s) bajo tu tutela</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Nombre</th>
                        <th>Plan</th>
                        <th>Semestre</th>
                        <th>Estatus</th>
                        <th>Promedio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td><strong>#{{ $alumno->id_alumno }}</strong></td>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                            <td class="texto-sutil">{{ $alumno->plan?->nombre ?? '—' }}</td>
                            <td>{{ $alumno->semestre?->numero_semestre ?? '—' }}</td>
                            <td>
                                <span @class([
                                    'estado',
                                    'estado-inactivo' => $alumno->id_estado_alumno === \App\Models\EstadoAlumno::BAJA_DEFINITIVA,
                                    'estado-activo' => $alumno->id_estado_alumno !== \App\Models\EstadoAlumno::BAJA_DEFINITIVA,
                                ])>
                                    {{ $alumno->estado?->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>{{ number_format((float) $alumno->promedio_general, 2) }}</td>
                            <td>
                                <a href="{{ route('tutor.alumnos.show', $alumno) }}" class="vinculo">
                                    <i class="fas fa-eye"></i> Ver expediente
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="vacio">No tienes alumnos asignados con esos criterios.</td>
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
