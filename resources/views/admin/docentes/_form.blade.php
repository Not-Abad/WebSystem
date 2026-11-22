@php
    $esEdicion = isset($docente);
    $valor = fn (string $campo, mixed $porDefecto = null): mixed => old($campo, $porDefecto);
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($esEdicion)
        @method('PUT')
        <input type="hidden" name="is_active" value="0">
    @endif

    <div class="form-grid">
        <div class="form-separador">Datos personales</div>

        <div class="form-campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ $valor('nombre', $docente->nombre ?? null) }}" required maxlength="60">
        </div>

        <div class="form-campo">
            <label for="apellido_paterno">Apellido paterno</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ $valor('apellido_paterno', $docente->apellido_paterno ?? null) }}" required maxlength="60">
        </div>

        <div class="form-campo">
            <label for="apellido_materno">Apellido materno</label>
            <input type="text" id="apellido_materno" name="apellido_materno" value="{{ $valor('apellido_materno', $docente->apellido_materno ?? null) }}" maxlength="60">
        </div>

        <div class="form-campo">
            <label for="curp">CURP</label>
            <input type="text" id="curp" name="curp" value="{{ $valor('curp', $docente->curp ?? null) }}" required minlength="18" maxlength="18" pattern="[A-Za-z0-9]{18}" style="text-transform:uppercase;" autocomplete="off">
            <small>18 caracteres. Se mostrará en la asignación de materias.</small>
        </div>

        <div class="form-campo">
            <label for="rfc">RFC</label>
            <input type="text" id="rfc" name="rfc" value="{{ $valor('rfc', $docente->rfc ?? null) }}" required maxlength="20" style="text-transform:uppercase;" autocomplete="off">
        </div>

        <div class="form-campo">
            <label for="correo_institucional">Correo institucional</label>
            <input type="email" id="correo_institucional" name="correo_institucional" value="{{ $valor('correo_institucional', $docente->correo_institucional ?? null) }}" required maxlength="120">
        </div>

        <div class="form-separador">Carga horaria y formación académica</div>

        <div class="form-campo">
            <label for="horas_semanales">Horas semanales</label>
            <input type="number" id="horas_semanales" name="horas_semanales" value="{{ $valor('horas_semanales', $docente->horas_semanales ?? 35) }}" required min="1" max="35">
            <small>Tope máximo de bloques de 50 minutos asignables por semana.</small>
        </div>

        <div class="form-campo">
            <label for="licenciatura">Licenciatura</label>
            <input type="text" id="licenciatura" name="licenciatura" value="{{ $valor('licenciatura', $docente->licenciatura ?? null) }}" required maxlength="120">
        </div>

        <div class="form-campo">
            <label for="especialidad">Especialidad (opcional)</label>
            <input type="text" id="especialidad" name="especialidad" value="{{ $valor('especialidad', $docente->especialidad ?? null) }}" maxlength="120">
        </div>

        <div class="form-campo">
            <label for="maestria">Maestría (opcional)</label>
            <input type="text" id="maestria" name="maestria" value="{{ $valor('maestria', $docente->maestria ?? null) }}" maxlength="120">
        </div>

        <div class="form-campo">
            <label for="doctorado">Doctorado (opcional)</label>
            <input type="text" id="doctorado" name="doctorado" value="{{ $valor('doctorado', $docente->doctorado ?? null) }}" maxlength="120">
        </div>

        <div class="form-campo" style="grid-column: 1 / -1;">
            <small>
                Especialidad, maestría y doctorado no son obligatorios. Esta información se muestra al seleccionar
                al docente para una materia; no impide la asignación.
            </small>
        </div>

        <div class="form-separador">Contexto académico (opcional)</div>

        <div class="form-campo">
            <label for="id_capacitacion">Capacitación</label>
            <select id="id_capacitacion" name="id_capacitacion">
                <option value="">Sin capacitación</option>
                @foreach ($capacitaciones as $id => $nombre)
                    <option value="{{ $id }}" @selected($valor('id_capacitacion', $docente->id_capacitacion ?? null) == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-campo">
            <label for="id_area_propedeutica">Área propedéutica</label>
            <select id="id_area_propedeutica" name="id_area_propedeutica">
                <option value="">Sin área</option>
                @foreach ($areas as $id => $nombre)
                    <option value="{{ $id }}" @selected($valor('id_area_propedeutica', $docente->id_area_propedeutica ?? null) == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-separador">Datos de acceso</div>

        <div class="form-campo">
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" value="{{ $valor('username', $usuario->username ?? null) }}" required maxlength="60" pattern="[A-Za-z0-9._\-]+" autocomplete="off">
        </div>

        <div class="form-campo">
            <label for="password">{{ $esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña temporal' }}</label>
            <input type="password" id="password" name="password" @required(! $esEdicion) minlength="8" autocomplete="new-password">
            <small>{{ $esEdicion ? 'Déjala en blanco para conservar la contraseña actual.' : 'Mínimo 8 caracteres. Entrégala al docente por un canal seguro.' }}</small>
        </div>

        <div class="form-campo">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" @required(! $esEdicion) minlength="8" autocomplete="new-password">
        </div>

        @if ($esEdicion)
            <div class="form-campo" style="grid-column: 1 / -1;">
                <label class="form-campo-check">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $docente->is_active))>
                    Docente activo
                </label>
                <small>Si lo desactivas, no podrá iniciar sesión ni aparecerá en el selector de horarios.</small>
            </div>
        @endif
    </div>

    <div class="form-acciones">
        <button type="submit" class="btn-modulo btn-primario">
            <i class="fas fa-floppy-disk"></i> {{ $esEdicion ? 'Guardar cambios' : 'Guardar docente' }}
        </button>
        <a href="{{ route('admin.docentes.index') }}" class="btn-modulo btn-secundario">Cancelar</a>
    </div>
</form>
