<?php

namespace Database\Seeders\Demo;

use App\Models\EstadoAlumno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo del módulo de R: un alumno en R1 y otro en R2, ambos con el alumno,
 * materia, semestre y docente perfectamente identificados en el módulo docente.
 */
class RecursamientosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $docente = DB::table('docentes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->where('usuarios.username', 'docente.prueba')
            ->select('docentes.id_docente')
            ->first();

        if (! $docente) {
            return;
        }

        $materias = DB::table('materias')
            ->where('id_plan', 1)
            ->where('id_semestre', 1)
            ->orderBy('clave')
            ->limit(2)
            ->select('id_materia')
            ->get();

        if ($materias->count() < 2) {
            return;
        }

        $ahora = now()->toDateTimeString();
        $password = Hash::make('Prueba123!');

        $this->sembrar(
            'alumno.r1',
            'R1',
            $materias->first()->id_materia,
            1,
            1,
            null,
            5.50,
            $docente->id_docente,
            $password,
            $ahora
        );

        $this->sembrar(
            'alumno.r2',
            'R2',
            $materias->last()->id_materia,
            2,
            2,
            1,
            5.20,
            $docente->id_docente,
            $password,
            $ahora
        );
    }

    private function sembrar(
        string $username,
        string $paterno,
        int $idMateria,
        int $ronda,
        int $semestre,
        ?int $rondaAnterior,
        float $calificacion,
        int $idDocente,
        string $password,
        string $ahora
    ): void {
        $idUsuario = DB::table('usuarios')->where('username', $username)->value('id_usuario');

        if (! $idUsuario) {
            $idUsuario = (int) DB::table('usuarios')->max('id_usuario') + 1;
            DB::table('usuarios')->insert([
                'id_usuario' => $idUsuario,
                'id_rol' => 4,
                'username' => $username,
                'password_hash' => $password,
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        $idAlumno = DB::table('alumnos')->where('id_usuario', $idUsuario)->value('id_alumno');
        $datos = [
            'id_tutor' => 1,
            'id_estado_alumno' => EstadoAlumno::REINSCRIPCION,
            'id_plan' => 1,
            'id_semestre' => 2,
            'id_grupo' => null,
            'nombre' => 'Alumno',
            'apellido_paterno' => $paterno,
            'apellido_materno' => 'Demo',
            'correo' => $username.'@eba-amr.edu.mx',
            'promedio_general' => 0,
            'updated_at' => $ahora,
        ];

        if (! $idAlumno) {
            $idAlumno = (int) DB::table('alumnos')->max('id_alumno') + 1;
            DB::table('alumnos')->insert($datos + [
                'id_alumno' => $idAlumno,
                'id_usuario' => $idUsuario,
                'created_at' => $ahora,
            ]);
        } else {
            DB::table('alumnos')->where('id_alumno', $idAlumno)->update($datos);
        }

        DB::table('recursamientos')->where('id_alumno', $idAlumno)->delete();
        DB::table('kardex_materias')->where('id_alumno', $idAlumno)->delete();

        DB::table('kardex_materias')->insert([
            'id_alumno' => $idAlumno,
            'id_materia' => $idMateria,
            'id_plan' => 1,
            'semestre' => $semestre,
            'estatus' => 'r'.$ronda,
            'calificacion_final' => $calificacion,
            'fecha_finalizacion' => '2026-06-30',
            'id_grupo' => null,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        if ($rondaAnterior !== null) {
            DB::table('recursamientos')->insert([
                'id_alumno' => $idAlumno,
                'id_materia' => $idMateria,
                'id_plan' => 1,
                'semestre' => $semestre,
                'id_grupo_original' => null,
                'id_docente' => $idDocente,
                'ronda' => $rondaAnterior,
                'estatus' => 'reprobada',
                'calificacion_final' => $calificacion,
                'fecha_examen' => '2026-07-05',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        DB::table('recursamientos')->insert([
            'id_alumno' => $idAlumno,
            'id_materia' => $idMateria,
            'id_plan' => 1,
            'semestre' => $semestre,
            'id_grupo_original' => null,
            'id_docente' => $idDocente,
            'ronda' => $ronda,
            'estatus' => 'pendiente',
            'calificacion_final' => null,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }
}
