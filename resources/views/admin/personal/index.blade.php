@extends('layouts.modulo')

@section('hero-actions')
    <a href="{{ route('admin.personal.create') }}" class="btn-modulo btn-primario">
        <i class="fas fa-user-plus"></i> Nuevo personal
    </a>
@endsection

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-magnifying-glass"></i> Buscar personal</h2>
                <p>Filtra por nombre, matrícula, correo o usuario de acceso.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.personal.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $busqueda }}" placeholder="Nombre, matrícula, correo o usuario">
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
                <h2><i class="fas fa-user-tie"></i> Administradores</h2>
                <p>{{ $administradores->count() }} registro(s)</p>
            </div>
            <a href="{{ route('admin.personal.create') }}" class="btn-modulo btn-secundario">
                <i class="fas fa-plus"></i> Agregar administrador
            </a>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Puesto</th>
                        <th>Matrícula / RFC</th>
                        <th>Correo</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($administradores as $administrador)
                        <tr>
                            <td>
                                <strong>{{ $administrador->nombre }} {{ $administrador->apellido_paterno }} {{ $administrador->apellido_materno }}</strong>
                            </td>
                            <td>{{ $administrador->usuario?->username ?? '—' }}</td>
                            <td>{{ $administrador->puesto?->nombre ?? '—' }}</td>
                            <td>{{ $administrador->matricula_rfc }}</td>
                            <td class="texto-sutil">{{ $administrador->correo_institucional }}</td>
                            <td>
                                @if ($administrador->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Activo</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-circle-pause"></i> Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.personal.edit', $administrador->id_usuario) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($administrador->is_active)
                                        <form method="POST" action="{{ route('admin.personal.destroy', $administrador->id_usuario) }}"
                                              onsubmit="return confirm('¿Dar de baja a {{ $administrador->nombre }} {{ $administrador->apellido_paterno }}?');">
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
                            <td colspan="7" class="vacio">No hay administradores que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-chalkboard-user"></i> Docentes</h2>
                <p>{{ $docentes->count() }} registro(s)</p>
            </div>
            <a href="{{ route('admin.personal.create') }}" class="btn-modulo btn-secundario">
                <i class="fas fa-plus"></i> Agregar docente
            </a>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Capacitación / Área</th>
                        <th>RFC</th>
                        <th>Carga</th>
                        <th>Correo</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docentes as $docente)
                        <tr>
                            <td>
                                <strong>{{ $docente->nombre }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}</strong>
                            </td>
                            <td>{{ $docente->usuario?->username ?? '—' }}</td>
                            <td>
                                {{ $docente->capacitacion?->nombre ?? $docente->area?->nombre ?? '—' }}
                            </td>
                            <td>{{ $docente->rfc ?: $docente->matricula_rfc }}</td>
                            <td><span class="carga-docente">{{ $docente->horas_semanales }} h</span></td>
                            <td class="texto-sutil">{{ $docente->correo_institucional }}</td>
                            <td>
                                @if ($docente->is_active)
                                    <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Activo</span>
                                @else
                                    <span class="estado estado-inactivo"><i class="fas fa-circle-pause"></i> Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.personal.edit', $docente->id_usuario) }}">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    @if ($docente->is_active)
                                        <form method="POST" action="{{ route('admin.personal.destroy', $docente->id_usuario) }}"
                                              onsubmit="return confirm('¿Dar de baja a {{ $docente->nombre }} {{ $docente->apellido_paterno }}?');">
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
                            <td colspan="8" class="vacio">No hay docentes que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p style="color:#57606a;font-size:.8rem;margin-top:20px;text-align:center;">
        Las bajas son lógicas: la cuenta queda inactiva y no se elimina información. Puedes reactivarla desde “Editar”.
    </p>
@endsection
