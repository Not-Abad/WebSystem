<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Alta segura del primer SuperUsuario en una instalación limpia.
 *
 * La contraseña nunca se escribe en la línea de comandos: se solicita de forma
 * interactiva y oculta. No existe ninguna contraseña de demostración en el
 * código ni en los seeders de producción.
 */
class CrearSuperUsuario extends Command
{
    protected $signature = 'usuarios:crear-superadmin {username : Usuario de acceso}';

    protected $description = 'Crea la cuenta inicial de SuperUsuario con contraseña solicitada de forma segura';

    public function handle(): int
    {
        if (User::where('id_rol', Rol::SUPERUSUARIO)->exists()) {
            $this->error('Ya existe una cuenta de SuperUsuario. Usa el módulo de Control de Personal para dar de alta otras cuentas.');

            return self::FAILURE;
        }

        $username = (string) $this->argument('username');

        $validacion = Validator::make(['username' => $username], [
            'username' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:usuarios,username'],
        ], [
            'username.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'username.unique' => 'Ese usuario ya está registrado.',
        ]);

        if ($validacion->fails()) {
            foreach ($validacion->errors()->all() as $mensaje) {
                $this->error($mensaje);
            }

            return self::FAILURE;
        }

        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmacion = (string) $this->secret('Confirma la contraseña');

        if (! hash_equals($password, $confirmacion)) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        $validacion = Validator::make(['password' => $password], [
            'password' => ['required', 'string', 'min:12', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/'],
        ], [
            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula y número.',
        ]);

        if ($validacion->fails()) {
            foreach ($validacion->errors()->all() as $mensaje) {
                $this->error($mensaje);
            }

            return self::FAILURE;
        }

        $ahora = now();
        $id = DB::table('usuarios')->insertGetId([
            'id_rol' => Rol::SUPERUSUARIO,
            'username' => $username,
            'password_hash' => Hash::make($password),
            'is_active' => 1,
            'intentos_fallidos' => 0,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        $this->info("SuperUsuario '{$username}' creado con id {$id}.");
        $this->comment('Ahora entra al sistema y crea el resto del personal desde el módulo correspondiente.');

        return self::SUCCESS;
    }
}
