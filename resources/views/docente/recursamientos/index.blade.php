@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Recursamientos asignados</h2>
            <p class="docente-ayuda">Cada R tiene un solo examen y una sola calificación final. No se capturan criterios de evaluación.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('docente.recursamientos.index') }}" class="docente-filtros">
        <label for="estatus">Estatus</label>
        <select id="estatus" name="estatus" onchange="this.form.submit()">
            <option value="pendiente" @selected($estatus === 'pendiente')>Pendientes</option>
            <option value="aprobada" @selected($estatus === 'aprobada')>Aprobadas</option>
            <option value="reprobada" @selected($estatus === 'reprobada')>Reprobadas</option>
            <option value="baja_temporal" @selected($estatus === 'baja_temporal')>Baja temporal</option>
            <option value="" @selected($estatus === '')>Todos</option>
        </select>
    </form>

    @forelse ($recursamientos as $recursamiento)
        <div class="tabla-wrap">
            <table class="tabla-docente-grid">
                <thead>
                    <tr>
                        <th>Ronda</th>
                        <th>Alumno</th>
                        <th>Materia</th>
                        <th>Semestre</th>
                        <th>Grupo original</th>
                        <th>Estatus</th>
                        <th>Calificación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="kardex-estado kardex-estado--r{{ $recursamiento->ronda }}">{{ $recursamiento->etiquetaRonda() }}</span></td>
                        <td>
                            <strong>{{ $recursamiento->alumno?->nombreCompleto() ?? '—' }}</strong>
                            <small>{{ $recursamiento->alumno?->usuario?->username }}</small>
                        </td>
                        <td>
                            <strong>{{ $recursamiento->materia?->nombre ?? '—' }}</strong>
                            <small>{{ $recursamiento->materia?->clave }}</small>
                        </td>
                        <td>{{ $recursamiento->semestre }}</td>
                        <td>{{ $recursamiento->grupoOriginal?->nombre ?? '—' }}</td>
                        <td>{{ $recursamiento->estatus }}</td>
                        <td>{{ $recursamiento->calificacion_final !== null ? number_format($recursamiento->calificacion_final, 2) : '—' }}</td>
                        <td>
                            <a href="{{ route('docente.recursamientos.show', $recursamiento) }}" class="btn-modulo btn-secundario">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div class="docente-vacio">
            <i class="fas fa-rotate-left"></i>
            <strong>No hay recursamientos con este estatus.</strong>
        </div>
    @endforelse
</section>
@endsection
