@extends('layouts.modulo')

@section('content')
@php
    $materia = $clase->materia;
    $docente = $clase->docente;
    $nombreDocente = $clase->docenteNombre() ?: 'Docente por asignar';
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>{{ $materia->clave }} · {{ $materia->nombre }}</h2>
        </div>
        <a href="{{ route('alumno.materias.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Mis materias
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
            <span>Plan de estudio en PDF</span>
            <strong>
                <a class="btn-modulo btn-primario alumno-pdf" href="{{ route('alumno.materias.programaPdf', $clase) }}" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf"></i> Ver programa
                </a>
            </strong>
        </div>
    </div>

    <div class="alumno-descripcion">
        <h3><i class="fas fa-align-left"></i> Descripción de la materia</h3>
        <p>{{ $materia->descripcion ?: 'La materia no tiene una descripción registrada.' }}</p>

        <h3><i class="fas fa-bullseye"></i> Objetivo</h3>
        <p>{{ $materia->objetivo ?: 'El objetivo de la materia no ha sido registrado.' }}</p>

        <h3><i class="fas fa-calendar-week"></i> Horario</h3>
        <p>
            @forelse ($clase->horarios->sortBy(['dia_semana', 'hora_inicio']) as $horario)
                {{ $horario->dia_semana === 'Miercoles' ? 'Miércoles' : $horario->dia_semana }}
                {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                ({{ $horario->salon?->clave ?? 'Sin salón' }})@if (! $loop->last), @endif
            @empty
                Sin horario asignado
            @endforelse
        </p>
    </div>
</section>

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Criterios de evaluación y calificaciones</h2>
        </div>
    </div>

    @forelse ($parciales as $parcial)
        @php
            $evaluacion = $parcial['evaluacion'];
            $criterios = $parcial['criterios'];
        @endphp
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
                    @if ($parcial['aceptada'])
                        <span class="alumno-chip-aceptado"><i class="fas fa-circle-check"></i> Criterios aceptados</span>
                    @endif
                </div>
            </div>

            @if ($criterios->isEmpty())
                <p class="docente-ayuda">Tu docente aún no ha publicado los criterios de esta evaluación.</p>
            @else
                <div class="tabla-wrap">
                    <table class="tabla-docente-grid">
                        <thead>
                            <tr>
                                <th>Criterio de evaluación</th>
                                <th>Ponderación</th>
                                @if ($parcial['aceptada'])
                                    <th>Porcentaje obtenido</th>
                                    <th>Calificación (0–10)</th>
                                    <th>Aportación al parcial</th>
                                @endif
                                <th>Fecha de entrega</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($criterios as $criterio)
                                <tr>
                                    <td>{{ $criterio['nombre_rubro'] ?: ($criterio['tipo_evidencia'] ?: '—') }}</td>
                                    <td>{{ number_format((float) $criterio['porcentaje'], 2) }} %</td>
                                    @if ($parcial['aceptada'])
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
                                    @endif
                                    <td>
                                        {{ ! empty($criterio['fecha_entrega'])
                                            ? \Illuminate\Support\Carbon::parse($criterio['fecha_entrega'])->format('d/m/Y')
                                            : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! $criterios->isEmpty() && ! $parcial['aceptada'])
                <form class="alumno-aceptacion" method="POST"
                      action="{{ route('alumno.materias.aceptarCriterios', [$clase, $evaluacion]) }}">
                    @csrf
                    <label>
                        <input type="checkbox" name="acepto" value="1" required>
                        Acepto los criterios de evaluación de {{ $evaluacion->etiqueta() }}.
                    </label>
                    <button type="submit" class="btn-modulo btn-primario">
                        <i class="fas fa-check"></i> Aceptar criterios
                    </button>
                </form>
            @endif

            @if ($parcial['aceptada'] && $parcial['tieneCalificacion'])
                <div class="alumno-calificacion">
                    <span>Calificación obtenida en el parcial</span>
                    <strong>{{ number_format($parcial['calificacion'], 2) }}</strong>
                </div>
            @endif
        </div>
    @empty
        <div class="docente-vacio">
            <i class="fas fa-calendar-days"></i>
            <strong>Tu docente aún no ha publicado evaluaciones.</strong>
        </div>
    @endforelse
</section>

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Calificación final de la materia</h2>
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

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Asistencia registrada</h2>
        </div>
    </div>

    @if ($asistencias->isEmpty())
        <p class="docente-ayuda">Aún no hay registros de asistencia para esta materia.</p>
    @else
        <div class="tabla-wrap">
            <table class="tabla-docente-grid">
                <thead><tr><th>Fecha</th><th>Estatus</th><th>Observaciones</th></tr></thead>
                <tbody>
                    @foreach ($asistencias as $asistencia)
                        <tr>
                            <td>{{ $asistencia->fecha->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($asistencia->estatus) }}</td>
                            <td>{{ $asistencia->observaciones ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
