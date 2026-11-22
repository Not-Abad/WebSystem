@extends('layouts.modulo')

@section('content')
@php
    $nombreDocente = $clase->docente?->nombreCompleto() ?? 'Docente por asignar';
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>{{ $clase->materia->clave }} · {{ $clase->materia->nombre }}</h2>
            <p class="docente-ayuda">
                {{ $clase->grupo->etiqueta() }} · {{ $nombreDocente }}
            </p>
        </div>
        <a href="{{ route('alumno.materias.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Mis materias
        </a>
    </div>

    <div class="docente-detalle">
        <div><span>Semestre</span><strong>{{ $clase->grupo->semestre?->nombreCompleto() ?? '—' }}</strong></div>
        <div><span>Plan de estudio</span><strong>{{ $clase->grupo->plan?->etiqueta() ?? 'Plan vigente' }}</strong></div>
        <div><span>Horario</span><strong>
            @forelse ($clase->horarios->sortBy(['dia_semana', 'hora_inicio']) as $horario)
                {{ $horario->dia_semana === 'Miercoles' ? 'Miércoles' : $horario->dia_semana }}
                {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                ({{ $horario->salon?->clave ?? 'Sin salón' }})@if (! $loop->last), @endif
            @empty
                Sin horario asignado
            @endforelse
        </strong></div>
    </div>
</section>

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Evaluaciones y calificaciones</h2>
            <p class="docente-ayuda">Los porcentajes y fechas son publicados por tu docente.</p>
        </div>
    </div>

    @forelse ($clase->evaluaciones as $evaluacion)
        @php
            $calificacion = round((float) $evaluacion->criterios->sum(function ($criterio) {
                return (float) ($criterio->calificaciones->first()?->puntaje_obtenido ?? 0);
            }), 2);
            $sinCalificaciones = $evaluacion->criterios->isEmpty() || $evaluacion->criterios->every(
                fn ($criterio) => $criterio->calificaciones->isEmpty()
            );
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
                    @if (! $sinCalificaciones)
                        <span class="docente-nota">Calificación: {{ number_format($calificacion, 2) }}</span>
                    @endif
                </div>
            </div>

            @if ($evaluacion->criterios->isEmpty())
                <p class="docente-ayuda">Tu docente aún no ha publicado los criterios de esta evaluación.</p>
            @else
                <div class="tabla-wrap">
                    <table class="tabla-docente-grid">
                        <thead>
                            <tr><th>Evidencia</th><th>Rubro</th><th>Ponderación</th><th>Porcentaje obtenido</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluacion->criterios as $criterio)
                                @php $nota = $criterio->calificaciones->first(); @endphp
                                <tr>
                                    <td>{{ $criterio->tipo_evidencia }}</td>
                                    <td>{{ $criterio->nombre_rubro }}</td>
                                    <td>{{ $criterio->porcentaje }} %</td>
                                    <td>{{ $nota ? number_format((float) $nota->porcentaje_obtenido, 2).' %' : 'Pendiente' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
            <h2>Asistencia registrada</h2>
            <p class="docente-ayuda">Asistencias, faltas y observaciones registradas para esta materia.</p>
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
