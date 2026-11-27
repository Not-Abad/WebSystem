<?php

namespace Database\Seeders\Demo;

use App\Models\EstadoAlumno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demostración del progreso persistente: un alumno en reinscripción con el
 * primer semestre cursado y un alumno graduado con el plan completo.
 * No modifica a los alumnos de los grupos existentes para conservar la demo.
 */
class KardexDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::table('planes_estudio')->where('id_plan', 1)->exists()) {
            return;
        }

        $materias = DB::table('materias')
            ->join('semestres', 'semestres.id_semestre', '=', 'materias.id_semestre')
            ->where('materias.id_plan', 1)
            ->orderBy('semestres.numero_semestre')
            ->orderBy('materias.clave')
            ->select('materias.id_materia', 'semestres.numero_semestre as semestre')
            ->get();

        if ($materias->isEmpty()) {
            return;
        }

        $ahora = now()->toDateTimeString();
        $password = Hash::make('Prueba123!');

        $semestreDos = DB::table('semestres')->where('numero_semestre', 2)->value('id_semestre');

        $this->sembrar(
            'alumno.reinscripcion',
            'Alumno',
            'Reinscripción',
            EstadoAlumno::REINSCRIPCION,
            $semestreDos,
            $materias->filter(fn ($materia) => (int) $materia->semestre === 1),
            $password,
            $ahora
        );

        $this->sembrar(
            'alumno.graduado',
            'Alumno',
            'Graduado',
            EstadoAlumno::GRADUADO,
            null,
            $materias,
            $password,
            $ahora
        );
    }

    /** @param \Illuminate\Support\Collection<int, object> $materias */
    private function sembrar(
        string $username,
        string $nombre,
        string $paterno,
        int $estado,
        ?int $idSemestre,
        $materias,
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
        $notas = [];

        foreach ($materias as $indice => $materia) {
            $notas[] = 7 + ($indice % 30) / 10;
        }

        $promedio = $notas === [] ? 0 : round(array_sum($notas) / count($notas), 2);

        if (! $idAlumno) {
            $idAlumno = (int) DB::table('alumnos')->max('id_alumno') + 1;
            DB::table('alumnos')->insert([
                'id_alumno' => $idAlumno,
                'id_usuario' => $idUsuario,
                'id_tutor' => null,
                'id_estado_alumno' => $estado,
                'id_plan' => 1,
                'id_semestre' => $idSemestre,
                'id_capacitacion' => null,
                'id_area_propedeutica' => null,
                'actividad_escolar_apa' => null,
                'id_grupo' => null,
                'nombre' => $nombre,
                'apellido_paterno' => $paterno,
                'apellido_materno' => 'Demo',
                'curp' => null,
                'acta_nacimiento_ruta' => null,
                'boleta_secundaria_ruta' => null,
                'correo' => $username.'@eba-amr.edu.mx',
                'promedio_general' => $estado === EstadoAlumno::GRADUADO ? $promedio : 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        } else {
            DB::table('alumnos')->where('id_alumno', $idAlumno)->update([
                'id_estado_alumno' => $estado,
                'id_plan' => 1,
                'id_semestre' => $idSemestre,
                'id_grupo' => null,
                'promedio_general' => $estado === EstadoAlumno::GRADUADO ? $promedio : 0,
                'updated_at' => $ahora,
            ]);
        }

        DB::table('kardex_materias')->where('id_alumno', $idAlumno)->delete();

        $filas = [];
        foreach ($materias as $indice => $materia) {
            $filas[] = [
                'id_alumno' => $idAlumno,
                'id_materia' => $materia->id_materia,
                'id_plan' => 1,
                'semestre' => (int) $materia->semestre,
                'estatus' => 'cursada',
                'calificacion_final' => $notas[$indice],
                'fecha_finalizacion' => '2026-06-30',
                'id_grupo' => null,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];
        }

        if ($filas !== []) {
            DB::table('kardex_materias')->insert($filas);
        }
    }
}
