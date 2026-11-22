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

        // Grupos por semestre. Desde 5º se organizan por área propedéutica.
        DB::table('grupos')->insertOrIgnore([
            ['id_grupo' => 1, 'id_semestre' => 1, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '1-A', 'cupo_maximo' => 45],
            ['id_grupo' => 2, 'id_semestre' => 2, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '2-A', 'cupo_maximo' => 45],
            ['id_grupo' => 3, 'id_semestre' => 3, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '3-A', 'cupo_maximo' => 45],
            ['id_grupo' => 4, 'id_semestre' => 4, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '4-A', 'cupo_maximo' => 45],
            ['id_grupo' => 5, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 2, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 6, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 2, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 7, 'id_semestre' => 1, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '1-B', 'cupo_maximo' => 45],
            ['id_grupo' => 8, 'id_semestre' => 2, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '2-B', 'cupo_maximo' => 45],
            ['id_grupo' => 9, 'id_semestre' => 3, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '3-B', 'cupo_maximo' => 45],
            ['id_grupo' => 10, 'id_semestre' => 4, 'id_plan' => 1, 'id_area_propedeutica' => null, 'nombre' => '4-B', 'cupo_maximo' => 45],
            ['id_grupo' => 11, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 1, 'nombre' => 'B', 'cupo_maximo' => 45],
            ['id_grupo' => 12, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 1, 'nombre' => 'B', 'cupo_maximo' => 45],
            ['id_grupo' => 13, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 2, 'nombre' => 'B', 'cupo_maximo' => 45],
            ['id_grupo' => 14, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 2, 'nombre' => 'B', 'cupo_maximo' => 45],
            ['id_grupo' => 15, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 1, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 16, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 1, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 17, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 3, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 18, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 3, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 19, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 4, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 20, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 4, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 21, 'id_semestre' => 5, 'id_plan' => 1, 'id_area_propedeutica' => 5, 'nombre' => 'A', 'cupo_maximo' => 45],
            ['id_grupo' => 22, 'id_semestre' => 6, 'id_plan' => 1, 'id_area_propedeutica' => 5, 'nombre' => 'A', 'cupo_maximo' => 45],
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
            'curp' => 'SACF800920HDFRML08',
            'rfc' => 'HEVC800920MV4',
            'matricula_rfc' => 'HEVC800920MV4',
            'correo_institucional' => 'docente@eba-amr.edu.mx',
            'horas_semanales' => 35,
            'licenciatura' => 'Licenciatura en Matemáticas',
            'especialidad' => 'Especialidad en Enseñanza de las Matemáticas',
            'maestria' => 'Maestría en Educación Media Superior',
            'doctorado' => null,
            'numero_personal_sep' => 'SEP-2023-0148',
            'contacto' => '228 123 4590',
            'nombramiento' => 'Definitivo',
            'categoria' => 'Profesor de asignatura',
            'plaza' => 'PLZ-MAT-014',
            'sindicato' => 'SNTE',
            'titulo' => 'Título de Licenciado en Matemáticas',
            'cedula_profesional' => 'CED-4589210',
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
            'ine_ruta' => 'tutores/ine_prueba.pdf',
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
            'id_semestre' => 4, // 4to semestre (APA en lugar de área propedéutica)
            'id_capacitacion' => 1, // Informática
            'id_area_propedeutica' => null,
            'actividad_escolar_apa' => 'Deportes',
            'id_grupo' => 4,
            'nombre' => 'Manuel Eduardo',
            'apellido_paterno' => 'Morales',
            'apellido_materno' => 'Morales',
            'curp' => 'PEGD080515HVZRRN01',
            'acta_nacimiento_ruta' => 'alumnos/acta_prueba.pdf',
            'boleta_secundaria_ruta' => 'alumnos/boleta_prueba.pdf',
            'correo' => 'alumno@eba-amr.edu.mx',
            'promedio_general' => 8.90,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }
}