@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.grupos.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a grupos
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas {{ isset($grupo) ? 'fa-pen' : 'fa-plus' }}"></i> Datos del grupo</h2>
            </div>
        </div>

        <form method="POST" action="{{ isset($grupo) ? route('admin.grupos.update', $grupo) : route('admin.grupos.store') }}">
            @csrf
            @if (isset($grupo))
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="form-campo">
                    <label for="id_plan">Plan de estudios <span style="color:#0066ff;">*</span></label>
                    <select id="id_plan" name="id_plan" required>
                        <option value="">Selecciona el plan</option>
                        @foreach ($planes as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('id_plan', $grupo->id_plan ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-campo">
                    <label for="id_semestre">Semestre <span style="color:#0066ff;">*</span></label>
                    <select id="id_semestre" name="id_semestre" required>
                        <option value="">Selecciona el semestre</option>
                        @foreach ($semestres as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('id_semestre', $grupo->id_semestre ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-campo" id="campo-area" hidden>
                    <label for="id_area_propedeutica">Área propedéutica <span style="color:#0066ff;">*</span></label>
                    <select id="id_area_propedeutica" name="id_area_propedeutica">
                        <option value="">Selecciona el área</option>
                        @foreach ($areas as $id => $nombre)
                            <option value="{{ $id }}" @selected(old('id_area_propedeutica', $grupo->id_area_propedeutica ?? '') == $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <small>Obligatoria desde 5to semestre. El grupo solo verá materias de esta área y del tronco común.</small>
                </div>

                <div class="form-campo">
                    <label for="nombre">Nombre del grupo <span style="color:#0066ff;">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                           value="{{ old('nombre', $grupo->nombre ?? '') }}"
                           required maxlength="15" pattern="[A-Za-z0-9\-]+" placeholder="Ej. 3-A o A">
                    <small>Debe ser único dentro del plan, semestre y área.</small>
                </div>

                <div class="form-campo">
                    <label for="cupo_maximo">Cupo máximo <span style="color:#0066ff;">*</span></label>
                    <input type="number" id="cupo_maximo" name="cupo_maximo"
                           value="{{ old('cupo_maximo', $grupo->cupo_maximo ?? 45) }}" required min="1" max="100">
                </div>

                <div class="form-campo">
                    <label for="fecha_conclusion">Fecha de conclusión del semestre <span style="color:#0066ff;">*</span></label>
                    <input type="date" id="fecha_conclusion" name="fecha_conclusion"
                           value="{{ old('fecha_conclusion', $grupo->fecha_conclusion?->format('Y-m-d') ?? now()->addMonths(4)->toDateString()) }}"
                           required>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> {{ isset($grupo) ? 'Actualizar grupo' : 'Guardar grupo' }}
                </button>
                <a href="{{ route('admin.grupos.index') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>

    <script>
        (function () {
            const semestresArea = @json($semestresArea);
            const semestre = document.getElementById('id_semestre');
            const campoArea = document.getElementById('campo-area');
            const area = document.getElementById('id_area_propedeutica');

            function alternarArea() {
                const requiere = semestresArea.includes(Number(semestre.value));
                campoArea.hidden = !requiere;
                area.required = requiere;

                if (!requiere) {
                    area.value = '';
                }
            }

            semestre.addEventListener('change', alternarArea);
            alternarArea();
        })();
    </script>
@endsection
