@extends('layouts.modulo')

@section('content')
    <a href="{{ route('admin.tutores.index') }}" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver a tutores
    </a>

    <section class="modulo-card">
        <div class="modulo-card-header">
            <div>
                <h2><i class="fas fa-user-pen"></i> Editar tutor</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.tutores.update', $tutor) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="is_active" value="0">

            <div class="form-grid">
                <div class="form-campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $tutor->nombre) }}" required maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="apellido_paterno">Apellido paterno</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $tutor->apellido_paterno) }}" required maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="apellido_materno">Apellido materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $tutor->apellido_materno) }}" maxlength="60">
                </div>

                <div class="form-campo">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $tutor->telefono) }}" required maxlength="15" pattern="[0-9+\-\s]+">
                </div>

                <div class="form-campo">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" value="{{ old('correo', $tutor->correo) }}" required maxlength="120">
                </div>

                @include('components.ruta-archivo', [
                    'id' => 'ine_ruta',
                    'name' => 'ine_ruta',
                    'etiqueta' => 'Ruta del INE',
                    'valor' => old('ine_ruta', $tutor->ine_ruta),
                    'placeholder' => 'tutores/ine-01.pdf',
                    'requerido' => true,
                ])

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
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tutor->is_active))>
                        Cuenta activa
                    </label>
                    <small>Si la desactivas, el tutor no podrá iniciar sesión pero la información se conserva.</small>
                </div>
            </div>

            <div class="form-acciones">
                <button type="submit" class="btn-modulo btn-primario">
                    <i class="fas fa-floppy-disk"></i> Guardar cambios
                </button>
                <a href="{{ route('admin.tutores.index') }}" class="btn-modulo btn-secundario">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
