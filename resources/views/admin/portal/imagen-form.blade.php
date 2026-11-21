@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.portal.galeria') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a galería
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ $imagen->exists ? 'fa-pen' : 'fa-plus' }}"></i> {{ $titulo }}</h2>
                <p>{{ $subtitulo }}</p>
            </div>
        </div>

        <form method="POST" action="{{ $accion }}">
            @csrf
            @if ($metodo !== 'POST')
                @method($metodo)
            @endif

            <input type="hidden" name="is_active" value="0">

            <div class="form-grid">
                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $imagen->titulo) }}" required maxlength="120">
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label for="descripcion">Descripción (opcional)</label>
                    <textarea id="descripcion" name="descripcion" rows="3" maxlength="255">{{ old('descripcion', $imagen->descripcion) }}</textarea>
                </div>

                @include('components.ruta-archivo', [
                    'id' => 'imagen_ruta',
                    'name' => 'imagen_ruta',
                    'etiqueta' => 'Ruta de la imagen',
                    'valor' => old('imagen_ruta', $imagen->imagen_ruta),
                    'placeholder' => 'imagenes/galeria/evento-01.jpg',
                    'requerido' => true,
                    'clase' => 'form-campo-ancho',
                ])

                <div class="form-campo">
                    <label for="fecha_subida">Fecha de subida</label>
                    <input type="datetime-local" id="fecha_subida" name="fecha_subida"
                           value="{{ old('fecha_subida', $imagen->fecha_subida?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label class="form-campo-check">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $imagen->exists ? $imagen->is_active : true))>
                        Imagen visible en el portal
                    </label>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar imagen
                </button>
                <a href="{{ route('admin.portal.galeria') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
