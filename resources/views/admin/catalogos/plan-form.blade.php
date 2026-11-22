@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.catalogos.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a planes
    </a>

    @include('admin.catalogos._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ isset($plan) ? 'fa-pen' : 'fa-file-circle-plus' }}"></i> Datos del plan</h2>
                <p>La clave se usa para identificar el plan en grupos y catálogos.</p>
            </div>
        </div>

        <form method="POST" action="{{ isset($plan) ? route('admin.catalogos.update', $plan) : route('admin.catalogos.store') }}">
            @csrf
            @if (isset($plan))
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-campo">
                    <label for="clave">Clave <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="clave" name="clave" value="{{ old('clave', $plan->clave ?? '') }}"
                           required maxlength="30" pattern="[A-Za-z0-9\-]+" placeholder="Ej. DGB-2023">
                </div>

                <div class="form-campo">
                    <label for="anio_inicio">Año de inicio <span style="color:#0066ff;">*</span></label>
                    <input type="number" id="anio_inicio" name="anio_inicio"
                           value="{{ old('anio_inicio', $plan->anio_inicio ?? date('Y')) }}"
                           required min="1900" max="{{ date('Y') + 5 }}">
                </div>

                <div class="form-campo form-campo-ancho">
                    <label for="nombre">Nombre del plan <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $plan->nombre ?? '') }}"
                           required maxlength="120" placeholder="Ej. Plan de Estudios Bachillerato General DGB">
                </div>

                @if (isset($plan))
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-campo" style="grid-column: 1 / -1;">
                        <label class="form-campo-check">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                            Plan activo
                        </label>
                        <small>Si lo desactivas, deja de estar disponible para nuevos grupos.</small>
                    </div>
                @endif
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> {{ isset($plan) ? 'Actualizar plan' : 'Guardar plan' }}
                </button>
                <a href="{{ route('admin.catalogos.index') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
