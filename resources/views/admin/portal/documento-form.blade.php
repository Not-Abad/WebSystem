@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.portal.documentos') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a documentos
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ $documento->exists ? 'fa-pen' : 'fa-plus' }}"></i> {{ $titulo }}</h2>
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
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $documento->titulo) }}" required maxlength="150">
                </div>

                <div class="form-campo">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria" required>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria }}" @selected(old('categoria', $documento->categoria) === $categoria)>{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>

                @include('components.ruta-archivo', [
                    'id' => 'archivo_ruta',
                    'name' => 'archivo_ruta',
                    'etiqueta' => 'Ruta del archivo',
                    'valor' => old('archivo_ruta', $documento->archivo_ruta),
                    'placeholder' => 'documentos/convocatorias/archivo.pdf',
                    'requerido' => true,
                ])

                <div class="form-campo">
                    <label for="fecha_subida">Fecha de subida</label>
                    <input type="datetime-local" id="fecha_subida" name="fecha_subida"
                           value="{{ old('fecha_subida', $documento->fecha_subida?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label class="form-campo-check">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $documento->exists ? $documento->is_active : true))>
                        Documento visible en el portal
                    </label>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar documento
                </button>
                <a href="{{ route('admin.portal.documentos') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
