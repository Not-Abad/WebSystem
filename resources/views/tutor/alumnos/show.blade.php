@extends('layouts.modulo')

@section('content')
    <a href="{{ route('tutor.alumnos.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a mis alumnos
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-address-card"></i> {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</h2>
                <p>Expediente <strong style="color:#0066ff;">#{{ $alumno->id_alumno }}</strong> · Grupo <strong style="color:#0066ff;">{{ $alumno->grupo?->nombre ?? '—' }}</strong> · Estatus: {{ $alumno->estado?->nombre ?? '—' }}</p>
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
                <span>Tutor asignado</span>
                <strong>
                    @if ($alumno->tutor)
                        {{ $alumno->tutor->nombre }} {{ $alumno->tutor->apellido_paterno }}
                    @else
                        —
                    @endif
                </strong>
            </div>
        </div>
    </section>
@endsection
