<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id_rol' => 1, 'nombre' => 'SuperUsuario', 'descripcion' => 'Control total, suspensión de personal y consulta exclusiva de bitácora'],
            ['id_rol' => 2, 'nombre' => 'Administrador', 'descripcion' => 'Gestión académica, grupos, horarios, alumnos, tutores y portal CMS'],
            ['id_rol' => 3, 'nombre' => 'Docente', 'descripcion' => 'Definición de criterios de evaluación y captura de calificaciones'],
            ['id_rol' => 4, 'nombre' => 'Alumno', 'descripcion' => 'Consulta de horario, criterios, calificaciones y descarga de boletas'],
            ['id_rol' => 5, 'nombre' => 'Tutor', 'descripcion' => 'Seguimiento académico y consulta de boletas del estudiante tutorado'],
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
    }
}
