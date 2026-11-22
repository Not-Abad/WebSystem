@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.docentes.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-user-plus"></i> Nuevo docente
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar docente</h2>
                <p>Filtra por nombre, CURP, RFC, personal SEP, nombramiento, sindicato, título, licenciatura o usuario.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.docentes.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Nombre, CURP, RFC, personal SEP, nombramiento, sindicato o usuario">
            </div>
            <div class="filtro">
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-chalkboard-user"></i> Personal docente</h2>
                <p>{{ $docentes->count() }} registro(s). La formación académica se muestra al asignar materias en Horarios.</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo tabla-docentes">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CURP / RFC</th>
                        <th>Personal SEP</th>
                        <th>Nombramiento / Plaza</th>
                        <th>Sindicato / Título / Cédula</th>
                        <th>Carga</th>
                        <th>Licenciatura</th>
                        <th>Formación adicional</th>
                        <th>Correo / Contacto</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docentes as $docente)
                        <tr>
                            <td>
                                <strong>{{ $docente->nombreCompleto() }}</strong>
                                <small class="texto-sutil">{{ $docente->usuario?->username ?? '—' }}</small>
                            </td>
                            <td class="texto-sutil">
                                <strong>{{ $docente->curp ?: 'Sin CURP' }}</strong><br>
                                RFC {{ $docente->rfc ?: '—' }}
                            </td>
                            <td class="texto-sutil">{{ $docente->numero_personal_sep ?: '—' }}</td>
                            <td>
                                <strong>{{ $docente->nombramiento ?: '—' }}</strong>
                                @if ($docente->categoria || $docente->plaza)
                                    <small class="detalle-compacto">
                                        {{ trim(($docente->categoria ?: '').' · '.($docente->plaza ?: ''), ' ·') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <small class="detalle-compacto">Sindicato: {{ $docente->sindicato ?: '—' }}</small>
                                <small class="detalle-compacto">Título: {{ $docente->titulo ?: '—' }}</small>
                                <small class="detalle-compacto">Cédula: {{ $docente->cedula_profesional ?: '—' }}</small>
                            </td>
                            <td>
                                <span class="carga-docente">{{ $docente->horas_semanales }} h</span>
                                <small class="texto-sutil">tope semanal</small>
                            </td>
                            <td>{{ $docente->licenciatura ?: 'No registrada' }}</td>
                            <td>
                                @php
                                    $adicionales = collect($docente->formacionAcademica())
                                        ->reject(fn (string $formacion) => str_starts_with($formacion, 'Licenciatura:'));
                                @endphp
                                @forelse ($adicionales as $formacion)
                                    <small class="detalle-compacto">{{ $formacion }}</small>
                                @empty
                                    <span class="texto-sutil">Sin grados adicionales</span>
                                @endforelse
                            </td>
                            <td class="texto-sutil">
                                {{ $docente->correo_institucional }}
                                @if ($docente->contacto)
                                    <small class="detalle-compacto">Contacto: {{ $docente->contacto }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($docente->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Activo</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-circle-pause"></i> Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.docentes.edit', $docente) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($docente->is_active)
                                        <form method="POST" action="{{ route('admin.docentes.destroy', $docente) }}"
                                              onsubmit="return confirm('¿Dar de baja a {{ $docente->nombreCompleto() }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"><i class="fas fa-user-slash"></i> Dar de baja</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="vacio">No hay docentes que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p class="nota-modulo">
        Las bajas son lógicas: la cuenta queda inactiva y se conserva toda la información del docente.
    </p>
@endsection
