@extends('layouts.modulo')

@section('content')
    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-filter"></i> Filtros de seguridad</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.seguridad.index') }}" class="filtros">
            <div class="filtro" style="grid-column: span 2;">
                <label for="q">Búsqueda</label>
                <input type="search" id="q" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Usuario, nombre o correo">
            </div>

            <div class="filtro">
                <label for="estado">Estatus</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activo</option>
                    <option value="bloqueado" @selected(($filtros['estado'] ?? '') === 'bloqueado')>Bloqueado</option>
                    <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivo</option>
                </select>
            </div>

            <div class="filtro">
                <label for="id_rol">Rol</label>
                <select id="id_rol" name="id_rol">
                    <option value="">Todos</option>
                    @foreach ($roles as $id => $nombre)
                        <option value="{{ $id }}" @selected(($filtros['id_rol'] ?? '') == $id)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filtro">
                <button type="submit" class="btn-modulo btn-primario" style="width: 100%;">
                    <i class="fas fa-magnifying-glass"></i> Buscar
                </button>
            </div>
        </form>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-lock"></i> Cuentas registradas</h2>
                <p>{{ $usuarios->total() }} cuenta(s) encontrada(s)</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Estatus</th>
                        <th>Intentos</th>
                        <th>Bloqueado hasta</th>
                        <th>Último acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td><strong>{{ $usuario->username }}</strong></td>
                            <td>{{ $usuario->nombreCompleto() }}</td>
                            <td>{{ $usuario->rol?->nombre ?? '—' }}</td>
                            <td>
                                @switch($usuario->estatusAcceso())
                                    @case('inactivo')
                                        <span class="estado estado-inactivo"><i class="fas fa-circle-pause"></i> Inactivo</span>
                                        @break
                                    @case('bloqueado')
                                        <span class="estado estado-alerta"><i class="fas fa-lock"></i> Bloqueado</span>
                                        @break
                                    @default
                                        <span class="estado estado-activo"><i class="fas fa-circle-check"></i> Activo</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="intentos">
                                    <div class="intentos-bar @class(['alta' => $usuario->intentos_fallidos >= 3])">
                                        <span style="width: {{ min(100, $usuario->intentos_fallidos * 20) }}%;"></span>
                                    </div>
                                    <span class="texto-sutil">{{ $usuario->intentos_fallidos }}/5</span>
                                </div>
                            </td>
                            <td class="texto-sutil">
                                {{ $usuario->estaBloqueado() ? $usuario->bloqueado_hasta?->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="texto-sutil">
                                {{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Sin acceso' }}
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="{{ route('admin.seguridad.show', $usuario) }}">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>

                                    @if ($usuario->id_usuario !== Auth::id())
                                        @if ($usuario->estaBloqueado())
                                            <form method="POST" action="{{ route('admin.seguridad.desbloquear', $usuario) }}">
                                                @csrf
                                                <button type="submit"><i class="fas fa-lock-open"></i> Desbloquear</button>
                                            </form>
                                        @endif

                                        @if ($usuario->intentos_fallidos > 0)
                                            <form method="POST" action="{{ route('admin.seguridad.reiniciar', $usuario) }}">
                                                @csrf
                                                <button type="submit"><i class="fas fa-rotate-left"></i> Reiniciar</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.seguridad.estatus', $usuario) }}">
                                            @csrf
                                            <input type="hidden" name="is_active" value="{{ $usuario->is_active ? 0 : 1 }}">
                                            <button type="submit">
                                                <i class="fas {{ $usuario->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                {{ $usuario->is_active ? 'Suspender' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="vacio">No hay cuentas que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="paginacion">
                {{ $usuarios->links() }}
            </div>
        @endif
    </section>
@endsection
