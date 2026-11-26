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
    /** Cada docente sin clases recibe una de estas asignaciones. */
    private const CLASES_INICIALES = [
        1 => ['id_materia' => 1, 'id_grupo' => 1],
        2 => ['id_materia' => 5, 'id_grupo' => 2],
    ];

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
                ->where('id_docente', $idDocente)
                ->where('is_active', true)
                ->orderBy('id_clase')
                ->value('id_clase');

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
        $asignacion = self::CLASES_INICIALES[$idDocente] ?? null;

        if (! $asignacion) {
            return null;
        }

        $idClase = (int) DB::table('clases')->max('id_clase') + 1;

        DB::table('clases')->insertOrIgnore([
            'id_clase' => $idClase,
            'id_materia' => $asignacion['id_materia'],
            'id_docente' => $idDocente,
            'id_grupo' => $asignacion['id_grupo'],
            'is_active' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        return DB::table('clases')
            ->where('id_materia', $asignacion['id_materia'])
            ->where('id_docente', $idDocente)
            ->where('id_grupo', $asignacion['id_grupo'])
            ->value('id_clase');
    }
}
