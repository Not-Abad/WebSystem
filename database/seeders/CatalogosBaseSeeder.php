<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogos oficiales mínimos necesarios para arrancar el sistema.
 *
 * No contiene personas, cuentas de acceso ni contenido publicable: esos datos
 * se dan de alta desde los módulos administrativos o se importan de la fuente
 * institucional real.
 */
class CatalogosBaseSeeder extends Seeder
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

        DB::table('areas_propedeuticas')->insertOrIgnore([
            ['id_area_propedeutica' => 1, 'nombre' => 'Físico-Matemático', 'is_active' => 1],
            ['id_area_propedeutica' => 2, 'nombre' => 'Humanidades', 'is_active' => 1],
            ['id_area_propedeutica' => 3, 'nombre' => 'Ciencias Sociales', 'is_active' => 1],
            ['id_area_propedeutica' => 4, 'nombre' => 'Químico-Biológico', 'is_active' => 1],
            ['id_area_propedeutica' => 5, 'nombre' => 'Económico-Administrativo', 'is_active' => 1],
        ]);

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
    }
}
