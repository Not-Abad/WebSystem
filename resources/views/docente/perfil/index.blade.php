@extends('layouts.modulo')

@section('content')
@php
    $valor = fn ($campo) => filled($campo) ? (string) $campo : '—';

    $secciones = [
        [
            'titulo' => 'Identificación',
            'icono' => 'fa-fingerprint',
            'campos' => [
                'Clave de docente' => $docente->id_docente,
                'Nombre(s)' => $docente->nombre,
                'Apellido paterno' => $docente->apellido_paterno,
                'Apellido materno' => $docente->apellido_materno,
                'CURP' => $docente->curp,
                'RFC' => $docente->rfc,
                'Matrícula / RFC anterior' => $docente->matricula_rfc,
                'Número de personal SEP' => $docente->numero_personal_sep,
                'Título' => $docente->titulo,
                'Cédula profesional' => $docente->cedula_profesional,
            ],
        ],
        [
            'titulo' => 'Contacto y cuenta',
            'icono' => 'fa-envelope-open-text',
            'campos' => [
                'Correo institucional' => $docente->correo_institucional,
                'Contacto' => $docente->contacto,
                'Usuario de acceso' => $docente->usuario?->username,
                'Estatus del perfil' => $docente->is_active ? 'Activo' : 'Inactivo',
            ],
        ],
        [
            'titulo' => 'Formación y cargo',
            'icono' => 'fa-graduation-cap',
            'campos' => [
                'Licenciatura' => $docente->licenciatura,
                'Especialidad' => $docente->especialidad,
                'Maestría' => $docente->maestria,
                'Doctorado' => $docente->doctorado,
                'Capacitación' => $docente->capacitacion?->nombre,
                'Área propedéutica' => $docente->area?->nombre,
                'Horas semanales' => $docente->horas_semanales,
                'Nombramiento' => $docente->nombramiento,
                'Categoría' => $docente->categoria,
                'Plaza' => $docente->plaza,
                'Sindicato' => $docente->sindicato,
            ],
        ],
    ];
@endphp

<section class="modulo-card">
    <div class="docente-cabecera">
        <div>
            <h2>Mis datos</h2>
        </div>
        <a href="{{ route('docente.horario.index') }}" class="btn-modulo btn-secundario">
            <i class="fas fa-arrow-left"></i> Volver a mi horario
        </a>
    </div>

    <div class="perfil-layout">
        <aside class="perfil-tarjeta">
            @if ($docente->fotoPerfilUrl())
                <img class="perfil-foto" src="{{ $docente->fotoPerfilUrl() }}"
                     alt="Foto de perfil de {{ $docente->nombreCompleto() }}">
            @else
                <div class="perfil-foto perfil-foto-iniciales">{{ $iniciales }}</div>
            @endif

            <h3>{{ $docente->nombreCompleto() }}</h3>
            <p class="perfil-correo">{{ $valor($docente->correo_institucional) }}</p>

            <form class="perfil-formulario" method="POST" action="{{ route('docente.perfil.foto') }}"
                  enctype="multipart/form-data">
                @csrf
                <label for="foto_perfil">Imagen de perfil</label>
                <input type="file" id="foto_perfil" name="foto_perfil"
                       accept="image/jpeg,image/png,image/webp" required>
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-camera"></i> Actualizar foto
                </button>
            </form>
            <p class="perfil-nota">JPG, PNG o WEBP · máximo 2 MB.</p>
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
