<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Complementa la demo para que no queden grupos vacíos ni grupos sin
 * evaluaciones. Es idempotente: se puede ejecutar sobre una demo ya cargada.
 */
class GruposCompletosSeeder extends Seeder
{
    /** Mínimo de alumnos por grupo; un grupo con menos se considera vacío. */
    private const MINIMO_ALUMNOS = 15;

    private const BASE_ALUMNOS = 600000;
    private const BASE_TUTORES = 700000;
    private const BASE_USUARIOS_ALUMNOS = 800000;
    private const BASE_USUARIOS_TUTORES = 900000;

    public function run(): void
    {
        $ahora = now();

        // Corrige perfiles docentes ligados a cuentas de tutor y crea la clase
        // inicial de quienes aún no tienen una.
        (new EvaluacionesCompletasSeeder())->run();

        $this->sembrarAlumnosYTutores(Hash::make('Prueba123!'), $ahora);
        $this->sembrarEvaluacionesPorGrupo($ahora);
        $this->sembrarEvaluacionesPorDocente($ahora);

        // Unifica la malla al final para que las clases nuevas también queden
        // con un solo docente por materia en cada grupo.
        (new HorariosCompletosSeeder())->unificarDocentePorMateria();
    }

    private function sembrarAlumnosYTutores(string $password, $ahora): void
    {
        $nombres = [
            'Carlos', 'Fernanda', 'Jorge', 'Lucía', 'María', 'Pedro', 'Sofía', 'Andrés',
            'Brenda', 'Emilio', 'Gabriela', 'Hugo', 'Irma', 'Javier', 'Karla', 'Luis',
        ];
        $paternos = ['Hernández', 'García', 'Martínez', 'González', 'López', 'Pérez', 'Sánchez', 'Ramírez'];
        $maternos = ['Luna', 'Vargas', 'Cano', 'Vega', 'Santos', 'Mendoza', 'Ruiz', 'Castillo'];
        $apa = ['Deportes', 'Arte y cultura', 'Tecnología', 'Música', 'Lectura'];

        $grupos = DB::table('grupos')->orderBy('id_grupo')->get();

        foreach ($grupos as $grupo) {
            $idGrupo = (int) $grupo->id_grupo;
            $actuales = (int) DB::table('alumnos')->where('id_grupo', $idGrupo)->count();
            $necesarios = min(self::MINIMO_ALUMNOS, max(0, (int) $grupo->cupo_maximo - $actuales)) - $actuales;

            if ($necesarios <= 0) {
                continue;
            }

            $tutoresUsuarios = [];
            $tutores = [];

            for ($tutor = 1; $tutor <= 2; $tutor++) {
                $idTutor = self::BASE_TUTORES + ($idGrupo * 2) + $tutor;
                $idUsuario = self::BASE_USUARIOS_TUTORES + ($idGrupo * 2) + $tutor;
                $nombre = $nombres[($idGrupo + $tutor) % count($nombres)];
                $paterno = $paternos[($idGrupo + $tutor) % count($paternos)];
                $materno = $maternos[($idGrupo * 2 + $tutor) % count($maternos)];

                $tutoresUsuarios[] = [
                    'id_usuario' => $idUsuario,
                    'id_rol' => 5,
                    'username' => 'tutor.g'.$idGrupo.'_'.$tutor,
                    'password_hash' => $password,
                    'is_active' => 1,
                    'intentos_fallidos' => 0,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $tutores[] = [
                    'id_tutor' => $idTutor,
                    'id_usuario' => $idUsuario,
                    'nombre' => $nombre,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'telefono' => '2283'.str_pad((string) $idTutor, 6, '0', STR_PAD_LEFT),
                    'correo' => 'tutor.g'.$idGrupo.'_'.$tutor.'@eba-amr.edu.mx',
                    'ine_ruta' => 'tutores/ine_grupo_'.$idGrupo.'_'.$tutor.'.pdf',
                    'is_active' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }

            DB::table('usuarios')->insertOrIgnore($tutoresUsuarios);
            DB::table('tutores')->insertOrIgnore($tutores);

            $usuarios = [];
            $alumnos = [];

            for ($numero = $actuales + 1; $numero <= $actuales + $necesarios; $numero++) {
                $idAlumno = self::BASE_ALUMNOS + ($idGrupo * 100) + $numero;
                $idUsuario = self::BASE_USUARIOS_ALUMNOS + ($idGrupo * 100) + $numero;
                $nombre = $nombres[($idGrupo + $numero) % count($nombres)];
                $paterno = $paternos[($idGrupo + $numero) % count($paternos)];
                $materno = $maternos[($idGrupo * 3 + $numero) % count($maternos)];
                $sexo = in_array($nombre, ['Fernanda', 'Lucía', 'María', 'Sofía', 'Brenda', 'Gabriela', 'Irma', 'Karla'], true) ? 'M' : 'H';
                $secuencia = ($idGrupo * 100) + $numero;
                $dia = 1 + intdiv($secuencia, 999);
                $curp = 'DG'.mb_strtoupper(mb_substr($paterno, 0, 1)).mb_strtoupper(mb_substr($materno, 0, 1))
                    .'9001'.str_pad((string) $dia, 2, '0', STR_PAD_LEFT).$sexo.'VEXX'
                    .str_pad((string) (($secuencia % 999) + 1), 3, '0', STR_PAD_LEFT);

                $usuarios[] = [
                    'id_usuario' => $idUsuario,
                    'id_rol' => 4,
                    'username' => 'alumno.g'.$idGrupo.'_'.$numero,
                    'password_hash' => $password,
                    'is_active' => 1,
                    'intentos_fallidos' => 0,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $alumnos[] = [
                    'id_alumno' => $idAlumno,
                    'id_usuario' => $idUsuario,
                    'id_tutor' => self::BASE_TUTORES + ($idGrupo * 2) + (($numero % 2) + 1),
                    'id_estado_alumno' => 2,
                    'id_plan' => $grupo->id_plan,
                    'id_semestre' => $grupo->id_semestre,
                    'id_capacitacion' => null,
                    'id_area_propedeutica' => $grupo->id_area_propedeutica,
                    'id_grupo' => $idGrupo,
                    'nombre' => $nombre,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'curp' => $curp,
                    'acta_nacimiento_ruta' => 'alumnos/demo/acta_'.$idAlumno.'.pdf',
                    'boleta_secundaria_ruta' => 'alumnos/demo/boleta_'.$idAlumno.'.pdf',
                    'correo' => 'alumno.g'.$idGrupo.'_'.$numero.'@eba-amr.edu.mx',
                    'promedio_general' => 7 + ($idAlumno % 30) / 10,
                    'actividad_escolar_apa' => (int) $grupo->id_semestre <= 4
                        ? $apa[$idAlumno % count($apa)]
                        : null,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }

            DB::table('usuarios')->insertOrIgnore($usuarios);
            DB::table('alumnos')->insertOrIgnore($alumnos);
        }
    }

    private function sembrarEvaluacionesPorGrupo($ahora): void
    {
        $demo = new DemoContenidoSeeder();
        $grupos = DB::table('grupos')->orderBy('id_grupo')->pluck('id_grupo');

        foreach ($grupos as $idGrupo) {
            $idGrupo = (int) $idGrupo;

            $clase = DB::table('clases')
                ->join('evaluaciones', 'evaluaciones.id_clase', '=', 'clases.id_clase')
                ->where('clases.id_grupo', $idGrupo)
                ->where('clases.is_active', 1)
                ->select('clases.id_clase')
                ->orderBy('clases.id_clase')
                ->first();

            if (! $clase) {
                $clase = DB::table('clases')
                    ->where('id_grupo', $idGrupo)
                    ->where('is_active', 1)
                    ->select('id_clase')
                    ->orderBy('id_clase')
                    ->first();
            }

            $idClase = $clase ? (int) $clase->id_clase : $this->crearClase($idGrupo, $ahora);

            if ($idClase) {
                $demo->capturarEvaluaciones($idClase, $ahora);
            }
        }
    }

    /**
     * Las calificaciones de la demo se generaron antes de completar los grupos.
     * Se recalculan sobre la primera clase evaluada de cada docente para que
     * nadie se quede con notas de un solo alumno.
     */
    private function sembrarEvaluacionesPorDocente($ahora): void
    {
        $demo = new DemoContenidoSeeder();

        $docentes = DB::table('docentes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->where('docentes.is_active', 1)
            ->where('usuarios.id_rol', 3)
            ->orderBy('docentes.id_docente')
            ->pluck('docentes.id_docente');

        foreach ($docentes as $idDocente) {
            $idDocente = (int) $idDocente;

            $clase = DB::table('clases')
                ->join('evaluaciones', 'evaluaciones.id_clase', '=', 'clases.id_clase')
                ->where('clases.id_docente', $idDocente)
                ->where('clases.is_active', 1)
                ->select('clases.id_clase')
                ->orderBy('clases.id_clase')
                ->first();

            if (! $clase) {
                $clase = DB::table('clases')
                    ->where('id_docente', $idDocente)
                    ->where('is_active', 1)
                    ->select('id_clase')
                    ->orderBy('id_clase')
                    ->first();
            }

            if ($clase) {
                $demo->capturarEvaluaciones((int) $clase->id_clase, $ahora);
            }
        }
    }

    private function crearClase(int $idGrupo, $ahora): ?int
    {
        $docentes = DB::table('docentes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->leftJoin('clases', 'clases.id_docente', '=', 'docentes.id_docente')
            ->where('docentes.is_active', 1)
            ->where('usuarios.id_rol', 3)
            ->groupBy('docentes.id_docente')
            ->selectRaw('docentes.id_docente, COUNT(clases.id_clase) AS total')
            ->orderBy('total')
            ->orderBy('docentes.id_docente')
            ->pluck('docentes.id_docente');

        $materias = DB::table('materias')->orderBy('id_materia')->pluck('id_materia');

        foreach ($docentes as $idDocente) {
            $usadas = DB::table('clases')
                ->where('id_docente', $idDocente)
                ->where('id_grupo', $idGrupo)
                ->pluck('id_materia');

            foreach ($materias as $idMateria) {
                if ($usadas->contains($idMateria)) {
                    continue;
                }

                $idClase = (int) DB::table('clases')->max('id_clase') + 1;

                DB::table('clases')->insertOrIgnore([
                    'id_clase' => $idClase,
                    'id_materia' => $idMateria,
                    'id_docente' => $idDocente,
                    'id_grupo' => $idGrupo,
                    'is_active' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);

                return (int) DB::table('clases')
                    ->where('id_materia', $idMateria)
                    ->where('id_docente', $idDocente)
                    ->where('id_grupo', $idGrupo)
                    ->value('id_clase');
            }
        }

        return null;
    }
}
