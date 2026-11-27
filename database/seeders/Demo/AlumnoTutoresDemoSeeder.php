<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sincroniza la tabla pivote con el vínculo principal histórico sembrado en
 * `alumnos.id_tutor`, para que el portal del tutor y el límite de tres tutores
 * usen la misma fuente de verdad en la demo.
 */
class AlumnoTutoresDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now()->toDateTimeString();

        $filas = DB::table('alumnos')
            ->whereNotNull('id_tutor')
            ->select('id_alumno', 'id_tutor')
            ->get()
            ->map(fn ($alumno) => [
                'id_alumno' => $alumno->id_alumno,
                'id_tutor' => $alumno->id_tutor,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ])
            ->all();

        if ($filas !== []) {
            DB::table('alumno_tutores')->insertOrIgnore($filas);
        }
    }
}
