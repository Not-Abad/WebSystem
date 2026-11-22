<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosAcademicosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('salones')->insertOrIgnore([
            ['id_salon' => 1, 'clave' => 'A-101', 'nombre' => 'Salón de matemáticas', 'capacidad' => 45, 'edificio' => 'Bloque A', 'is_active' => 1],
            ['id_salon' => 2, 'clave' => 'A-102', 'nombre' => 'Salón de lengua', 'capacidad' => 45, 'edificio' => 'Bloque A', 'is_active' => 1],
            ['id_salon' => 3, 'clave' => 'B-201', 'nombre' => 'Salón de humanidades', 'capacidad' => 40, 'edificio' => 'Bloque B', 'is_active' => 1],
            ['id_salon' => 4, 'clave' => 'B-202', 'nombre' => 'Salón de ciencias sociales', 'capacidad' => 40, 'edificio' => 'Bloque B', 'is_active' => 1],
            ['id_salon' => 5, 'clave' => 'LAB-01', 'nombre' => 'Laboratorio de cómputo', 'capacidad' => 35, 'edificio' => 'Bloque C', 'is_active' => 1],
        ]);

        // [id, semestre, clave, nombre, tipo, horas, id_area, tronco_comun]
        $materias = [
            [1, 1, 'LEN-101', 'Lengua y comunicación I', 'Basica', 5, null, 1],
            [2, 1, 'MAT-101', 'Matemáticas I', 'Basica', 5, null, 1],
            [3, 1, 'FCE-101', 'Formación cívica y ética I', 'Basica', 4, null, 1],
            [4, 1, 'PAR-101', 'Actividad para escolar: Deportes', 'Paraescolar', 3, null, 1],
            [5, 2, 'LEN-102', 'Lengua y comunicación II', 'Basica', 5, null, 1],
            [6, 2, 'MAT-102', 'Matemáticas II', 'Basica', 5, null, 1],
            [7, 2, 'BIO-102', 'Biología', 'Basica', 4, null, 1],
            [8, 2, 'PAR-102', 'Actividad para escolar: Arte y cultura', 'Paraescolar', 3, null, 1],
            [9, 3, 'LIT-203', 'Literatura', 'Basica', 5, null, 1],
            [10, 3, 'MAT-203', 'Matemáticas III', 'Basica', 5, null, 1],
            [11, 3, 'QUI-203', 'Química', 'Basica', 4, null, 1],
            [12, 3, 'HIS-203', 'Historia de México', 'Basica', 4, null, 1],
            [13, 4, 'FIS-204', 'Física', 'Basica', 5, null, 1],
            [14, 4, 'GEO-204', 'Geografía', 'Basica', 4, null, 1],
            [15, 4, 'FIL-204', 'Filosofía', 'Basica', 4, null, 1],
            [16, 4, 'PAR-204', 'Actividad para escolar: Tecnología', 'Paraescolar', 3, null, 1],
            [17, 5, 'FMT-305', 'Físico-Matemático', 'Propedeutica', 6, 1, 0],
            [18, 5, 'HUM-305', 'Humanidades', 'Propedeutica', 6, 2, 0],
            [19, 5, 'CSO-305', 'Ciencias Sociales', 'Propedeutica', 6, 3, 0],
            [20, 5, 'CAP-305', 'Capacitación en Informática', 'Capacitacion', 4, null, 1],
            [21, 6, 'FMT-306', 'Físico-Matemático II', 'Propedeutica', 6, 1, 0],
            [22, 6, 'HUM-306', 'Humanidades II', 'Propedeutica', 6, 2, 0],
            [23, 6, 'CSO-306', 'Ciencias Sociales II', 'Propedeutica', 6, 3, 0],
            [24, 6, 'CAP-306', 'Capacitación en Turismo', 'Capacitacion', 4, null, 1],
            [25, 5, 'LEN-305', 'Lengua y comunicación III', 'Basica', 5, null, 1],
            [26, 5, 'MAT-305', 'Matemáticas IV', 'Basica', 5, null, 1],
            [27, 6, 'LEN-306', 'Lengua y comunicación IV', 'Basica', 5, null, 1],
            [28, 6, 'MAT-306', 'Matemáticas V', 'Basica', 5, null, 1],
            [29, 5, 'QUIM-305', 'Temas Selectos de Química I', 'Propedeutica', 3, 4, 0],
            [30, 6, 'QUIM-306', 'Temas Selectos de Química II', 'Propedeutica', 3, 4, 0],
            [31, 5, 'ADM-305', 'Administración I', 'Propedeutica', 3, 5, 0],
            [32, 6, 'ADM-306', 'Administración II', 'Propedeutica', 3, 5, 0],
            [33, 1, 'EDU-101', 'Educación y valores I', 'Basica', 3, null, 1],
            [34, 2, 'EDU-102', 'Educación y valores II', 'Basica', 3, null, 1],
            [35, 3, 'TEC-203', 'Tecnologías de la información I', 'Basica', 3, null, 1],
            [36, 4, 'TEC-204', 'Tecnologías de la información II', 'Basica', 3, null, 1],
            [37, 5, 'HUM-307', 'Temas selectos de humanidades', 'Propedeutica', 3, 2, 0],
            [38, 6, 'CSO-308', 'Temas selectos de ciencias sociales', 'Propedeutica', 3, 3, 0],
        ];

        $filas = array_map(function (array $materia) {
            [$id, $idSemestre, $clave, $nombre, $tipo, $horas, $idArea, $tronco] = $materia;

            return [
                'id_materia' => $id,
                'id_plan' => 1,
                'id_semestre' => $idSemestre,
                'id_capacitacion' => $tipo === 'Capacitacion' ? ($idSemestre === 5 ? 1 : 2) : null,
                'id_area_propedeutica' => $idArea,
                'clave' => $clave,
                'nombre' => $nombre,
                'tipo_materia' => $tipo,
                'es_tronco_comun' => $tronco,
                'horas_semana' => $horas,
                'descripcion' => "Materia del plan DGB-2023 correspondiente al semestre {$idSemestre}.",
                'objetivo' => 'Fortalecer los aprendizajes esperados del semestre.',
                'programa_pdf_ruta' => null,
                'is_active' => 1,
            ];
        }, $materias);

        DB::table('materias')->insertOrIgnore($filas);
    }
}
