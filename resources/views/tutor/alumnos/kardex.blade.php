@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Kardex de {{ $alumno->nombreCompleto() }}</h2>
            <p class="docente-ayuda">
                Plan: {{ $alumno->plan?->etiqueta() ?? 'Plan no asignado' }} ·
                Promedio acumulado: <strong>{{ number_format($promedio, 2) }}</strong>
            </p>
        </div>
        <a href="{{ route('tutor.alumnos.show', $alumno) }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver al expediente
        </a>
    </div>

    @forelse ($reticula->groupBy('semestre') as $semestre => $materias)
        <div class="docente-bloque">
            <h3>Semestre {{ $semestre }}</h3>
            <div class="tabla-wrap">
                <table class="tabla-docente-grid">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Clave</th>
                            <th>Situación</th>
                            <th>Calificación final</th>
                            <th>Fecha de conclusión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materias as $fila)
                            <tr>
                                <td><strong>{{ $fila['materia']->nombre }}</strong></td>
                                <td>{{ $fila['materia']->clave }}</td>
                                <td>
                                    <span class="kardex-estado kardex-estado--{{ $fila['estatus'] }}">
                                        <i class="fas fa-circle"></i> {{ $fila['etiqueta_estatus'] }}
                                    </span>
                                </td>
                                <td>{{ $fila['calificacion_final'] !== null ? number_format($fila['calificacion_final'], 2) : '—' }}</td>
                                <td>{{ $fila['fecha_finalizacion']?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="docente-vacio">
            <i class="fas fa-book-open"></i>
            <strong>Este alumno no tiene materias registradas en su plan.</strong>
        </div>
    @endforelse

    <div class="docente-bloque">
        <h3>Historial de recursamientos (R)</h3>
        @if ($recursamientos->isNotEmpty())
            <div class="tabla-wrap">
                <table class="tabla-docente-grid">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Semestre</th>
                            <th>Ronda</th>
                            <th>Situación</th>
                            <th>Calificación del examen</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recursamientos as $item)
                            @php
                                $situacion = [
                                    'pendiente' => 'Pendiente',
                                    'aprobada' => 'Aprobada',
                                    'reprobada' => 'Reprobada',
                                    'baja_temporal' => 'Baja temporal',
                                ][$item->estatus] ?? $item->estatus;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item->materia?->nombre ?? '—' }}</strong>
                                    <small>{{ $item->materia?->clave }}</small>
                                </td>
                                <td>{{ $item->semestre }}</td>
                                <td><span class="kardex-estado kardex-estado--r{{ $item->ronda }}">{{ $item->etiquetaRonda() }}</span></td>
                                <td>{{ $situacion }}</td>
                                <td>{{ $item->calificacion_final !== null ? number_format($item->calificacion_final, 2) : '—' }}</td>
                                <td>{{ $item->fecha_examen?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="docente-vacio">
                <i class="fas fa-circle-check"></i>
                <strong>Este alumno no tiene recursamientos registrados.</strong>
            </div>
        @endif
    </div>
</section>
@endsection
