@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.personal.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver al control de personal
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-plus"></i> Datos del nuevo personal</h2>
                <p>Se creará una cuenta de acceso con el rol seleccionado.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.personal.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-campo">
                    <label for="tipo">Tipo de personal</label>
                    <select id="tipo" name="tipo">
                        <option value="Administrador" @selected(old('tipo', 'Administrador') === 'Administrador')>Administrador</option>
                        <option value="Docente" @selected(old('tipo') === 'Docente')>Docente</option>
                    </select>
                </div>

                <div class="form-separador">Datos personales</div>

                <div class="form-campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="apellido_paterno">Apellido paterno</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="apellido_materno">Apellido materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="matricula_rfc">Matrícula o RFC</label>
                    <input type="text" id="matricula_rfc" name="matricula_rfc" value="{{ old('matricula_rfc') }}" required maxlength="20">
                </div>

                <div class="form-campo">
                    <label for="correo_institucional">Correo institucional</label>
                    <input type="email" id="correo_institucional" name="correo_institucional" value="{{ old('correo_institucional') }}" required maxlength="120">
                </div>

                <div id="campos-admin">
                    <div class="form-campo">
                        <label for="id_puesto">Puesto administrativo</label>
                        <select id="id_puesto" name="id_puesto">
                            <option value="">Selecciona un puesto</option>
                            @foreach ($puestos as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('id_puesto') == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="campos-docente">
                    <div class="form-campo">
                        <label for="id_capacitacion">Capacitación (opcional)</label>
                        <select id="id_capacitacion" name="id_capacitacion">
                            <option value="">Sin capacitación</option>
                            @foreach ($capacitaciones as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('id_capacitacion') == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-campo">
                        <label for="id_area_propedeutica">Área propedéutica (opcional)</label>
                        <select id="id_area_propedeutica" name="id_area_propedeutica">
                            <option value="">Sin área</option>
                            @foreach ($areas as $id => $nombre)
                                <option value="{{ $id }}" @selected(old('id_area_propedeutica') == $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-separador">Datos de acceso</div>

                <div class="form-campo">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required maxlength="60" pattern="[A-Za-z0-9._\-]+" autocomplete="off">
                </div>

                <div class="form-campo">
                    <label for="password">Contraseña temporal</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    <small>Mínimo 8 caracteres. Entrégala al empleado por un canal seguro.</small>
                </div>

                <div class="form-campo">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar personal
                </button>
                <a href="{{ route('admin.personal.index') }}" class="btn-modulo btn-secundario">
                    Cancelar
                </a>
            </div>
        </form>
    </section>

    <script>
        (function () {
            const tipo = document.getElementById('tipo');
            const secciones = {
                Administrador: document.getElementById('campos-admin'),
                Docente: document.getElementById('campos-docente')
            };

            function alternar() {
                Object.entries(secciones).forEach(([clave, seccion]) => {
                    const activa = clave === tipo.value;
                    seccion.style.display = activa ? 'block' : 'none';
                    seccion.querySelectorAll('input, select, textarea').forEach(campo => {
                        campo.disabled = !activa;
                    });
                });
            }

            tipo.addEventListener('change', alternar);
            alternar();
        })();
    </script>
@endsection
