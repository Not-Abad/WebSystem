@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mi horario</h2>
        </div>
        <div class="docente-chips">
            <span><i class="fas fa-book"></i> {{ $clases->count() }} materias</span>
            <span><i class="fas fa-users"></i> {{ $alumno->grupo?->etiqueta() ?? 'Sin grupo' }}</span>
        </div>
    </div>

    @if ($clases->isEmpty())
        <div class="docente-vacio">
            <i class="fas fa-calendar-xmark"></i>
            <strong>Aún no hay materias asignadas a tu grupo.</strong>
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
                                        $clave = \App\Support\HorariosPortal::claveCelda(
                                            $dia,
                                            $bloque['inicio'],
                                            $bloque['fin']
                                        );
                                        $asignacion = $asignaciones[$clave] ?? null;
                                    @endphp
                                    <td class="celda-horario {{ $asignacion ? 'celda-asignada' : 'celda-vacia' }}">
                                        @if ($asignacion)
                                            @php $clase = $asignacion['clase']; @endphp
                                            <a class="celda-enlace" href="{{ route('alumno.materias.show', $clase) }}">
                                                <strong>{{ $clase->materia->nombre }}</strong>
                                                <small>{{ $clase->materia->clave }}</small>
                                                <small>{{ $asignacion['horario']->salon?->clave ?? 'Sin salón' }}</small>
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
                <a class="docente-tarjeta" href="{{ route('alumno.materias.show', $clase) }}">
                    <span class="docente-tarjeta-clave">{{ $clase->materia->clave }}</span>
                    <strong>{{ $clase->materia->nombre }}</strong>
                    <span>{{ $clase->docente?->nombreCompleto() ?? 'Docente por asignar' }}</span>
                    <span class="docente-tarjeta-pie">
                        {{ $clase->evaluaciones->count() }} evaluaciones
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection
