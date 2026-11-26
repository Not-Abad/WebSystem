<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Garantiza que cada docente tenga, al menos, una clase con evaluaciones,
 * criterios, calificaciones y asistencia en la demostración.
 *
 * Se ejecuta al final de DemoDataSeeder, después de que todos los horarios y
 * grupos ya están cargados. Es idempotente: reutiliza lo que ya existe.
 */
class EvaluacionesCompletasSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        $this->corregirUsuariosDocentes($ahora);

        $demo = new DemoContenidoSeeder();

        $docentes = DB::table('docentes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->where('docentes.is_active', true)
            ->where('usuarios.id_rol', 3)
            ->orderBy('docentes.id_docente')
            ->pluck('docentes.id_docente');

        foreach ($docentes as $idDocente) {
            $idDocente = (int) $idDocente;

            $idClase = DB::table('clases')
                ->join('evaluaciones', 'evaluaciones.id_clase', '=', 'clases.id_clase')
                ->where('clases.id_docente', $idDocente)
                ->where('clases.is_active', true)
                ->orderBy('clases.id_clase')
                ->value('clases.id_clase');

            if (! $idClase) {
                $idClase = $this->crearClaseInicial($idDocente, $ahora);
            }

            if ($idClase) {
                $demo->capturarEvaluaciones($idClase, $ahora);
            }
        }
    }

    /**
     * Las versiones anteriores de la demo reutilizaban cuentas de tutor para
     * algunos docentes. Se asegura de que todo docente tenga su propia cuenta
     * con rol Docente.
     */
    private function corregirUsuariosDocentes($ahora): void
    {
        $docentes = DB::table('docentes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->where('usuarios.id_rol', '!=', 3)
            ->select('docentes.id_docente')
            ->get();

        foreach ($docentes as $docente) {
            $idDocente = (int) $docente->id_docente;
            $idUsuario = 4000 + $idDocente;

            DB::table('usuarios')->insertOrIgnore([
                'id_usuario' => $idUsuario,
                'id_rol' => 3,
                'username' => 'docente.horario'.$idDocente,
                'password_hash' => bcrypt('Prueba123!'),
                'is_active' => 1,
                'intentos_fallidos' => 0,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            DB::table('docentes')
                ->where('id_docente', $idDocente)
                ->update(['id_usuario' => $idUsuario]);
        }
    }

    private function crearClaseInicial(int $idDocente, $ahora): ?int
    {
        // Se busca una materia que todavía no tenga docente asignado en el
        // grupo para no repetir la misma materia con dos profesores.
        foreach (DB::table('grupos')->orderBy('id_grupo')->get() as $grupo) {
            $materia = DB::table('materias')
                ->where('id_plan', 1)
                ->where('id_semestre', $grupo->id_semestre)
                ->where('is_active', true)
                ->whereNotExists(function ($consulta) use ($grupo) {
                    $consulta->select(DB::raw(1))
                        ->from('clases')
                        ->whereColumn('clases.id_materia', 'materias.id_materia')
                        ->where('clases.id_grupo', $grupo->id_grupo)
                        ->where('clases.is_active', true);
                })
                ->orderBy('es_tronco_comun', 'desc')
                ->orderBy('id_materia')
                ->first();

            if (! $materia) {
                continue;
            }

            $idClase = (int) DB::table('clases')->insertGetId([
                'id_materia' => $materia->id_materia,
                'id_docente' => $idDocente,
                'id_grupo' => $grupo->id_grupo,
                'is_active' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            return $idClase;
        }

        return null;
    }
}
