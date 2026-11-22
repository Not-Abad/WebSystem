@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mi carga horaria</h2>
            <p class="docente-ayuda">
                Da clic en una materia para consultar el grupo, los alumnos, la asistencia y la evaluación.
            </p>
        </div>
        <div class="docente-chips">
            <span><i class="fas fa-book"></i> {{ $clases->count() }} materias</span>
            <span><i class="fas fa-clock"></i> {{ $totalBloques }} bloques</span>
        </div>
    </div>

    @if ($clases->isEmpty())
        <div class="docente-vacio">
            <i class="fas fa-calendar-xmark"></i>
            <strong>Aún no tienes clases asignadas.</strong>
            <p>Cuando el administrador configure los horarios, tus materias aparecerán aquí.</p>
        </div>
    @else
        <div class="tabla-wrap">
            <table class="tabla-horario tabla-horario-docente">
                <thead>
                    <tr>
                        <th>Hora</th>
                        @foreach ($dias as $dia)
                            <th>{{ $dia === 'Miercoles' ? 'Miércoles' : $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bloques as $bloque)
                        @php $rango = substr($bloque['inicio'], 0, 5).' a '.substr($bloque['fin'], 0, 5); @endphp
                        @if ($bloque['tipo'] === 'receso')
                            <tr class="fila-receso">
                                <td>{{ $rango }}</td>
                                <td colspan="{{ count($dias) }}"><i class="fas fa-mug-hot"></i> {{ $bloque['etiqueta'] }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="hora-bloque">{{ $rango }}</td>
                                @foreach ($dias as $dia)
                                    @php
                                        $clave = \App\Http\Controllers\Docente\HorarioController::claveCelda($dia, $bloque['inicio'], $bloque['fin']);
                                        $asignacion = $asignaciones[$clave] ?? null;
                                    @endphp
                                    <td class="celda-horario {{ $asignacion ? 'celda-asignada' : 'celda-vacia' }}">
                                        @if ($asignacion)
                                            @php $clase = $asignacion['clase']; @endphp
                                            <a class="celda-enlace"
                                               href="{{ route('docente.clases.show', $clase) }}?horario={{ $asignacion['horario']->id_horario }}">
                                                <strong>{{ $clase->materia->nombre }}</strong>
                                                <small>{{ $clase->grupo->etiqueta() }}</small>
                                                <small>{{ $asignacion['horario']->salon?->clave ?? 'Sin salón' }} · {{ $clase->materia->clave }}</small>
                                            </a>
                                        @else
                                            <span class="celda-vacia">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="docente-subtitulo">Mis materias</h3>
        <div class="docente-tarjetas">
            @foreach ($clases as $clase)
                <a class="docente-tarjeta" href="{{ route('docente.clases.show', $clase) }}">
                    <span class="docente-tarjeta-clave">{{ $clase->materia->clave }}</span>
                    <strong>{{ $clase->materia->nombre }}</strong>
                    <span>{{ $clase->grupo->etiqueta() }} · Semestre {{ $clase->grupo->semestre?->numero_semestre ?? '—' }}</span>
                    <span class="docente-tarjeta-pie">
                        {{ $clase->horarios->count() }} bloques ·
                        {{ $clase->alumnosActivos()->count() }} alumnos activos
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection
