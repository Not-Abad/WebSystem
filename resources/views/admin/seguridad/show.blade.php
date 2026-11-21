@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.seguridad.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a seguridad de accesos
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-lock"></i> {{ $usuario->username }}</h2>
                <p>{{ $usuario->rol?->nombre ?? 'Sin rol' }} · {{ $usuario->nombreCompleto() }}</p>
            </div>
            <a href="{{ route('admin.bitacora.index', ['id_usuario' => $usuario->id_usuario]) }}" class="btn-modulo btn-secundario">
                <i class="fas fa-file-circle-check"></i> Ver bitácora completa
            </a>
        </div>

        @if ($usuario->id_usuario !== Auth::id())
            <div class="form-acciones" style="margin-bottom: 22px;">
                @if ($usuario->estaBloqueado())
                    <form method="POST" action="{{ route('admin.seguridad.desbloquear', $usuario) }}">
                        @csrf
                        <button type="submit" class="btn-modulo btn-primario">
                            <i class="fas fa-lock-open"></i> Desbloquear cuenta
                        </button>
                    </form>
                @endif

                @if ($usuario->intentos_fallidos > 0)
                    <form method="POST" action="{{ route('admin.seguridad.reiniciar', $usuario) }}">
                        @csrf
                        <button type="submit" class="btn-modulo btn-secundario">
                            <i class="fas fa-rotate-left"></i> Reiniciar intentos
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.seguridad.estatus', $usuario) }}">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $usuario->is_active ? 0 : 1 }}">
                    <button type="submit" class="btn-modulo {{ $usuario->is_active ? 'btn-peligro' : 'btn-primario' }}">
                        <i class="fas {{ $usuario->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                        {{ $usuario->is_active ? 'Suspender cuenta' : 'Activar cuenta' }}
                    </button>
                </form>
            </div>
        @endif

        <div class="detalle-lista">
            <div class="detalle-item">
                <span>Estatus de acceso</span>
                <strong>
                    @switch($usuario->estatusAcceso())
                        @case('inactivo') Inactivo @break
                        @case('bloqueado') Bloqueado @break
                        @default Activo
                    @endswitch
                </strong>
            </div>

            <div class="detalle-item">
                <span>Intentos fallidos</span>
                <strong>{{ $usuario->intentos_fallidos }} de 5</strong>
            </div>

            <div class="detalle-item">
                <span>Bloqueado hasta</span>
                <strong>{{ $usuario->bloqueado_hasta?->format('d/m/Y H:i') ?? 'No bloqueado' }}</strong>
            </div>

            <div class="detalle-item">
                <span>Último acceso</span>
                <strong>{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Sin registro' }}</strong>
            </div>

            <div class="detalle-item">
                <span>Cuenta creada</span>
                <strong>{{ $usuario->created_at?->format('d/m/Y H:i') ?? '—' }}</strong>
            </div>

            <div class="detalle-item">
                <span>Correo del perfil</span>
                <strong>
                    {{ $usuario->administrador?->correo_institucional
                        ?? $usuario->docente?->correo_institucional
                        ?? $usuario->alumno?->correo
                        ?? $usuario->tutor?->correo
                        ?? '—' }}
                </strong>
            </div>
        </div>
    </section>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-clock-rotate-left"></i> Últimos eventos de la cuenta</h2>
                <p>Máximo 10 registros más recientes.</p>
            </div>
        </div>

        <div class="tabla-wrap">
            <table class="tabla-modulo">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eventos as $evento)
                        <tr>
                            <td class="texto-sutil">{{ $evento->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <span @class([
                                    'estado',
                                    'estado-activo' => in_array($evento->accion, ['ALTA', 'INGRESO', 'ACTIVACION', 'DESBLOQUEO', 'REINICIO_INTENTOS'], true),
                                    'estado-alerta' => in_array($evento->accion, ['BAJA', 'SUSPENSION', 'INGRESO_FALLIDO'], true),
                                ])>
                                    {{ $evento->accion }}
                                </span>
                            </td>
                            <td>{{ $evento->tabla_afectada }}</td>
                            <td>{{ $evento->descripcion }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="vacio">Esta cuenta aún no tiene eventos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
