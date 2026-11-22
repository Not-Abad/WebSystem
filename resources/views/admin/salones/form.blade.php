@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.salones.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a salones
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ isset($salon) ? 'fa-pen' : 'fa-plus' }}"></i> Datos del salón</h2>
                <p>La clave y la capacidad se usarán al asignar el salón en el horario.</p>
            </div>
        </div>

        <form method="POST" action="{{ isset($salon) ? route('admin.salones.update', $salon) : route('admin.salones.store') }}">
            @csrf
            @if (isset($salon))
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-campo">
                    <label for="clave">Clave <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="clave" name="clave"
                           value="{{ old('clave', $salon->clave ?? '') }}"
                           required maxlength="20" pattern="[A-Za-z0-9\-]+" placeholder="Ej. A-101">
                </div>

                <div class="form-campo">
                    <label for="nombre">Nombre <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                           value="{{ old('nombre', $salon->nombre ?? '') }}"
                           required maxlength="100" placeholder="Ej. Salón de matemáticas">
                </div>

                <div class="form-campo">
                    <label for="capacidad">Capacidad <span style="color:#0066ff;">*</span></label>
                    <input type="number" id="capacidad" name="capacidad"
                           value="{{ old('capacidad', $salon->capacidad ?? 45) }}" required min="1" max="500">
                </div>

                <div class="form-campo">
                    <label for="edificio">Edificio</label>
                    <input type="text" id="edificio" name="edificio"
                           value="{{ old('edificio', $salon->edificio ?? '') }}"
                           maxlength="60" placeholder="Ej. Bloque A">
                </div>

                @if (isset($salon))
                    <div class="form-campo form-check">
                        <label for="is_active">
                            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $salon->is_active))>
                            Salón activo
                        </label>
                    </div>
                @endif
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> {{ isset($salon) ? 'Actualizar salón' : 'Guardar salón' }}
                </button>
                <a href="{{ route('admin.salones.index') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
