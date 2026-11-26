<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HorariosCompletosTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    public function test_todos_los_grupos_tienen_horario_completo(): void
    {
        $grupos = DB::table('grupos')->pluck('id_grupo');
        $porGrupo = DB::table('horarios')
            ->join('clases', 'clases.id_clase', '=', 'horarios.id_clase')
            ->select('clases.id_grupo', DB::raw('COUNT(*) AS total'))
            ->groupBy('clases.id_grupo')
            ->pluck('total', 'id_grupo');

        foreach ($grupos as $idGrupo) {
            $this->assertGreaterThanOrEqual(17, (int) ($porGrupo[$idGrupo] ?? 0), "Grupo {$idGrupo} sin horario completo.");
        }

        $this->assertSame(22, $porGrupo->count());
        $this->assertGreaterThanOrEqual(440, DB::table('horarios')->count());
    }

    public function test_cada_materia_cubre_sus_horas_semanales(): void
    {
        $asignadas = [];
        foreach (DB::table('horarios')
            ->join('clases', 'clases.id_clase', '=', 'horarios.id_clase')
            ->select('clases.id_grupo', 'clases.id_materia', DB::raw('COUNT(*) AS total'))
            ->groupBy('clases.id_grupo', 'clases.id_materia')
            ->get() as $fila) {
            $asignadas[$fila->id_grupo][$fila->id_materia] = (int) $fila->total;
        }

        foreach (DB::table('grupos')->get() as $grupo) {
            foreach (DB::table('materias')
                ->where('id_plan', 1)
                ->where('id_semestre', $grupo->id_semestre)
                ->where('is_active', true)
                ->get() as $materia) {
                if (in_array((int) $grupo->id_semestre, [5, 6], true)) {
                    if (! $grupo->id_area_propedeutica) {
                        if (! $materia->es_tronco_comun) {
                            continue;
                        }
                    } elseif (! $materia->es_tronco_comun
                        && (int) $materia->id_area_propedeutica !== (int) $grupo->id_area_propedeutica) {
                        continue;
                    }
                }

                $this->assertSame(
                    (int) $materia->horas_semana,
                    (int) ($asignadas[$grupo->id_grupo][$materia->id_materia] ?? 0),
                    "Grupo {$grupo->id_grupo}, materia {$materia->clave}."
                );
            }
        }
    }

    public function test_no_hay_empalmes_de_docentes_ni_salones(): void
    {
        $filas = DB::table('horarios')
            ->join('clases', 'clases.id_clase', '=', 'horarios.id_clase')
            ->select('horarios.*', 'clases.id_docente', 'clases.id_grupo')
            ->get();

        $celdasDocente = [];
        $celdasSalon = [];
        $celdasGrupo = [];
        $carga = [];

        foreach ($filas as $fila) {
            $celda = $fila->dia_semana.'|'.$fila->hora_inicio.'|'.$fila->hora_fin;
            $this->assertFalse(isset($celdasDocente[$fila->id_docente][$celda]));
            $this->assertFalse(isset($celdasSalon[$fila->id_salon][$celda]));
            $this->assertFalse(isset($celdasGrupo[$fila->id_grupo][$celda]));

            $celdasDocente[$fila->id_docente][$celda] = true;
            $celdasSalon[$fila->id_salon][$celda] = true;
            $celdasGrupo[$fila->id_grupo][$celda] = true;
            $carga[$fila->id_docente] = ($carga[$fila->id_docente] ?? 0) + 1;
        }

        foreach (DB::table('docentes')->where('is_active', true)->get() as $docente) {
            $this->assertLessThanOrEqual(
                (int) $docente->horas_semanales,
                (int) ($carga[$docente->id_docente] ?? 0),
                "Docente {$docente->id_docente} supera su tope semanal."
            );
        }

        // Recursos base reservados para pruebas de horarios y del portal.
        $this->assertSame(0, (int) ($carga[1] ?? 0));
        $this->assertSame(0, (int) ($carga[2] ?? 0));
        $this->assertSame(2, (int) ($carga[3] ?? 0));
        $this->assertSame(2, (int) ($carga[4] ?? 0));
    }

    public function test_hay_salones_y_docentes_genericos_suficientes(): void
    {
        $this->assertGreaterThanOrEqual(17, DB::table('salones')->where('is_active', true)->count());
        $this->assertSame(7, DB::table('docentes')->where('matricula_rfc', 'like', 'DOC-HOR-%')->count());
        $this->assertGreaterThanOrEqual(38, DB::table('materias')->where('is_active', true)->count());
    }
}
