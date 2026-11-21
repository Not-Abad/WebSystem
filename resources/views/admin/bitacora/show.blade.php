@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.bitacora.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a la bitácora
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-file-circle-check"></i> Evento #{{ $registro->id_bitacora }}</h2>
                <p>Registro inalterable de auditoría.</p>
            </div>
            <a href="{{ route('admin.bitacora.export') }}" class="btn-modulo btn-secundario">
                <i class="fas fa-file-csv"></i> Exportar bitácora
            </a>
        </div>

        <div class="detalle-lista">
            <div class="detalle-item">
                <span>Fecha y hora</span>
                <strong>{{ $registro->created_at?->format('d/m/Y H:i:s') ?? '—' }}</strong>
            </div>

            <div class="detalle-item">
                <span>Usuario</span>
                <strong>{{ $registro->usuario?->username ?? 'Sistema' }}</strong>
            </div>

            <div class="detalle-item">
                <span>Tabla afectada</span>
                <strong>{{ $registro->tabla_afectada }}</strong>
            </div>

            <div class="detalle-item">
                <span>ID del registro</span>
                <strong>{{ $registro->id_registro_afectado ?? '—' }}</strong>
            </div>

            <div class="detalle-item">
                <span>Acción</span>
                <strong>{{ $registro->accion }}</strong>
            </div>

            <div class="detalle-item">
                <span>IP de origen</span>
                <strong>{{ $registro->ip_origen ?? '—' }}</strong>
            </div>
        </div>

        <div class="detalle-descripcion">
            <strong>Descripción:</strong><br>
            {{ $registro->descripcion }}
        </div>
    </section>
@endsection
