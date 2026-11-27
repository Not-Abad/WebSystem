@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.portal.reportes') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a reportes financieros
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ $reporte->exists ? 'fa-pen' : 'fa-plus' }}"></i> {{ $titulo }}</h2>
            </div>
        </div>

        <form method="POST" action="{{ $accion }}">
            @csrf
            @if ($metodo !== 'POST')
                @method($metodo)
            @endif

            <input type="hidden" name="is_active" value="0">

            @php
                $meses = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                ];
            @endphp

            <div class="form-grid">
                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $reporte->titulo) }}" required maxlength="150">
                </div>

                <div class="form-campo">
                    <label for="mes">Mes</label>
                    <select id="mes" name="mes" required>
                        @foreach ($meses as $numero => $nombre)
                            <option value="{{ $numero }}" @selected(old('mes', $reporte->mes) == $numero)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-campo">
                    <label for="anio">Año</label>
                    <input type="number" id="anio" name="anio" value="{{ old('anio', $reporte->anio ?? now()->year) }}" required min="2000" max="2100">
                </div>

                @include('components.ruta-archivo', [
                    'id' => 'archivo_pdf_ruta',
                    'name' => 'archivo_pdf_ruta',
                    'etiqueta' => 'Ruta del PDF',
                    'valor' => old('archivo_pdf_ruta', $reporte->archivo_pdf_ruta),
                    'placeholder' => 'documentos/reportes/2026-01.pdf',
                    'requerido' => true,
                    'clase' => 'form-campo-ancho',
                ])

                <div class="form-campo">
                    <label for="fecha_publicacion">Fecha de publicación</label>
                    <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion"
                           value="{{ old('fecha_publicacion', $reporte->fecha_publicacion?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label class="form-campo-check">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $reporte->exists ? $reporte->is_active : true))>
                        Reporte visible en el portal
                    </label>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar reporte
                </button>
                <a href="{{ route('admin.portal.reportes') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
