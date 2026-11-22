<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Contenido de demostración: grupos completos, docentes genéricos y
 * evaluaciones capturadas para que las plantillas REINCO se puedan mostrar.
 */
class DemoContenidoSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();
        $password = Hash::make('Prueba123!');

        $this->sembrarDocentes($password, $ahora);
        $this->sembrarAlumnos($password, $ahora);
        $this->sembrarClasesYEvaluaciones($ahora);
    }

    private function sembrarDocentes(string $password, $ahora): void
    {
        $perfiles = [
            ['Marta', 'García', 'Ruiz', 'M', 'Licenciatura en Lengua y Literatura Hispánica', null],
            ['Jorge', 'Ramírez', 'Soto', 'H', 'Licenciatura en Matemáticas', 1],
            ['Lucía', 'Torres', 'Vega', 'M', 'Licenciatura en Historia', 3],
            ['Andrés', 'Morales', 'Cruz', 'H', 'Licenciatura en Física', 1],
            ['Fernanda', 'López', 'Pérez', 'M', 'Licenciatura en Química', 4],
            ['Hugo', 'Sánchez', 'Díaz', 'H', 'Licenciatura en Biología', 4],
            ['Karla', 'Martínez', 'Navarro', 'M', 'Licenciatura en Geografía', 3],
            ['Pablo', 'González', 'Herrera', 'H', 'Licenciatura en Filosofía', 2],
            ['Sandra', 'Rivera', 'Medina', 'M', 'Licenciatura en Artes y Cultura', 2],
            ['Ricardo', 'Flores', 'Aguilar', 'H', 'Licenciatura en Informática', 1],
            ['Brenda', 'Reyes', 'Jiménez', 'M', 'Licenciatura en Administración', 5],
            ['Diego', 'Gómez', 'Vargas', 'H', 'Licenciatura en Educación Media Superior', null],
        ];

        $usuarios = [];
        $docentes = [];

        foreach ($perfiles as $indice => $perfil) {
            [$nombre, $paterno, $materno, $sexo, $licenciatura, $area] = $perfil;
            $numero = $indice + 5;
            $idUsuario = 2000 + $numero;
            $sufijo = str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
            $indiceCurp = str_pad((string) $numero, 3, '0', STR_PAD_LEFT);

            $usuarios[] = [
                'id_usuario' => $idUsuario,
                'id_rol' => 3,
                'username' => 'docente.demo'.$numero,
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];

            $docentes[] = [
                'id_docente' => $numero,
                'id_usuario' => $idUsuario,
                'id_capacitacion' => in_array($numero, [10, 14, 15], true) ? 1 : null,
                'id_area_propedeutica' => $area,
                'nombre' => $nombre,
                'apellido_paterno' => $paterno,
                'apellido_materno' => $materno,
                'curp' => 'DD'.mb_strtoupper(mb_substr($paterno, 0, 1)).mb_strtoupper(mb_substr($materno, 0, 1)).'750101'.$sexo.'VEXX'.$indiceCurp,
                'rfc' => 'DEMO800101'.$sufijo,
                'matricula_rfc' => 'DOC-DEMO-'.$sufijo,
                'correo_institucional' => 'docente.demo'.$numero.'@eba-amr.edu.mx',
                'horas_semanales' => 30,
                'licenciatura' => $licenciatura,
                'especialidad' => $indice % 2 === 0 ? null : 'Especialidad en Enseñanza Media Superior',
                'maestria' => null,
                'doctorado' => null,
                'numero_personal_sep' => 'SEP-DEMO-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
                'contacto' => '228 100 00'.$sufijo,
                'nombramiento' => $indice % 2 === 0 ? 'Definitivo' : 'Interino',
                'categoria' => 'Profesor de asignatura',
                'plaza' => 'PLZ-DEMO-'.$sufijo,
                'sindicato' => 'SNTE',
                'titulo' => 'Título de '.$licenciatura,
                'cedula_profesional' => 'CED-DEMO-'.str_pad((string) $numero, 5, '0', STR_PAD_LEFT),
                'is_active' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        DB::table('usuarios')->insertOrIgnore($usuarios);
        DB::table('docentes')->insertOrIgnore($docentes);
    }

    private function sembrarAlumnos(string $password, $ahora): void
    {
        // Un grupo completo por semestre: 7, 2, 3, 4, 11 y 12.
        $grupos = [
            ['id_grupo' => 7, 'semestre' => 1, 'area' => null, 'nuevos' => 45],
            ['id_grupo' => 2, 'semestre' => 2, 'area' => null, 'nuevos' => 44],
            ['id_grupo' => 3, 'semestre' => 3, 'area' => null, 'nuevos' => 44],
            ['id_grupo' => 4, 'semestre' => 4, 'area' => null, 'nuevos' => 44],
            ['id_grupo' => 11, 'semestre' => 5, 'area' => 1, 'nuevos' => 45],
            ['id_grupo' => 12, 'semestre' => 6, 'area' => 1, 'nuevos' => 45],
        ];

        $nombres = [
            'Carlos', 'Fernanda', 'Jorge', 'Lucía', 'María', 'Pedro', 'Sofía', 'Andrés',
            'Brenda', 'Emilio', 'Gabriela', 'Hugo', 'Irma', 'Javier', 'Karla', 'Luis',
            'Marta', 'Néstor', 'Olivia', 'Pablo', 'Ricardo', 'Sandra', 'Tomás', 'Ursula',
            'Víctor', 'Wendy', 'Xavier', 'Yolanda', 'Zoe', 'Rodrigo',
        ];
        $paternos = ['Hernández', 'García', 'Martínez', 'González', 'López', 'Pérez', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Cruz', 'Morales', 'Reyes'];
        $maternos = ['Luna', 'Vargas', 'Cano', 'Vega', 'Santos', 'Mendoza', 'Ruiz', 'Castillo', 'Jiménez', 'Moreno', 'Álvarez', 'Romero', 'Navarro', 'Herrera', 'Medina', 'Aguilar'];
        $apa = ['Deportes', 'Arte y cultura', 'Tecnología', 'Música', 'Lectura'];

        $usuarios = [];
        $alumnos = [];
        $indice = 0;

        foreach ($grupos as $grupo) {
            for ($i = 0; $i < $grupo['nuevos']; $i++) {
                $indice++;
                $numero = 100 + $indice;
                $idUsuario = 1000 + $indice;
                $nombre = $nombres[$indice % count($nombres)];
                $paterno = $paternos[intdiv($indice, count($nombres)) % count($paternos)];
                $materno = $maternos[($indice * 7) % count($maternos)];
                $sexo = in_array($nombre, ['Fernanda', 'Lucía', 'María', 'Sofía', 'Brenda', 'Gabriela', 'Irma', 'Karla', 'Marta', 'Olivia', 'Sandra', 'Ursula', 'Wendy', 'Yolanda', 'Zoe'], true) ? 'M' : 'H';
                $curp = 'DM'.mb_strtoupper(mb_substr($paterno, 0, 1)).mb_strtoupper(mb_substr($materno, 0, 1))
                    .'900101'.$sexo.'VEXX'.str_pad((string) $indice, 3, '0', STR_PAD_LEFT);

                $usuarios[] = [
                    'id_usuario' => $idUsuario,
                    'id_rol' => 4,
                    'username' => 'alumno.demo'.$numero,
                    'password_hash' => $password,
                    'is_active' => 1,
                    'intentos_fallidos' => 0,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $alumnos[] = [
                    'id_alumno' => $numero,
                    'id_usuario' => $idUsuario,
                    'id_tutor' => 1 + ($indice % 4),
                    'id_estado_alumno' => 2,
                    'id_plan' => 1,
                    'id_semestre' => $grupo['semestre'],
                    'id_capacitacion' => null,
                    'id_area_propedeutica' => $grupo['area'],
                    'actividad_escolar_apa' => $grupo['semestre'] <= 4 ? $apa[$indice % count($apa)] : null,
                    'id_grupo' => $grupo['id_grupo'],
                    'nombre' => $nombre,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'curp' => $curp,
                    'acta_nacimiento_ruta' => 'alumnos/demo/acta_'.$numero.'.pdf',
                    'boleta_secundaria_ruta' => 'alumnos/demo/boleta_'.$numero.'.pdf',
                    'correo' => 'alumno.demo'.$numero.'@eba-amr.edu.mx',
                    'promedio_general' => 7 + ($indice % 30) / 10,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        foreach (array_chunk($usuarios, 100) as $lote) {
            DB::table('usuarios')->insertOrIgnore($lote);
        }
        foreach (array_chunk($alumnos, 100) as $lote) {
            DB::table('alumnos')->insertOrIgnore($lote);
        }
    }

    private function sembrarClasesYEvaluaciones($ahora): void
    {
        // [grupo, semestre, id_materia, id_docente]
        $asignaciones = [
            [7, 1, 1, 5], [7, 1, 2, 6], [7, 1, 3, 12], [7, 1, 4, 13], [7, 1, 33, 12],
            [2, 2, 5, 5], [2, 2, 6, 6], [2, 2, 7, 10], [2, 2, 8, 13], [2, 2, 34, 12],
            [3, 3, 9, 5], [3, 3, 10, 6], [3, 3, 11, 9], [3, 3, 12, 7], [3, 3, 35, 14],
            [4, 4, 13, 8], [4, 4, 16, 13], [4, 4, 36, 14],
            [11, 5, 17, 8], [11, 5, 18, 12], [11, 5, 19, 7], [11, 5, 20, 14], [11, 5, 25, 5], [11, 5, 37, 12],
            [12, 6, 21, 8], [12, 6, 22, 12], [12, 6, 23, 7], [12, 6, 24, 14], [12, 6, 27, 5], [12, 6, 38, 7],
        ];

        // Se usan salones 2 a 4 y de martes a viernes para no interferir con
        // las celdas que las pruebas de horarios reservan en salón 1 / lunes.
        $salones = [2, 3, 4];
        $dias = ['Martes', 'Miercoles', 'Jueves', 'Viernes'];
        $bloques = [
            ['15:00:00', '15:50:00'],
            ['15:50:00', '16:40:00'],
            ['16:40:00', '17:30:00'],
            ['17:50:00', '18:40:00'],
            ['18:40:00', '19:30:00'],
            ['19:40:00', '20:30:00'],
            ['20:30:00', '21:20:00'],
        ];

        // Se precarga lo sembrado por PortalDocenteSeeder para no empalmar.
        $celdasSalon = [];
        $celdasDocente = [];
        $celdasGrupo = [];
        foreach (DB::table('horarios')
            ->join('clases', 'clases.id_clase', '=', 'horarios.id_clase')
            ->select('horarios.*', 'clases.id_docente', 'clases.id_grupo')
            ->get() as $horario) {
            $clave = $horario->dia_semana.'|'.substr($horario->hora_inicio, 0, 8).'|'.substr($horario->hora_fin, 0, 8);
            $celdasSalon[$horario->id_salon][$clave] = true;
            $celdasDocente[$horario->id_docente][$clave] = true;
            $celdasGrupo[$horario->id_grupo][$clave] = true;
        }

        $idClase = 3;
        $idHorario = 5;
        $primerasClases = [];

        foreach ($asignaciones as $asignacion) {
            [$idGrupo, $semestre, $idMateria, $idDocente] = $asignacion;

            DB::table('clases')->insertOrIgnore([
                'id_clase' => $idClase,
                'id_materia' => $idMateria,
                'id_docente' => $idDocente,
                'id_grupo' => $idGrupo,
                'is_active' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            [$dia, $inicio, $fin, $idSalon] = $this->siguienteCeldaLibre(
                $dias,
                $bloques,
                $salones,
                (int) $idGrupo,
                (int) $idDocente,
                $celdasGrupo,
                $celdasDocente,
                $celdasSalon
            );

            DB::table('horarios')->insertOrIgnore([
                'id_horario' => $idHorario,
                'id_clase' => $idClase,
                'dia_semana' => $dia,
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
                'aula' => null,
                'id_salon' => $idSalon,
            ]);

            $clave = $dia.'|'.$inicio.'|'.$fin;
            $celdasSalon[$idSalon][$clave] = true;
            $celdasDocente[$idDocente][$clave] = true;
            $celdasGrupo[$idGrupo][$clave] = true;

            if (! isset($primerasClases[$idGrupo])) {
                $primerasClases[$idGrupo] = ['id_clase' => $idClase, 'semestre' => $semestre];
            }

            $idClase++;
            $idHorario++;
        }

        foreach ($primerasClases as $datos) {
            $this->capturarEvaluaciones($datos['id_clase'], $ahora);
        }
    }

    /**
     * @param  array<int, string>  $dias
     * @param  array<int, array<int, string>>  $bloques
     * @param  array<int, int>  $salones
     * @param  array<int, array<string, bool>>  $celdasGrupo
     * @param  array<int, array<string, bool>>  $celdasDocente
     * @param  array<int, array<string, bool>>  $celdasSalon
     * @return array{0: string, 1: string, 2: string, 3: int}
     */
    private function siguienteCeldaLibre(
        array $dias,
        array $bloques,
        array $salones,
        int $idGrupo,
        int $idDocente,
        array $celdasGrupo,
        array $celdasDocente,
        array $celdasSalon
    ): array {
        foreach ($dias as $dia) {
            foreach ($bloques as [$inicio, $fin]) {
                $clave = $dia.'|'.$inicio.'|'.$fin;
                foreach ($salones as $idSalon) {
                    if (isset($celdasGrupo[$idGrupo][$clave])
                        || isset($celdasDocente[$idDocente][$clave])
                        || isset($celdasSalon[$idSalon][$clave])) {
                        continue;
                    }

                    return [$dia, $inicio, $fin, $idSalon];
                }
            }
        }

        throw new \RuntimeException('No hay celdas libres para el grupo '.$idGrupo);
    }

    private function capturarEvaluaciones(int $idClase, $ahora): void
    {
        $tipos = ['ambos', 'examen', 'proyecto'];
        $fechasExamen = ['2026-11-30', '2027-01-20', '2027-03-10'];
        $fechasEntrega = ['2026-12-05', '', '2027-03-15'];
        $ponderaciones = [
            ['Examen', 40], ['Tareas', 20], ['Asistencia', 10], ['Proyecto', 30],
        ];

        foreach ($tipos as $indice => $tipo) {
            $idEvaluacion = DB::table('evaluaciones')->insertGetId([
                'id_clase' => $idClase,
                'parcial' => $indice + 1,
                'tipo' => $tipo,
                'fecha_examen' => $fechasExamen[$indice],
                'fecha_entrega_proyecto' => $fechasEntrega[$indice] ?: null,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            $criterios = [];
            foreach ($ponderaciones as $posicion => [$rubro, $porcentaje]) {
                $idCriterio = DB::table('criterios_evaluacion')->insertGetId([
                    'id_clase' => $idClase,
                    'parcial' => $indice + 1,
                    'id_evaluacion' => $idEvaluacion,
                    'nombre_rubro' => $rubro,
                    'tipo_evidencia' => $rubro,
                    'porcentaje' => $porcentaje,
                    'fecha_entrega' => $fechasEntrega[$indice] ?: null,
                ]);
                $criterios[] = ['id' => $idCriterio, 'peso' => $porcentaje, 'orden' => $posicion];
            }

            $this->capturarCalificaciones($idClase, $criterios, $ahora);
        }

        $this->capturarAsistencias($idClase, $ahora);
    }

    /** @param array<int, array<string, int>> $criterios */
    private function capturarCalificaciones(int $idClase, array $criterios, $ahora): void
    {
        $alumnos = DB::table('alumnos')
            ->join('clases', 'clases.id_grupo', '=', 'alumnos.id_grupo')
            ->where('clases.id_clase', $idClase)
            ->whereIn('alumnos.id_estado_alumno', [1, 2, 3])
            ->select('alumnos.id_alumno')
            ->pluck('id_alumno');

        $filas = [];
        foreach ($alumnos as $idAlumno) {
            foreach ($criterios as $criterio) {
                $obtenido = 65 + (($idAlumno + $criterio['orden'] * 7) % 30);
                $filas[] = [
                    'id_criterio' => $criterio['id'],
                    'id_alumno' => $idAlumno,
                    'porcentaje_obtenido' => $obtenido,
                    'puntaje_obtenido' => round($obtenido * $criterio['peso'] / 1000, 2),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        foreach (array_chunk($filas, 200) as $lote) {
            DB::table('calificaciones')->insertOrIgnore($lote);
        }
    }

    private function capturarAsistencias(int $idClase, $ahora): void
    {
        $alumnos = DB::table('alumnos')
            ->join('clases', 'clases.id_grupo', '=', 'alumnos.id_grupo')
            ->where('clases.id_clase', $idClase)
            ->whereIn('alumnos.id_estado_alumno', [1, 2, 3])
            ->select('alumnos.id_alumno')
            ->pluck('id_alumno');

        $fechas = collect(range(6, 1))->map(fn ($dias) => now()->subDays($dias)->toDateString());
        $filas = [];

        foreach ($fechas as $fecha) {
            foreach ($alumnos as $idAlumno) {
                $estatus = 'presente';
                $observaciones = null;
                if ($idAlumno % 17 === 0) {
                    $estatus = 'falta';
                    $observaciones = 'Inasistencia registrada en la sesión de demostración.';
                } elseif ($idAlumno % 13 === 0) {
                    $estatus = 'retardo';
                } elseif ($idAlumno % 19 === 0) {
                    $estatus = 'justificada';
                    $observaciones = 'Justificación presentada.';
                }

                $filas[] = [
                    'id_clase' => $idClase,
                    'id_alumno' => $idAlumno,
                    'fecha' => $fecha,
                    'estatus' => $estatus,
                    'observaciones' => $observaciones,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        foreach (array_chunk($filas, 200) as $lote) {
            DB::table('asistencias')->insertOrIgnore($lote);
        }
    }
}
