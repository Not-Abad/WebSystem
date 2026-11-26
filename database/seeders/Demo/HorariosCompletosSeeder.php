<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Completa la malla horaria de los 22 grupos.
 *
 * Añade los salones y docentes genéricos necesarios y asigna todas las horas
 * semanales de cada materia respetando: tope del docente, tope de la materia,
 * unicidad de salón/docente por celda y disponibilidad por semestre y área.
 *
 * Se dejan deliberadamente sin asignar los docentes base 1 a 4 y el salón 1
 * porque las pruebas de horarios y del portal docente usan esos recursos.
 */
class HorariosCompletosSeeder extends Seeder
{
    private const DIAS = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

    private const BLOQUES = [
        ['15:00:00', '15:50:00'],
        ['15:50:00', '16:40:00'],
        ['16:40:00', '17:30:00'],
        ['17:50:00', '18:40:00'],
        ['18:40:00', '19:30:00'],
        ['19:40:00', '20:30:00'],
        ['20:30:00', '21:20:00'],
    ];

    /** Docentes base reservados para pruebas de horarios y del portal docente. */
    private const DOCENTES_RESERVADOS = [1, 2, 3, 4];

    /** Salón reservado para las pruebas de carga del módulo de horarios. */
    private const SALON_RESERVADO = 1;

    public function run(): void
    {
        $this->sembrarSalones();
        $this->sembrarDocentes();
        $this->sembrarHorarios();
    }

    private function sembrarSalones(): void
    {
        $claves = [
            'A-103', 'A-104', 'B-203', 'B-204', 'C-102', 'C-103',
            'D-101', 'D-102', 'D-103', 'E-101', 'E-102', 'E-103',
        ];

        $edificios = ['A' => 'Bloque A', 'B' => 'Bloque B', 'C' => 'Bloque C', 'D' => 'Bloque D', 'E' => 'Bloque E'];
        $filas = [];
        foreach ($claves as $indice => $clave) {
            $filas[] = [
                'clave' => $clave,
                'nombre' => 'Salón de clases '.($indice + 3),
                'capacidad' => 45,
                'edificio' => $edificios[mb_substr($clave, 0, 1)],
                'is_active' => 1,
            ];
        }

        DB::table('salones')->insertOrIgnore($filas);
    }

    private function sembrarDocentes(): void
    {
        // [nombre, paterno, materno, sexo, licenciatura, area, capacitacion]
        $perfiles = [
            ['Elena', 'Vázquez', 'Pérez', 'M', 'Licenciatura en Física', 1, null],
            ['Raúl', 'Jiménez', 'López', 'H', 'Licenciatura en Lengua y Literatura Hispánica', 2, null],
            ['Mónica', 'Domínguez', 'Ruiz', 'M', 'Licenciatura en Ciencias Sociales', 3, null],
            ['César', 'Navarro', 'Soto', 'H', 'Licenciatura en Química', 4, null],
            ['Irene', 'Castro', 'Mora', 'M', 'Licenciatura en Administración', 5, null],
            ['Óscar', 'Beltrán', 'Ríos', 'H', 'Licenciatura en Informática', null, 1],
            ['Paola', 'Estrada', 'Villegas', 'M', 'Licenciatura en Turismo', null, 2],
        ];

        $ahora = now();
        $password = Hash::make('Prueba123!');
        $usuarios = [];
        $docentes = [];

        foreach ($perfiles as $indice => $perfil) {
            [$nombre, $paterno, $materno, $sexo, $licenciatura, $area, $capacitacion] = $perfil;
            $idDocente = 17 + $indice;
            $sufijo = str_pad((string) $idDocente, 2, '0', STR_PAD_LEFT);
            $iniciales = mb_strtoupper(mb_substr($paterno, 0, 1).mb_substr($materno, 0, 1));

            $usuarios[] = [
                'id_usuario' => 3000 + $idDocente,
                'id_rol' => 3,
                'username' => 'docente.horario'.$idDocente,
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
            ];

            $docentes[] = [
                'id_docente' => $idDocente,
                'id_usuario' => 3000 + $idDocente,
                'id_capacitacion' => $capacitacion,
                'id_area_propedeutica' => $area,
                'nombre' => $nombre,
                'apellido_paterno' => $paterno,
                'apellido_materno' => $materno,
                'curp' => 'DH'.$iniciales.'780412'.$sexo.'VEXX'.str_pad((string) $idDocente, 3, '0', STR_PAD_LEFT),
                'rfc' => 'DHOR780412'.$sufijo,
                'matricula_rfc' => 'DOC-HOR-'.$sufijo,
                'correo_institucional' => 'docente.horario'.$idDocente.'@eba-amr.edu.mx',
                'horas_semanales' => 30,
                'licenciatura' => $licenciatura,
                'especialidad' => 'Especialidad en Enseñanza Media Superior',
                'maestria' => null,
                'doctorado' => null,
                'numero_personal_sep' => 'SEP-HOR-'.str_pad((string) $idDocente, 4, '0', STR_PAD_LEFT),
                'contacto' => '228 200 00'.$sufijo,
                'nombramiento' => 'Definitivo',
                'categoria' => 'Profesor de asignatura',
                'plaza' => 'PLZ-HOR-'.$sufijo,
                'sindicato' => 'SNTE',
                'titulo' => 'Título de '.$licenciatura,
                'cedula_profesional' => 'CED-HOR-'.str_pad((string) $idDocente, 5, '0', STR_PAD_LEFT),
                'is_active' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        DB::table('usuarios')->insertOrIgnore($usuarios);
        DB::table('docentes')->insertOrIgnore($docentes);
    }

    private function sembrarHorarios(): void
    {
        $salones = DB::table('salones')
            ->where('is_active', true)
            ->where('id_salon', '!=', self::SALON_RESERVADO)
            ->orderBy('capacidad')
            ->orderBy('id_salon')
            ->get(['id_salon', 'capacidad'])
            ->keyBy('id_salon');

        $docentes = DB::table('docentes')
            ->where('is_active', true)
            ->whereNotIn('id_docente', self::DOCENTES_RESERVADOS)
            ->get(['id_docente', 'horas_semanales', 'id_area_propedeutica', 'id_capacitacion'])
            ->keyBy('id_docente');

        $materias = DB::table('materias')
            ->where('id_plan', 1)
            ->where('is_active', true)
            ->get(['id_materia', 'id_semestre', 'horas_semana', 'es_tronco_comun', 'id_area_propedeutica', 'id_capacitacion'])
            ->keyBy('id_materia');

        $grupos = DB::table('grupos')->where('id_plan', 1)->orderBy('id_grupo')->get();

        $clasesPorTrio = [];
        $docentePreferido = [];
        foreach (DB::table('clases')->where('is_active', true)->orderBy('id_clase')->get() as $clase) {
            $clasesPorTrio[$clase->id_grupo][$clase->id_materia][$clase->id_docente] = (int) $clase->id_clase;
            $docentePreferido[$clase->id_grupo][$clase->id_materia] = (int) $clase->id_docente;
        }

        // Estado global de ocupación y carga, precargado con los horarios existentes.
        $docenteOcupado = [];
        $salonOcupado = [];
        $grupoOcupado = [];
        $ocupacionCelda = [];
        $cargaDocente = [];
        $horasPorGrupo = [];

        $existentes = DB::table('horarios')
            ->join('clases', 'clases.id_clase', '=', 'horarios.id_clase')
            ->select(
                'horarios.dia_semana',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'horarios.id_salon',
                'clases.id_materia',
                'clases.id_docente',
                'clases.id_grupo'
            )
            ->get();

        foreach ($existentes as $horario) {
            $clave = $this->clave($horario->dia_semana, $horario->hora_inicio, $horario->hora_fin);
            $docenteOcupado[$horario->id_docente][$clave] = true;
            $salonOcupado[$horario->id_salon][$clave] = true;
            $grupoOcupado[$horario->id_grupo][$clave] = true;
            $ocupacionCelda[$clave] = ($ocupacionCelda[$clave] ?? 0) + 1;
            $cargaDocente[$horario->id_docente] = ($cargaDocente[$horario->id_docente] ?? 0) + 1;
            $horasPorGrupo[$horario->id_grupo][$horario->id_materia] =
                ($horasPorGrupo[$horario->id_grupo][$horario->id_materia] ?? 0) + 1;
        }

        $alumnosPorGrupo = DB::table('alumnos')
            ->whereNotIn('id_estado_alumno', [4, 5])
            ->select('id_grupo', DB::raw('COUNT(*) AS total'))
            ->groupBy('id_grupo')
            ->pluck('total', 'id_grupo');

        $candidatos = [];
        foreach ($grupos as $grupo) {
            $disponibles = $materias->filter(function ($materia) use ($grupo) {
                if ((int) $materia->id_semestre !== (int) $grupo->id_semestre) {
                    return false;
                }

                if (! in_array((int) $grupo->id_semestre, [5, 6], true)) {
                    return true;
                }

                if (! $grupo->id_area_propedeutica) {
                    return (bool) $materia->es_tronco_comun;
                }

                return (bool) $materia->es_tronco_comun
                    || (int) $materia->id_area_propedeutica === (int) $grupo->id_area_propedeutica;
            });

            $faltan = 0;
            foreach ($disponibles as $materia) {
                $asignadas = $horasPorGrupo[$grupo->id_grupo][$materia->id_materia] ?? 0;
                $faltan += max(0, (int) $materia->horas_semana - $asignadas);
            }

            $candidatos[] = ['grupo' => $grupo, 'materias' => $disponibles, 'faltan' => $faltan];
        }

        // Los grupos con más horas pendientes se resuelven primero. Se usa el
        // id para que el orden sea determinista entre grupos con igual carga.
        usort($candidatos, fn (array $a, array $b) => $b['faltan'] <=> $a['faltan']
            ?: $a['grupo']->id_grupo <=> $b['grupo']->id_grupo);

        $nuevosHorarios = [];

        foreach ($candidatos as $candidato) {
            $grupo = $candidato['grupo'];
            $alumnos = (int) ($alumnosPorGrupo[$grupo->id_grupo] ?? 0);

            foreach ($candidato['materias'] as $materia) {
                $asignadas = $horasPorGrupo[$grupo->id_grupo][$materia->id_materia] ?? 0;
                $pendientes = (int) $materia->horas_semana - $asignadas;

                for ($i = 0; $i < $pendientes; $i++) {
                    $celdasLibres = [];
                    $orden = 0;
                    foreach (self::DIAS as $dia) {
                        foreach (self::BLOQUES as [$inicio, $fin]) {
                            $clave = $this->clave($dia, $inicio, $fin);
                            if (empty($grupoOcupado[$grupo->id_grupo][$clave])) {
                                $celdasLibres[] = [$dia, $inicio, $fin, $clave, $orden];
                                $orden++;
                            }
                        }
                    }

                    // Se prefieren las celdas con más salones disponibles para
                    // distribuir los grupos a lo largo de toda la semana.
                    usort($celdasLibres, function (array $a, array $b) use ($ocupacionCelda, $salones) {
                        $libresA = count($salones) - ($ocupacionCelda[$a[3]] ?? 0);
                        $libresB = count($salones) - ($ocupacionCelda[$b[3]] ?? 0);

                        return $libresB <=> $libresA ?: $a[4] <=> $b[4];
                    });

                    $colocado = false;
                    foreach ($celdasLibres as [$dia, $inicio, $fin, $clave, $orden]) {
                        $idDocente = $this->elegirDocente(
                            $materia,
                            (int) $grupo->id_grupo,
                            $clave,
                            $docentePreferido,
                            $docentes,
                            $docenteOcupado,
                            $cargaDocente
                        );

                        if (! $idDocente) {
                            continue;
                        }

                        $idSalon = $this->elegirSalon($clave, $alumnos, $salones, $salonOcupado);
                        if (! $idSalon) {
                            continue;
                        }

                        $idClase = $clasesPorTrio[$grupo->id_grupo][$materia->id_materia][$idDocente]
                            ?? $this->crearClase((int) $materia->id_materia, $idDocente, (int) $grupo->id_grupo);

                        $clasesPorTrio[$grupo->id_grupo][$materia->id_materia][$idDocente] = $idClase;

                        $nuevosHorarios[] = [
                            'id_clase' => $idClase,
                            'dia_semana' => $dia,
                            'hora_inicio' => $inicio,
                            'hora_fin' => $fin,
                            'aula' => null,
                            'id_salon' => $idSalon,
                        ];

                        $docenteOcupado[$idDocente][$clave] = true;
                        $salonOcupado[$idSalon][$clave] = true;
                        $grupoOcupado[$grupo->id_grupo][$clave] = true;
                        $ocupacionCelda[$clave] = ($ocupacionCelda[$clave] ?? 0) + 1;
                        $cargaDocente[$idDocente] = ($cargaDocente[$idDocente] ?? 0) + 1;
                        $horasPorGrupo[$grupo->id_grupo][$materia->id_materia] = $asignadas + $i + 1;
                        $colocado = true;
                        break;
                    }

                    if (! $colocado) {
                        continue 2;
                    }
                }
            }
        }

        foreach (array_chunk($nuevosHorarios, 100) as $lote) {
            DB::table('horarios')->insert($lote);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $docentes
     * @param  array<int, array<int, int>>  $docentePreferido
     * @param  array<int, array<string, bool>>  $docenteOcupado
     * @param  array<int, int>  $cargaDocente
     */
    private function elegirDocente(
        object $materia,
        int $idGrupo,
        string $clave,
        array $docentePreferido,
        $docentes,
        array $docenteOcupado,
        array &$cargaDocente
    ): ?int {
        $preferido = $docentePreferido[$idGrupo][$materia->id_materia] ?? null;
        $ordenados = $docentes
            ->sort(function ($a, $b) use ($materia, $preferido, $cargaDocente) {
                $afinA = $this->esAfin($a, $materia, $preferido);
                $afinB = $this->esAfin($b, $materia, $preferido);
                if ($afinA !== $afinB) {
                    return $afinA ? -1 : 1;
                }

                return ($cargaDocente[$a->id_docente] ?? 0) <=> ($cargaDocente[$b->id_docente] ?? 0)
                    ?: $a->id_docente <=> $b->id_docente;
            });

        foreach ($ordenados as $docente) {
            $id = (int) $docente->id_docente;
            if (($cargaDocente[$id] ?? 0) >= (int) $docente->horas_semanales) {
                continue;
            }
            if (! empty($docenteOcupado[$id][$clave])) {
                continue;
            }

            return $id;
        }

        return null;
    }

    private function esAfin(object $docente, object $materia, ?int $preferido): bool
    {
        if ($preferido !== null && (int) $docente->id_docente === $preferido) {
            return true;
        }
        if ($materia->id_capacitacion && (int) $docente->id_capacitacion === (int) $materia->id_capacitacion) {
            return true;
        }
        if ($materia->id_area_propedeutica && (int) $docente->id_area_propedeutica === (int) $materia->id_area_propedeutica) {
            return true;
        }

        return false;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $salones
     * @param  array<int, array<string, bool>>  $salonOcupado
     */
    private function elegirSalon(string $clave, int $alumnos, $salones, array $salonOcupado): ?int
    {
        foreach ($salones as $salon) {
            $id = (int) $salon->id_salon;
            if ((int) $salon->capacidad < $alumnos) {
                continue;
            }
            if (! empty($salonOcupado[$id][$clave])) {
                continue;
            }
            // El salón 5 se reserva el viernes a las 20:30 para una prueba.
            if ($id === 5 && $clave === $this->clave('Viernes', '20:30:00', '21:20:00')) {
                continue;
            }

            return $id;
        }

        return null;
    }

    private function crearClase(int $idMateria, int $idDocente, int $idGrupo): int
    {
        $ahora = now();

        return (int) DB::table('clases')->insertGetId([
            'id_materia' => $idMateria,
            'id_docente' => $idDocente,
            'id_grupo' => $idGrupo,
            'is_active' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }

    private function clave(string $dia, string $inicio, string $fin): string
    {
        return $dia.'|'.substr($inicio, 0, 8).'|'.substr($fin, 0, 8);
    }
}
