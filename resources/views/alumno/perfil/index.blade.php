@extends('layouts.modulo')

@section('content')
@php
    $valor = fn ($campo) => filled($campo) ? (string) $campo : '—';

    $secciones = [
        [
            'titulo' => 'Identificación',
            'icono' => 'fa-fingerprint',
            'campos' => [
                'Clave de alumno' => $alumno->id_alumno,
                'Nombre(s)' => $alumno->nombre,
                'Apellido paterno' => $alumno->apellido_paterno,
                'Apellido materno' => $alumno->apellido_materno,
                'CURP' => $alumno->curp,
                'Correo' => $alumno->correo,
                'Estatus' => $alumno->estado?->nombre ?? '—',
                'Promedio general' => number_format((float) $alumno->promedio_general, 2),
            ],
        ],
        [
            'titulo' => 'Escolaridad',
            'icono' => 'fa-school',
            'campos' => [
                'Grupo' => $alumno->grupo?->etiqueta(),
                'Semestre' => $alumno->semestre?->nombreCompleto() ?? $alumno->grupo?->semestre?->nombreCompleto(),
                'Plan de estudio' => $alumno->plan?->etiqueta() ?? $alumno->grupo?->plan?->etiqueta(),
                'Capacitación' => $alumno->capacitacion?->nombre,
                'Área propedéutica' => $alumno->area?->nombre ?? $alumno->grupo?->area?->nombre,
                'Actividad escolar APA' => $alumno->actividad_escolar_apa,
                'Tutor' => $alumno->tutor
                    ? trim($alumno->tutor->nombre.' '.$alumno->tutor->apellido_paterno.' '.$alumno->tutor->apellido_materno)
                    : null,
            ],
        ],
        [
            'titulo' => 'Cuenta y documentos',
            'icono' => 'fa-folder-open',
            'campos' => [
                'Usuario de acceso' => $alumno->usuario?->username,
                'Acta de nacimiento' => $alumno->acta_nacimiento_ruta,
                'Boleta de secundaria' => $alumno->boleta_secundaria_ruta,
            ],
        ],
    ];
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mis datos</h2>
        </div>
        <a href="{{ route('alumno.materias.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a mis materias
        </a>
    </div>

    <div class="perfil-layout">
        <aside class="perfil-tarjeta">
            <div class="perfil-foto perfil-foto-iniciales">{{ $iniciales }}</div>
            <h3>{{ $alumno->nombreCompleto() }}</h3>
            <p class="perfil-correo">{{ $valor($alumno->correo) }}</p>
            <p class="perfil-nota">
                <i class="fas fa-users"></i> {{ $valor($alumno->grupo?->etiqueta()) }}
            </p>
        </aside>

        <div class="perfil-secciones">
            @foreach ($secciones as $seccion)
                <section class="perfil-seccion">
                    <h3><i class="fas {{ $seccion['icono'] }}"></i> {{ $seccion['titulo'] }}</h3>
                    <dl class="perfil-lista">
                        @foreach ($seccion['campos'] as $etiqueta => $campo)
                            <div class="perfil-campo">
                                <dt>{{ $etiqueta }}</dt>
                                <dd>{{ $valor($campo) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    </div>
</section>
@endsection
