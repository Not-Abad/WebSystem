@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.personal.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver al control de personal
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-pen"></i> Editar {{ $tipo }}</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.personal.update', $usuario) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="is_active" value="0">

            <div class="form-grid">
                <div class="form-separador">Datos personales</div>

                <div class="form-campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $perfil->nombre) }}" required maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="apellido_paterno">Apellido paterno</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $perfil->apellido_paterno) }}" required maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="apellido_materno">Apellido materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $perfil->apellido_materno) }}" maxlength="60">
                </div>

                @if ($tipo === 'Administrador')
                    <div class="form-campo">
                        <label for="matricula_rfc">Matrícula o RFC</label>
                        <input type="text" id="matricula_rfc" name="matricula_rfc" value="{{ old('matricula_rfc', $perfil->matricula_rfc) }}" required maxlength="20">
                    </div>
                @else
                    <div class="form-campo">
                        <label for="curp">CURP</label>
                        <input type="text" id="curp" name="curp" value="{{ old('curp', $perfil->curp) }}" required minlength="18" maxlength="18" pattern="[A-Za-z0-9]{18}" style="text-transform:uppercase;" autocomplete="off">
                    </div>

                    <div class="form-campo">
                        <label for="rfc">RFC</label>
                        <input type="text" id="rfc" name="rfc" value="{{ old('rfc', $perfil->rfc) }}" required maxlength="20" style="text-transform:uppercase;" autocomplete="off">
                    </div>

                    <div class="form-campo">
                        <label for="horas_semanales">Horas semanales</label>
                        <input type="number" id="horas_semanales" name="horas_semanales" value="{{ old('horas_semanales', $perfil->horas_semanales) }}" required min="1" max="35">
                        <small>Tope máximo de bloques asignables por semana.</small>
                    </div>

                    <div class="form-campo">
                        <label for="licenciatura">Licenciatura</label>
                        <input type="text" id="licenciatura" name="licenciatura" value="{{ old('licenciatura', $perfil->licenciatura) }}" required maxlength="120">
                    </div>

                    <div class="form-campo">
                        <label for="especialidad">Especialidad (opcional)</label>
                        <input type="text" id="especialidad" name="especialidad" value="{{ old('especialidad', $perfil->especialidad) }}" maxlength="120">
                    </div>

                    <div class="form-campo">
                        <label for="maestria">Maestría (opcional)</label>
                        <input type="text" id="maestria" name="maestria" value="{{ old('maestria', $perfil->maestria) }}" maxlength="120">
                    </div>

                    <div class="form-campo">
                        <label for="doctorado">Doctorado (opcional)</label>
                        <input type="text" id="doctorado" name="doctorado" value="{{ old('doctorado', $perfil->doctorado) }}" maxlength="120">
                    </div>

                    <div class="form-separador">Ficha institucional y nombramiento (opcional)</div>

                    <div class="form-campo">
                        <label for="numero_personal_sep">Número de personal (SEP)</label>
                        <input type="text" id="numero_personal_sep" name="numero_personal_sep" value="{{ old('numero_personal_sep', $perfil->numero_personal_sep) }}" maxlength="30" style="text-transform:uppercase;">
                    </div>

                    <div class="form-campo">
                        <label for="contacto">Contacto</label>
                        <input type="text" id="contacto" name="contacto" value="{{ old('contacto', $perfil->contacto) }}" maxlength="60" placeholder="Teléfono o extensión">
                    </div>

                    <div class="form-campo">
                        <label for="nombramiento">Nombramiento</label>
                        <input type="text" id="nombramiento" name="nombramiento" value="{{ old('nombramiento', $perfil->nombramiento) }}" maxlength="100">
                    </div>

                    <div class="form-campo">
                        <label for="categoria">Categoría</label>
                        <input type="text" id="categoria" name="categoria" value="{{ old('categoria', $perfil->categoria) }}" maxlength="100">
                    </div>

                    <div class="form-campo">
                        <label for="plaza">Plaza</label>
                        <input type="text" id="plaza" name="plaza" value="{{ old('plaza', $perfil->plaza) }}" maxlength="60">
                    </div>

                    <div class="form-campo">
                        <label for="sindicato">Sindicato</label>
                        <input type="text" id="sindicato" name="sindicato" value="{{ old('sindicato', $perfil->sindicato) }}" maxlength="100">
                    </div>

                    <div class="form-campo">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $perfil->titulo) }}" maxlength="120">
                    </div>

                    <div class="form-campo">
                        <label for="cedula_profesional">Cédula profesional</label>
                        <input type="text" id="cedula_profesional" name="cedula_profesional" value="{{ old('cedula_profesional', $perfil->cedula_profesional) }}" maxlength="40" style="text-transform:uppercase;">
                    </div>
                @endif

                <div class="form-campo">
                    <label for="correo_institucional">Correo institucional</label>
                    <input type="email" id="correo_institucional" name="correo_institucional" value="{{ old('correo_institucional', $perfil->correo_institucional) }}" required maxlength="120">
                </div>

                @if ($tipo === 'Administrador')
                    <div class="form-campo">
                        <label for="id_puesto">Puesto administrativo</label>
                        <select id="id_puesto" name="id_puesto" required>
                            <option value="">Selecciona un puesto</option>
                            @foreach ($puestos as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('id_puesto', $perfil->id_puesto) == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="form-campo">
                        <label for="id_capacitacion">Capacitación (opcional)</label>
                        <select id="id_capacitacion" name="id_capacitacion">
                            <option value="">Sin capacitación</option>
                            @foreach ($capacitaciones as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('id_capacitacion', $perfil->id_capacitacion) == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-campo">
                        <label for="id_area_propedeutica">Área propedéutica (opcional)</label>
                        <select id="id_area_propedeutica" name="id_area_propedeutica">
                            <option value="">Sin área</option>
                            @foreach ($areas as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('id_area_propedeutica', $perfil->id_area_propedeutica) == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-separador">Datos de acceso</div>

                <div class="form-campo">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $usuario->username) }}" required maxlength="60" pattern="[A-Za-z0-9._\-]+" autocomplete="off">
                </div>

                <div class="form-campo">
                    <label for="password">Nueva contraseña (opcional)</label>
                    <input type="password" id="password" name="password" minlength="8" autocomplete="new-password">
                    <small>Déjala en blanco para conservar la contraseña actual.</small>
                </div>

                <div class="form-campo">
                    <label for="password_confirmation">Confirmar nueva contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password">
                </div>

                <div class="form-campo" style="grid-column: 1 / -1;">
                    <label class="form-campo-check">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $perfil->is_active))>
                        Cuenta activa
                    </label>
                    <small>Si la desactivas, la cuenta no podrá iniciar sesión pero la información se conserva.</small>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar cambios
                </button>
                <a href="{{ route('admin.personal.index') }}" class="btn-modulo btn-secundario">
                    Cancelar
                </a>
            </div>
        </form>
    </section>
@endsection
