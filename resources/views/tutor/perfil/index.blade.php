@extends('layouts.modulo')

@section('content')
@php
    $valor = fn ($campo) => filled($campo) ? (string) $campo : '—';
    $nombre = trim($tutor->nombre.' '.$tutor->apellido_paterno.' '.$tutor->apellido_materno);

    $secciones = [
        [
            'titulo' => 'Identificación',
            'icono' => 'fa-fingerprint',
            'campos' => [
                'Clave de tutor' => $tutor->id_tutor,
                'Nombre(s)' => $tutor->nombre,
                'Apellido paterno' => $tutor->apellido_paterno,
                'Apellido materno' => $tutor->apellido_materno,
                'Correo' => $tutor->correo,
                'Teléfono' => $tutor->telefono,
                'Estatus' => $tutor->is_active ? 'Activo' : 'Inactivo',
            ],
        ],
        [
            'titulo' => 'Cuenta y seguimiento',
            'icono' => 'fa-users',
            'campos' => [
                'Usuario de acceso' => $tutor->usuario?->username,
                'Alumnos bajo tu tutela' => $totalAlumnos,
                'Identificación oficial (INE)' => $tutor->ine_ruta,
                'Alta' => $tutor->created_at?->format('d/m/Y H:i'),
                'Última actualización' => $tutor->updated_at?->format('d/m/Y H:i'),
            ],
        ],
    ];
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mis datos</h2>
        </div>
        <a href="{{ route('tutor.alumnos.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a mis alumnos
        </a>
    </div>

    <div class="perfil-layout">
        <aside class="perfil-tarjeta">
            <div class="perfil-foto perfil-foto-iniciales">{{ $iniciales }}</div>
            <h3>{{ $nombre }}</h3>
            <p class="perfil-correo">{{ $valor($tutor->correo) }}</p>
            <p class="perfil-nota">
                <i class="fas fa-users"></i> {{ $totalAlumnos }} alumno(s) bajo tu tutela
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
