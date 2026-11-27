@extends('layouts.modulo')

@section('content')
<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Examen de {{ $recursamiento->etiquetaRonda() }}</h2>
            <p class="docente-ayuda">Confirmación de alumno, materia, semestre y ronda antes de capturar la calificación.</p>
        </div>
        <a href="{{ route('docente.recursamientos.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a recursamientos
        </a>
    </div>

    <div class="docente-bloque">
        <h3>Identificación del recursamiento</h3>
        <div class="docente-detalle">
            <div>
                <span>Alumno</span>
                <strong>{{ $recursamiento->alumno?->nombreCompleto() ?? '—' }}</strong>
                <small>{{ $recursamiento->alumno?->usuario?->username }} · {{ $recursamiento->alumno?->correo ?? 'Sin correo' }}</small>
            </div>
            <div>
                <span>Materia</span>
                <strong>{{ $recursamiento->materia?->nombre ?? '—' }}</strong>
                <small>{{ $recursamiento->materia?->clave }} · Plan {{ $recursamiento->plan?->etiqueta() ?? '—' }}</small>
            </div>
            <div>
                <span>Semestre</span>
                <strong>{{ $recursamiento->semestre }}</strong>
                <small>Grupo original: {{ $recursamiento->grupoOriginal?->nombre ?? '—' }}</small>
            </div>
            <div>
                <span>Ronda</span>
                <strong>{{ $recursamiento->etiquetaRonda() }}</strong>
                <small>Docente: {{ $recursamiento->docente?->nombreCompleto() ?? '—' }}</small>
            </div>
        </div>
    </div>

    @if ($recursamiento->estatus === 'pendiente')
        <div class="docente-bloque">
            <h3>Calificación del examen</h3>
            <p class="docente-ayuda">
                En {{ $recursamiento->etiquetaRonda() }} se registra una sola calificación final.
                No hay criterios de evaluación ni parciales. Máximo permitido: <strong>{{ $maximo }} %</strong>
                ({{ number_format($maximo / 10, 2) }} / 10). Se aprueba con 6 o más.
            </p>

            <form method="POST" action="{{ route('docente.recursamientos.calificar', $recursamiento) }}" class="docente-formulario">
                @csrf
                <label for="porcentaje">Porcentaje obtenido en el examen</label>
                <input id="porcentaje" name="porcentaje" type="number" min="0" max="{{ $maximo }}" step="0.01"
                       value="{{ old('porcentaje') }}" required>
                <button type="submit" class="btn-modulo">
                    <i class="fas fa-save"></i> Guardar calificación
                </button>
            </form>
        </div>
    @else
        <div class="alumno-calificacion-final">
            <i class="fas fa-circle-check"></i>
            <div>
                <span>Calificación final de {{ $recursamiento->etiquetaRonda() }}</span>
                <strong>{{ number_format((float) $recursamiento->calificacion_final, 2) }}</strong>
                <small>Estatus: {{ $recursamiento->estatus }} · {{ $recursamiento->fecha_examen?->format('d/m/Y') ?? '—' }}</small>
            </div>
        </div>
    @endif

    <div class="docente-bloque">
        <h3>Historial de rondas del alumno en esta materia</h3>
        <div class="tabla-wrap">
            <table class="tabla-docente-grid">
                <thead>
                    <tr>
                        <th>Ronda</th>
                        <th>Materia</th>
                        <th>Semestre</th>
                        <th>Estatus</th>
                        <th>Calificación</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historial as $item)
                        @php
                            $situacion = [
                                'pendiente' => 'Pendiente',
                                'aprobada' => 'Aprobada',
                                'reprobada' => 'Reprobada',
                                'baja_temporal' => 'Baja temporal',
                            ][$item->estatus] ?? $item->estatus;
                        @endphp
                        <tr>
                            <td><span class="kardex-estado kardex-estado--r{{ $item->ronda }}">{{ $item->etiquetaRonda() }}</span></td>
                            <td>{{ $item->materia?->nombre ?? '—' }}</td>
                            <td>{{ $item->semestre }}</td>
                            <td>{{ $situacion }}</td>
                            <td>{{ $item->calificacion_final !== null ? number_format($item->calificacion_final, 2) : '—' }}</td>
                            <td>{{ $item->fecha_examen?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
