<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id_rol' => 1, 'nombre' => 'SuperUsuario'],
            ['id_rol' => 2, 'nombre' => 'Administrador'],
            ['id_rol' => 3, 'nombre' => 'Docente'],
            ['id_rol' => 4, 'nombre' => 'Alumno'],
            ['id_rol' => 5, 'nombre' => 'Tutor'],
        ]);

        DB::table('puestos_administrativos')->insertOrIgnore([
            ['id_puesto' => 1, 'nombre' => 'secretaria'],
            ['id_puesto' => 2, 'nombre' => 'RRHH'],
            ['id_puesto' => 3, 'nombre' => 'subdirector'],
            ['id_puesto' => 4, 'nombre' => 'director'],
        ]);

        DB::table('estados_alumno')->insertOrIgnore([
            ['id_estado_alumno' => 1, 'nombre' => 'preinscrito'],
            ['id_estado_alumno' => 2, 'nombre' => 'activo'],
            ['id_estado_alumno' => 3, 'nombre' => 'baja temporal'],
            ['id_estado_alumno' => 4, 'nombre' => 'baja definitiva'],
            ['id_estado_alumno' => 5, 'nombre' => 'graduado'],
        ]);

        DB::table('semestres')->insertOrIgnore([
            ['id_semestre' => 1, 'numero_semestre' => 1, 'nombre' => 'Primer Semestre'],
            ['id_semestre' => 2, 'numero_semestre' => 2, 'nombre' => 'Segundo Semestre'],
            ['id_semestre' => 3, 'numero_semestre' => 3, 'nombre' => 'Tercer Semestre'],
            ['id_semestre' => 4, 'numero_semestre' => 4, 'nombre' => 'Cuarto Semestre'],
            ['id_semestre' => 5, 'numero_semestre' => 5, 'nombre' => 'Quinto Semestre'],
            ['id_semestre' => 6, 'numero_semestre' => 6, 'nombre' => 'Sexto Semestre'],
        ]);

        DB::table('capacitaciones')->insertOrIgnore([
            ['id_capacitacion' => 1, 'nombre' => 'Informática', 'is_active' => 1],
            ['id_capacitacion' => 2, 'nombre' => 'Turismo', 'is_active' => 1],
            ['id_capacitacion' => 3, 'nombre' => 'Emprendimiento', 'is_active' => 1],
            ['id_capacitacion' => 4, 'nombre' => 'Contabilidad', 'is_active' => 1],
        ]);

        // Carga los 5 usuarios de prueba con sus perfiles
        $this->call(UsuariosPruebaSeeder::class);

        // Personal adicional y eventos de bitácora para los módulos de SuperUsuario
        $this->call(PersonalBitacoraSeeder::class);

        // Alumnos, tutores y contenido del portal para los módulos de Administrador
        $this->call(AdminModulosSeeder::class);

        // Estructura de carpetas del gestor de archivos
        $this->call(ArchivosSeeder::class);
    }
}