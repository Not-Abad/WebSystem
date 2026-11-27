@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.tutores.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a tutores
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-link"></i> Alumnos vinculados</h2>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Alumno</th>
                        <th>Semestre</th>
                        <th>Estatus</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tutor->alumnos as $alumno)
                        <tr>
                            <td><strong>#{{ $alumno->id_alumno }}</strong></td>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
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
                            <td>
                                <form method="POST" action="{{ route('admin.tutores.alumnos.desasignar', [$tutor, $alumno]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-modulo btn-secundario btn-mini">
                                        <i class="fas fa-unlink"></i> Desasignar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="vacio">Este tutor todavía no tiene alumnos vinculados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-plus"></i> Vincular alumno</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.tutores.alumnos', $tutor) }}" class="filtros">
            <div class="filtro" style="grid-column: span 3;">
                <label for="q">Buscar alumno</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}"
                       placeholder="Ej. Ana, Hernández, CURP o correo"
                       minlength="2" autocomplete="off" autofocus>
            </div>

            <div class="filtro">
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-magnifying-glass"></i> Buscar
                </button>
            </div>

            @if ($busqueda !== '')
                <div class="filtro">
                    <a href="{{ route('admin.tutores.alumnos', $tutor) }}" class="btn-modulo btn-secundario" style="width: 100%;">
                        <i class="fas fa-xmark"></i> Limpiar
                    </a>
                </div>
            @endif
        </form>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-list-check"></i> Resultados</h2>
                <p>{{ $disponibles->total() }} alumno(s) disponible(s) para vincular</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Alumno</th>
                        <th>CURP</th>
                        <th>Tutores actuales (máx. 3)</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($disponibles as $alumno)
                        <tr>
                            <td><strong>#{{ $alumno->id_alumno }}</strong></td>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                            <td class="texto-sutil">{{ $alumno->curp }}</td>
                            <td class="texto-sutil">
                                @forelse ($alumno->tutores as $tutorAsignado)
                                    {{ trim($tutorAsignado->nombre.' '.$tutorAsignado->apellido_paterno) }}{{ $loop->last ? '' : ', ' }}
                                @empty
                                    {{ $alumno->tutor
                                        ? trim($alumno->tutor->nombre.' '.$alumno->tutor->apellido_paterno)
                                        : '—' }}
                                @endforelse
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.tutores.alumnos.vincular', $tutor) }}">
                                    @csrf
                                    <input type="hidden" name="id_alumno" value="{{ $alumno->id_alumno }}">
                                    <button type="submit" class="btn-modulo btn-primario btn-mini">
                                        <i class="fas fa-link"></i> Vincular
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="vacio">
                                {{ $busqueda === ''
                                    ? 'Escribe al menos 2 caracteres para buscar alumnos disponibles.'
                                    : 'No se encontraron alumnos con ese criterio.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($disponibles->hasPages())
            <div class="paginacion">{{ $disponibles->links() }}</div>
        @endif
    </section>
@endsection
