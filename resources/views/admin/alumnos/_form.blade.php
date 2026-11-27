<div class="form-grid">
    <div class="form-separador">Datos escolares</div>

    <div class="form-campo">
        <label for="id_semestre">Semestre</label>
        <select id="id_semestre" name="id_semestre">
            <option value="">Sin especificar</option>
            @foreach ($semestres as $id => $nombre)
                <option value="{{ $id }}" @selected(old('id_semestre', $alumno->id_semestre ?? '') == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-campo">
        <label for="id_grupo">Grupo</label>
        <select id="id_grupo" name="id_grupo">
            <option value="">Sin asignar</option>
            @foreach ($grupos as $grupo)
                <option value="{{ $grupo['id'] }}" data-semestre="{{ $grupo['id_semestre'] }}" data-area="{{ $grupo['id_area'] ?? '' }}"
                        @selected(old('id_grupo', $alumno->id_grupo ?? '') == $grupo['id'])>{{ $grupo['etiqueta'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-campo">
        <label for="id_estado_alumno">Estatus escolar</label>
        <select id="id_estado_alumno" name="id_estado_alumno">
            @foreach ($estados as $id => $nombre)
                <option value="{{ $id }}" @selected(old('id_estado_alumno', $alumno->id_estado_alumno ?? \App\Models\EstadoAlumno::PREINSCRITO) == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-campo">
        <label for="id_plan">Plan de estudios</label>
        <select id="id_plan" name="id_plan">
            <option value="">Sin especificar</option>
            @foreach ($planes as $id => $nombre)
                <option value="{{ $id }}" @selected(old('id_plan', $alumno->id_plan ?? '') == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-campo">
        <label for="id_capacitacion">Capacitación (opcional)</label>
        <select id="id_capacitacion" name="id_capacitacion">
            <option value="">Sin capacitación</option>
            @foreach ($capacitaciones as $id => $nombre)
                <option value="{{ $id }}" @selected(old('id_capacitacion', $alumno->id_capacitacion ?? '') == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    @php
        $semestreSeleccionado = (int) old('id_semestre', $alumno->id_semestre ?? 0);
        $mostrarArea = in_array($semestreSeleccionado, $semestresArea, true);
        $mostrarApa = in_array($semestreSeleccionado, $semestresApa, true);
    @endphp

    <div id="campo-area-propedeutica" class="form-campo" @if (! $mostrarArea) style="display: none;" @endif>
        <label for="id_area_propedeutica">Área propedéutica</label>
        <select id="id_area_propedeutica" name="id_area_propedeutica">
            <option value="">Sin especificar</option>
            @foreach ($areas as $id => $nombre)
                <option value="{{ $id }}" @selected(old('id_area_propedeutica', $alumno->id_area_propedeutica ?? '') == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    <div id="campo-apa" class="form-campo" @if (! $mostrarApa) style="display: none;" @endif>
        <label for="actividad_escolar_apa">Actividad para escolar (APA)</label>
        <input type="text" id="actividad_escolar_apa" name="actividad_escolar_apa"
               value="{{ old('actividad_escolar_apa', $alumno->actividad_escolar_apa ?? '') }}"
               maxlength="120" placeholder="Ej. Deportes, arte, tecnología">
    </div>

    <div class="form-campo">
        <label for="promedio_general">Promedio general</label>
        <input type="number" id="promedio_general" name="promedio_general" value="{{ old('promedio_general', $alumno->promedio_general ?? '') }}" min="0" max="10" step="0.01">
    </div>

    <div class="form-separador">Datos personales</div>

    <div class="form-campo">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $alumno->nombre ?? '') }}" required maxlength="60">
    </div>

    <div class="form-campo">
        <label for="apellido_paterno">Apellido paterno</label>
        <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $alumno->apellido_paterno ?? '') }}" required maxlength="60">
    </div>

    <div class="form-campo">
        <label for="apellido_materno">Apellido materno</label>
        <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $alumno->apellido_materno ?? '') }}" maxlength="60">
    </div>

    <div class="form-campo">
        <label for="curp">CURP</label>
        <input type="text" id="curp" name="curp" value="{{ old('curp', $alumno->curp ?? '') }}" maxlength="18" placeholder="ABCD123456HABCDF12">
    </div>

    <div class="form-campo">
        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo" value="{{ old('correo', $alumno->correo ?? '') }}" maxlength="120">
    </div>

    @include('components.ruta-archivo', [
        'id' => 'acta_nacimiento_ruta',
        'name' => 'acta_nacimiento_ruta',
        'etiqueta' => 'Ruta del acta de nacimiento',
        'valor' => old('acta_nacimiento_ruta', $alumno->acta_nacimiento_ruta ?? ''),
        'placeholder' => 'alumnos/acta-01.pdf',
        'requerido' => false,
    ])

    @include('components.ruta-archivo', [
        'id' => 'boleta_secundaria_ruta',
        'name' => 'boleta_secundaria_ruta',
        'etiqueta' => 'Ruta de la boleta de secundaria',
        'valor' => old('boleta_secundaria_ruta', $alumno->boleta_secundaria_ruta ?? ''),
        'placeholder' => 'alumnos/boleta-01.pdf',
        'requerido' => false,
    ])

    <div class="form-separador">Datos de acceso</div>

    <div class="form-campo">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username" value="{{ old('username', $usuario->username ?? '') }}" required maxlength="60" pattern="[A-Za-z0-9._\-]+" autocomplete="off">
    </div>

    <div class="form-campo">
        <label for="password">{{ isset($alumno) ? 'Nueva contraseña (opcional)' : 'Contraseña temporal' }}</label>
        <input type="password" id="password" name="password" {{ isset($alumno) ? '' : 'required' }} minlength="8" autocomplete="new-password">
    </div>

    <div class="form-campo">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" {{ isset($alumno) ? '' : 'required' }} minlength="8" autocomplete="new-password">
    </div>

    @if (isset($alumno))
        <div class="form-campo" style="grid-column: 1 / -1;">
            <label class="form-campo-check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $usuario->is_active))>
                Cuenta activa
            </label>
        </div>
    @endif
</div>

<script>
    (function () {
        const semestre = document.getElementById('id_semestre');
        const grupo = document.getElementById('id_grupo');
        const opcionesGrupo = Array.from(grupo.options).filter(opcion => opcion.value !== '');
        const campoArea = document.getElementById('campo-area-propedeutica');
        const campoApa = document.getElementById('campo-apa');
        const selectArea = campoArea.querySelector('select');
        const semestresArea = @json($semestresArea);
        const semestresApa = @json($semestresApa);

        function filtrarGrupos() {
            const idSemestre = Number(semestre.value);
            const conArea = semestresArea.includes(idSemestre);
            const idArea = Number(selectArea.value);

            opcionesGrupo.forEach(opcion => {
                const coincideSemestre = Number(opcion.dataset.semestre) === idSemestre;
                const coincideArea = !conArea || Number(opcion.dataset.area || 0) === idArea;
                const coincide = coincideSemestre && coincideArea;
                opcion.hidden = !coincide;
                opcion.disabled = !coincide;
            });

            const seleccionActual = grupo.options[grupo.selectedIndex];

            if (! seleccionActual || seleccionActual.value === '' || seleccionActual.disabled) {
                grupo.value = '';
            }
        }

        function alternarCamposSemestre() {
            const idSemestre = Number(semestre.value);
            const conArea = semestresArea.includes(idSemestre);
            const conApa = semestresApa.includes(idSemestre);

            campoArea.style.display = conArea ? '' : 'none';
            campoApa.style.display = conApa ? '' : 'none';

            selectArea.disabled = !conArea;
            campoApa.querySelector('input').disabled = !conApa;
        }

        function alCambiarSemestre() {
            filtrarGrupos();
            alternarCamposSemestre();
        }

        semestre.addEventListener('change', alCambiarSemestre);
        selectArea.addEventListener('change', filtrarGrupos);
        alCambiarSemestre();
    })();
</script>
