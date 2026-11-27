@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mi kardex escolar</h2>
            <p class="docente-ayuda">Plan de estudios: {{ $alumno->plan?->etiqueta() ?? 'Plan no asignado' }}</p>
        </div>
        <a href="{{ route('alumno.materias.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a mis materias
        </a>
    </div>

    @if ($graduado)
        <div class="alumno-calificacion-final">
            <i class="fas fa-graduation-cap"></i>
            <div>
                <span>Promedio final de todas las calificaciones</span>
                <strong>{{ number_format((float) $promedioFinal, 2) }}</strong>
            </div>
        </div>
    @else
        <div class="kardex-resumen">
            <div class="kardex-tarjeta">
                <span>Materias cursadas</span>
                <strong>{{ $totales['cursadas'] }}</strong>
            </div>
            <div class="kardex-tarjeta">
                <span>En curso</span>
                <strong>{{ $totales['en_curso'] }}</strong>
            </div>
            <div class="kardex-tarjeta">
                <span>Sin cursar</span>
                <strong>{{ $totales['pendientes'] }}</strong>
            </div>
            <div class="kardex-tarjeta">
                <span>Promedio acumulado</span>
                <strong>{{ $promedioParcial !== null ? number_format($promedioParcial, 2) : '—' }}</strong>
            </div>
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
                                @php
                                    $estado = $fila['estatus'];
                                    $etiqueta = $fila['etiqueta_estatus'];
                                    $icono = [
                                        'cursada' => 'fa-circle-check',
                                        'aprobada_r' => 'fa-circle-check',
                                        'en_curso' => 'fa-spinner',
                                        'r1' => 'fa-rotate-left',
                                        'r2' => 'fa-rotate-left',
                                        'r3' => 'fa-rotate-left',
                                        'r4' => 'fa-rotate-left',
                                        'baja_temporal' => 'fa-circle-xmark',
                                        'pendiente' => 'fa-circle',
                                    ][$estado] ?? 'fa-circle';
                                @endphp
                                <tr>
                                    <td><strong>{{ $fila['materia']->nombre }}</strong></td>
                                    <td>{{ $fila['materia']->clave }}</td>
                                    <td>
                                        <span class="kardex-estado kardex-estado--{{ $estado }}">
                                            <i class="fas {{ $icono }}"></i> {{ $etiqueta }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $fila['calificacion_final'] !== null
                                            ? number_format($fila['calificacion_final'], 2)
                                            : '—' }}
                                    </td>
                                    <td>
                                        {{ $fila['fecha_finalizacion']?->format('d/m/Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="docente-vacio">
                <i class="fas fa-book-open"></i>
                <strong>No hay materias registradas en tu plan de estudios.</strong>
            </div>
        @endforelse

        @if ($recursamientos->isNotEmpty())
            <div class="docente-bloque">
                <h3>Historial de recursamientos (R)</h3>
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
                            @foreach ($recursamientos as $recursamiento)
                                @php
                                    $situacion = [
                                        'pendiente' => 'Pendiente',
                                        'aprobada' => 'Aprobada',
                                        'reprobada' => 'Reprobada',
                                        'baja_temporal' => 'Baja temporal',
                                    ][$recursamiento->estatus] ?? $recursamiento->estatus;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $recursamiento->materia?->nombre ?? '—' }}</strong>
                                        <small>{{ $recursamiento->materia?->clave }}</small>
                                    </td>
                                    <td>{{ $recursamiento->semestre }}</td>
                                    <td><span class="kardex-estado kardex-estado--r{{ $recursamiento->ronda }}">{{ $recursamiento->etiquetaRonda() }}</span></td>
                                    <td>{{ $situacion }}</td>
                                    <td>{{ $recursamiento->calificacion_final !== null ? number_format($recursamiento->calificacion_final, 2) : '—' }}</td>
                                    <td>{{ $recursamiento->fecha_examen?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</section>
@endsection
