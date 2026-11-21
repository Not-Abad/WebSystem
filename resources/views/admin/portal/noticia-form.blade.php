@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.portal.noticias') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a noticias
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ $noticia->exists ? 'fa-pen' : 'fa-plus' }}"></i> {{ $titulo }}</h2>
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
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $noticia->titulo) }}" required maxlength="160">
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label for="contenido">Contenido</label>
                    <textarea id="contenido" name="contenido" rows="8" required minlength="20">{{ old('contenido', $noticia->contenido) }}</textarea>
                </div>

                @include('components.ruta-archivo', [
                    'id' => 'imagen_portada_ruta',
                    'name' => 'imagen_portada_ruta',
                    'etiqueta' => 'Ruta de la imagen de portada (opcional)',
                    'valor' => old('imagen_portada_ruta', $noticia->imagen_portada_ruta),
                    'placeholder' => 'imagenes/noticias/portada.jpg',
                    'requerido' => false,
                ])

                <div class="form-campo">
                    <label for="fecha_publicacion">Fecha de publicación</label>
                    <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion"
                           value="{{ old('fecha_publicacion', $noticia->fecha_publicacion?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label class="form-campo-check">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $noticia->exists ? $noticia->is_active : true))>
                        Noticia visible en el portal
                    </label>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar noticia
                </button>
                <a href="{{ route('admin.portal.noticias') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
