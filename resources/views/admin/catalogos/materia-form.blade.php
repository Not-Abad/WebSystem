@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.catalogos.materias.plan', $plan) }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a materias del plan
    </a>

    @include('admin.catalogos._tabs')

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ isset($materia) ? 'fa-pen' : 'fa-book-bookmark' }}"></i> Datos de la materia</h2>
            </div>
        </div>

        <form method="POST" action="{{ isset($materia) ? route('admin.catalogos.materias.actualizar', $materia) : route('admin.catalogos.materias.guardar', $plan) }}">
            @csrf
            @if (isset($materia))
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-campo">
                    <label for="clave">Clave <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="clave" name="clave" value="{{ old('clave', $materia->clave ?? '') }}"
                           required maxlength="25" pattern="[A-Za-z0-9\-]+" placeholder="Ej. MAT-101">
                </div>

                <div class="form-campo">
                    <label for="id_semestre">Semestre <span style="color:#0066ff;">*</span></label>
                    <select id="id_semestre" name="id_semestre" required>
                        @foreach ($semestres as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('id_semestre', $materia->id_semestre ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-campo form-campo-ancho">
                    <label for="nombre">Nombre de la materia <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $materia->nombre ?? '') }}"
                           required maxlength="120">
                </div>

                <div class="form-campo">
                    <label for="tipo_materia">Tipo de materia <span style="color:#0066ff;">*</span></label>
                    <select id="tipo_materia" name="tipo_materia" required>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_materia', $materia->tipo_materia ?? 'Basica') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-campo">
                    <label for="horas_semana">Horas por semana <span style="color:#0066ff;">*</span></label>
                    <input type="number" id="horas_semana" name="horas_semana"
                           value="{{ old('horas_semana', $materia->horas_semana ?? 5) }}" required min="1" max="40">
                    <small>Límite máximo de bloques de esta materia en el horario.</small>
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <input type="hidden" name="es_tronco_comun" value="0">
                    <label class="form-campo-check">
                        <input type="checkbox" id="es_tronco_comun" name="es_tronco_comun" value="1"
                               @checked(old('es_tronco_comun', $materia->es_tronco_comun ?? false))>
                        Materia de tronco común (visible para todas las áreas desde 5º semestre)
                    </label>
                </div>

                <div id="campo-capacitacion" class="form-campo">
                    <label for="id_capacitacion">Capacitación <span style="color:#0066ff;">*</span></label>
                    <select id="id_capacitacion" name="id_capacitacion">
                        <option value="">Selecciona la capacitación</option>
                        @foreach ($capacitaciones as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('id_capacitacion', $materia->id_capacitacion ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="campo-area" class="form-campo">
                    <label for="id_area_propedeutica">Área propedéutica <span style="color:#0066ff;">*</span></label>
                    <select id="id_area_propedeutica" name="id_area_propedeutica">
                        <option value="">Selecciona el área</option>
                        @foreach ($areas as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('id_area_propedeutica', $materia->id_area_propedeutica ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-campo form-campo-ancho">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $materia->descripcion ?? '') }}</textarea>
                </div>

                <div class="form-campo form-campo-ancho">
                    <label for="objetivo">Objetivo</label>
                    <textarea id="objetivo" name="objetivo" rows="3">{{ old('objetivo', $materia->objetivo ?? '') }}</textarea>
                </div>

                <div class="form-campo-ancho">
                    @include('components.ruta-archivo', [
                        'id' => 'programa_pdf_ruta',
                        'name' => 'programa_pdf_ruta',
                        'etiqueta' => 'Programa (PDF)',
                        'valor' => old('programa_pdf_ruta', $materia->programa_pdf_ruta ?? ''),
                        'placeholder' => 'programas/materia.pdf',
                    ])
                </div>

                @if (isset($materia))
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-campo" style="grid-column: 1 / -1;">
                        <label class="form-campo-check">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $materia->is_active))>
                            Materia activa
                        </label>
                    </div>
                @endif
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> {{ isset($materia) ? 'Actualizar materia' : 'Guardar materia' }}
                </button>
                <a href="{{ route('admin.catalogos.materias.plan', $plan) }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>

    <script>
        (function () {
            const tipo = document.getElementById('tipo_materia');
            const semestre = document.getElementById('id_semestre');
            const tronco = document.getElementById('es_tronco_comun');
            const campoCapacitacion = document.getElementById('campo-capacitacion');
            const campoArea = document.getElementById('campo-area');
            const capacitacion = document.getElementById('id_capacitacion');
            const area = document.getElementById('id_area_propedeutica');
            const semestresArea = @json($semestresArea);

            function alternar() {
                const conCapacitacion = tipo.value === 'Capacitacion';
                campoCapacitacion.style.display = conCapacitacion ? '' : 'none';
                capacitacion.disabled = !conCapacitacion;
                if (conCapacitacion) {
                    capacitacion.setAttribute('required', 'required');
                } else {
                    capacitacion.removeAttribute('required');
                    capacitacion.value = '';
                }

                const conArea = semestresArea.includes(Number(semestre.value)) && ! tronco.checked;
                campoArea.style.display = conArea ? '' : 'none';
                area.disabled = !conArea;
                if (conArea) {
                    area.setAttribute('required', 'required');
                } else {
                    area.removeAttribute('required');
                    area.value = '';
                }
            }

            tipo.addEventListener('change', alternar);
            semestre.addEventListener('change', alternar);
            tronco.addEventListener('change', alternar);
            alternar();
        })();
    </script>
@endsection
