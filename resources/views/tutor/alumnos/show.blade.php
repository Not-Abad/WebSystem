@extends('layouts.modulo')

@section('content')
    <a href="{{ route('tutor.alumnos.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a mis alumnos
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-address-card"></i> {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</h2>
            </div>
        </div>

        <div class="detalle-lista">
            <div class="detalle-item">
                <span>Correo</span>
                <strong>{{ $alumno->correo }}</strong>
            </div>
            <div class="detalle-item">
                <span>CURP</span>
                <strong>{{ $alumno->curp }}</strong>
            </div>
            <div class="detalle-item">
                <span>Grupo</span>
                <strong>{{ $alumno->grupo?->etiqueta() ?? '—' }}</strong>
            </div>
            <div class="detalle-item">
                <span>Plan de estudios</span>
                <strong>{{ $alumno->plan?->nombre ?? '—' }}</strong>
            </div>
            <div class="detalle-item">
                <span>Semestre</span>
                <strong>{{ $alumno->semestre?->nombreCompleto() ?? '—' }}</strong>
            </div>
            <div class="detalle-item">
                <span>Capacitación</span>
                <strong>{{ $alumno->capacitacion?->nombre ?? 'No asignada' }}</strong>
            </div>
            @if (in_array($alumno->semestre?->numero_semestre, [5, 6]))
                <div class="detalle-item">
                    <span>Área propedéutica</span>
                    <strong>{{ $alumno->area?->nombre ?? 'No asignada' }}</strong>
                </div>
            @else
                <div class="detalle-item">
                    <span>Actividad para escolar (APA)</span>
                    <strong>{{ $alumno->actividad_escolar_apa ?: 'No asignada' }}</strong>
                </div>
            @endif
            <div class="detalle-item">
                <span>Promedio general</span>
                <strong>{{ number_format((float) $alumno->promedio_general, 2) }}</strong>
            </div>
            <div class="detalle-item">
                <span>Tutores asignados</span>
                <strong>
                    @forelse ($alumno->tutores as $tutorAsignado)
                        {{ $tutorAsignado->nombre }} {{ $tutorAsignado->apellido_paterno }}{{ $loop->last ? '' : ', ' }}
                    @empty
                        {{ $alumno->tutor ? $alumno->tutor->nombre.' '.$alumno->tutor->apellido_paterno : '—' }}
                    @endforelse
                </strong>
            </div>
        </div>

        <div class="docente-cabecera">
            <a href="{{ route('tutor.alumnos.kardex', $alumno) }}" class="btn-modulo">
                <i class="fas fa-file-archive"></i> Ver kardex y recursamientos
            </a>
        </div>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-book-open"></i> Materias del alumno</h2>
            </div>
        </div>

        @if ($clases->isEmpty())
            <div class="docente-vacio">
                <i class="fas fa-book-open"></i>
                <strong>No hay materias activas asignadas al grupo del alumno.</strong>
            </div>
        @else
            <div class="docente-tarjetas">
                @foreach ($clases as $clase)
                    <a class="docente-tarjeta" href="{{ route('tutor.alumnos.materia', [$alumno, $clase]) }}">
                        <span class="docente-tarjeta-clave">{{ $clase->materia->clave }}</span>
                        <strong>{{ $clase->materia->nombre }}</strong>
                        <span>{{ $clase->docente?->nombreCompleto() ?? 'Docente por asignar' }}</span>
                        <span class="docente-tarjeta-pie">
                            {{ $clase->evaluaciones->count() }} evaluación(es)
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
