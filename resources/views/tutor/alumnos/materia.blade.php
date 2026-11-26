@extends('layouts.modulo')

@section('content')
@php
    $materia = $clase->materia;
    $docente = $clase->docente;
    $nombreDocente = $clase->docenteNombre() ?: 'Docente por asignar';
    $nombreAlumno = $alumno->nombreCompleto();
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>{{ $materia->clave }} · {{ $materia->nombre }}</h2>
            <p class="docente-ayuda">
                Seguimiento de {{ $nombreAlumno }} · {{ $clase->grupo->etiqueta() }}
            </p>
        </div>
        <a href="{{ route('tutor.alumnos.show', $alumno) }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Expediente del alumno
        </a>
    </div>

    <div class="docente-detalle">
        <div>
            <span>Docente</span>
            <strong>{{ $nombreDocente }}</strong>
        </div>
        <div>
            <span>Correo institucional</span>
            <strong>{{ $docente?->correo_institucional ?? '—' }}</strong>
        </div>
        <div>
            <span>RFC</span>
            <strong>{{ $docente?->rfc ?: '—' }}</strong>
        </div>
        <div>
            <span>Semestre</span>
            <strong>{{ $materia->semestre?->nombreCompleto() ?? $clase->grupo->semestre?->nombreCompleto() ?? '—' }}</strong>
        </div>
        <div>
            <span>Plan de estudio</span>
            <strong>{{ $materia->plan?->etiqueta() ?? 'Plan vigente' }}</strong>
        </div>
        <div>
            <span>Tipo de materia</span>
            <strong>{{ $materia->tipo_materia }} · {{ $materia->horas_semana }} h/semana</strong>
        </div>
        <div>
            <span>Horario</span>
            <strong>
                @forelse ($clase->horarios->sortBy(['dia_semana', 'hora_inicio']) as $horario)
                    {{ $horario->dia_semana === 'Miercoles' ? 'Miércoles' : $horario->dia_semana }}
                    {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                    ({{ $horario->salon?->clave ?? 'Sin salón' }})@if (! $loop->last), @endif
                @empty
                    Sin horario asignado
                @endforelse
            </strong>
        </div>
        <div>
            <span>Descripción</span>
            <strong>{{ $materia->descripcion ?: 'Sin descripción registrada.' }}</strong>
        </div>
    </div>
</section>

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Criterios de evaluación y calificaciones</h2>
            <p class="docente-ayuda">
                Calificaciones obtenidas por {{ $nombreAlumno }} hasta el momento. Cada criterio se convierte a una nota de 0.00 a 10.00 y el parcial es el promedio ponderado de esas notas.
            </p>
        </div>
    </div>

    @forelse ($parciales as $parcial)
        @php $evaluacion = $parcial['evaluacion']; @endphp
        <div class="docente-bloque">
            <div class="docente-cabecera">
                <h3>{{ $evaluacion->etiqueta() }}</h3>
                <div class="docente-chips">
                    @if ($evaluacion->fecha_examen)
                        <span><i class="fas fa-file-pen"></i> Examen: {{ $evaluacion->fecha_examen->format('d/m/Y') }}</span>
                    @endif
                    @if ($evaluacion->fecha_entrega_proyecto)
                        <span><i class="fas fa-diagram-project"></i> Proyecto: {{ $evaluacion->fecha_entrega_proyecto->format('d/m/Y') }}</span>
                    @endif
                    <span class="{{ $parcial['aceptada'] ? 'alumno-chip-aceptado' : '' }}">
                        <i class="fas {{ $parcial['aceptada'] ? 'fa-circle-check' : 'fa-hourglass-half' }}"></i>
                        {{ $parcial['aceptada'] ? 'Criterios aceptados por el alumno' : 'Criterios pendientes de aceptar' }}
                    </span>
                </div>
            </div>

            @if ($parcial['criterios']->isEmpty())
                <p class="docente-ayuda">El docente aún no ha publicado criterios de evaluación para este parcial.</p>
            @else
                <div class="tabla-wrap">
                    <table class="tabla-docente-grid">
                        <thead>
                            <tr>
                                <th>Evidencia</th>
                                <th>Rubro</th>
                                <th>Ponderación</th>
                                <th>Porcentaje obtenido</th>
                                <th>Calificación (0–10)</th>
                                <th>Aportación al parcial</th>
                                <th>Fecha de entrega</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($parcial['criterios'] as $criterio)
                                <tr>
                                    <td>{{ $criterio['tipo_evidencia'] ?: '—' }}</td>
                                    <td>{{ $criterio['nombre_rubro'] }}</td>
                                    <td>{{ number_format($criterio['porcentaje'], 2) }} %</td>
                                    <td>
                                        {{ $criterio['porcentaje_obtenido'] !== null
                                            ? number_format($criterio['porcentaje_obtenido'], 2).' %'
                                            : 'Pendiente' }}
                                    </td>
                                    <td>
                                        {{ $criterio['calificacion'] !== null
                                            ? number_format($criterio['calificacion'], 2)
                                            : 'Pendiente' }}
                                    </td>
                                    <td>
                                        {{ $criterio['aportacion'] !== null
                                            ? number_format($criterio['aportacion'], 2)
                                            : 'Pendiente' }}
                                    </td>
                                    <td>
                                        {{ $criterio['fecha_entrega']
                                            ? \Illuminate\Support\Carbon::parse($criterio['fecha_entrega'])->format('d/m/Y')
                                            : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($parcial['tieneCalificacion'])
                <div class="alumno-calificacion">
                    <span>Calificación obtenida en el parcial</span>
                    <strong>{{ number_format($parcial['calificacion'], 2) }}</strong>
                </div>
            @endif
        </div>
    @empty
        <div class="docente-vacio">
            <i class="fas fa-calendar-days"></i>
            <strong>El docente aún no ha publicado evaluaciones de esta materia.</strong>
        </div>
    @endforelse
</section>

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Calificación total de la materia</h2>
            <p class="docente-ayuda">Promedio de los tres parciales cuando todos están calificados.</p>
        </div>
    </div>

    @if ($calificacionFinal !== null)
        <div class="alumno-calificacion-final">
            <i class="fas fa-award"></i>
            <div>
                <span>Calificación total</span>
                <strong>{{ number_format($calificacionFinal, 2) }}</strong>
            </div>
        </div>
    @else
        <div class="docente-vacio">
            <i class="fas fa-hourglass-half"></i>
            <strong>Pendiente de los tres parciales.</strong>
        </div>
    @endif
</section>
@endsection
