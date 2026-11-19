<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Prueba123!');
        $ahora = now();

        // 1. Datos académicos mínimos requeridos por la tabla alumnos
        DB::table('planes_estudio')->insertOrIgnore([
            'id_plan' => 1,
            'clave' => 'DGB-2023',
            'nombre' => 'Plan de Estudios Bachillerato General DGB',
            'anio_inicio' => 2023,
            'is_active' => 1,
        ]);

        DB::table('periodos_academicos')->insertOrIgnore([
            'id_periodo' => 1,
            'clave' => '2026-B',
            'fecha_inicio' => '2026-08-17',
            'fecha_fin' => '2026-12-18',
            'is_active' => 1,
        ]);

        // 2. Cuentas de usuario (1 por cada rol)
        DB::table('usuarios')->insertOrIgnore([
            [
                'id_usuario' => 1,
                'id_rol' => 1, // SuperUsuario
                'username' => 'superadmin',
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'id_usuario' => 2,
                'id_rol' => 2, // Administrador
                'username' => 'admin.escolar',
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'id_usuario' => 3,
                'id_rol' => 3, // Docente
                'username' => 'docente.prueba',
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'id_usuario' => 4,
                'id_rol' => 4, // Alumno
                'username' => 'alumno.prueba',
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'id_usuario' => 5,
                'id_rol' => 5, // Tutor
                'username' => 'tutor.prueba',
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);

        // 3. Perfiles asociados a cada cuenta
        DB::table('administradores')->insertOrIgnore([
            'id_administrador' => 1,
            'id_usuario' => 2,
            'id_puesto' => 1, // secretaria
            'nombre' => 'Jesús',
            'apellido_paterno' => 'Abad',
            'apellido_materno' => 'Romero',
            'matricula_rfc' => 'MESL850412HD1',
            'correo_institucional' => 'admin@eba-amr.edu.mx',
            'is_active' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        DB::table('docentes')->insertOrIgnore([
            'id_docente' => 1,
            'id_usuario' => 3,
            'id_capacitacion' => 1, // Informática
            'id_area_propedeutica' => null,
            'nombre' => 'Felipe Martin',
            'apellido_paterno' => 'Sánchez',
            'apellido_materno' => 'Cortés',
            'matricula_rfc' => 'HEVC800920MV4',
            'correo_institucional' => 'docente@eba-amr.edu.mx',
            'is_active' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        // El Tutor debe insertarse antes que el Alumno por la llave foránea id_tutor
        DB::table('tutores')->insertOrIgnore([
            'id_tutor' => 1,
            'id_usuario' => 5,
            'nombre' => 'Natalia',
            'apellido_paterno' => 'Cruz',
            'apellido_materno' => 'Chiu',
            'telefono' => '2281234567',
            'correo' => 'tutor@ejemplo.com',
            'ine_ruta' => 'private/tutores/ine_prueba.pdf',
            'is_active' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        DB::table('alumnos')->insertOrIgnore([
            'id_alumno' => 1,
            'id_usuario' => 4,
            'id_tutor' => 1,
            'id_estado_alumno' => 2, // activo
            'id_plan' => 1, // DGB-2023
            'id_semestre' => 4, // 4to semestre (permite capacitación, sin área propedéutica)
            'id_capacitacion' => 1, // Informática
            'id_area_propedeutica' => null,
            'matricula' => '2026AMR001',
            'nombre' => 'Manuel Eduardo',
            'apellido_paterno' => 'Morales',
            'apellido_materno' => 'Morales',
            'curp' => 'PEGD080515HVZRRN01',
            'acta_nacimiento_ruta' => 'private/alumnos/acta_prueba.pdf',
            'boleta_secundaria_ruta' => 'private/alumnos/boleta_prueba.pdf',
            'correo' => 'alumno@eba-amr.edu.mx',
            'promedio_general' => 8.90,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }
}