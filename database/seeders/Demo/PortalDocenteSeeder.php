<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortalDocenteSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        DB::table('clases')->insertOrIgnore([
            [
                'id_clase' => 1,
                'id_materia' => 14,
                'id_docente' => 4,
                'id_grupo' => 4,
                'is_active' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'id_clase' => 2,
                'id_materia' => 15,
                'id_docente' => 3,
                'id_grupo' => 4,
                'is_active' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);

        DB::table('horarios')->insertOrIgnore([
            ['id_horario' => 1, 'id_clase' => 1, 'dia_semana' => 'Viernes', 'hora_inicio' => '15:00:00', 'hora_fin' => '15:50:00', 'aula' => null, 'id_salon' => 5],
            ['id_horario' => 2, 'id_clase' => 1, 'dia_semana' => 'Viernes', 'hora_inicio' => '17:50:00', 'hora_fin' => '18:40:00', 'aula' => null, 'id_salon' => 5],
            ['id_horario' => 3, 'id_clase' => 2, 'dia_semana' => 'Viernes', 'hora_inicio' => '15:50:00', 'hora_fin' => '16:40:00', 'aula' => null, 'id_salon' => 5],
            ['id_horario' => 4, 'id_clase' => 2, 'dia_semana' => 'Viernes', 'hora_inicio' => '18:40:00', 'hora_fin' => '19:30:00', 'aula' => null, 'id_salon' => 5],
        ]);
    }
}
