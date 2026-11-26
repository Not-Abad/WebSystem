<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Http\Controllers\Docente\ClaseController;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoContenidoTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    public function test_cada_semestre_tiene_un_grupo_lleno_de_alumnos(): void
    {
        foreach ([7 => 1, 2 => 2, 3 => 3, 4 => 4, 11 => 5, 12 => 6] as $idGrupo => $semestre) {
            $grupo = Grupo::findOrFail($idGrupo);

            $this->assertSame($semestre, (int) $grupo->id_semestre);
            $this->assertSame($grupo->cupo_maximo, $grupo->alumnos()->count());
        }
    }

    public function test_hay_docentes_genericos_con_ficha_institucional_completa(): void
    {
        $docentes = DB::table('docentes')->where('matricula_rfc', 'like', 'DOC-DEMO-%')->get();

        $this->assertCount(12, $docentes);
        $this->assertSame(6, DB::table('materias')->whereBetween('id_materia', [33, 38])->count());
        foreach ($docentes as $docente) {
            $this->assertNotEmpty($docente->nombre);
            $this->assertNotEmpty($docente->licenciatura);
            $this->assertNotEmpty($docente->numero_personal_sep);
            $this->assertNotEmpty($docente->cedula_profesional);
            $this->assertSame(mb_strtoupper($docente->cedula_profesional), $docente->cedula_profesional);
        }

        $this->actingAs(User::where('username', 'docente.demo5')->firstOrFail())
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('Mi horario');
    }

    public function test_banco_de_criterios_ofrece_opciones_multiples_y_evaluaciones_llenas(): void
    {
        $docente = User::where('username', 'docente.demo5')->firstOrFail();

        $this->actingAs($docente)
            ->get(route('docente.clases.show', 3))
            ->assertOk()
            ->assertSee('Banco de criterios de evaluación')
            ->assertSee('Actividades en clase')
            ->assertSee('Asistencia');

        foreach (['Examen', 'Tareas', 'Asistencia', 'Proyecto', 'Actividades en clase'] as $criterio) {
            $this->assertContains($criterio, ClaseController::BANCO_EVIDENCIAS);
        }

        $this->assertGreaterThanOrEqual(6, DB::table('evaluaciones')->count());
        $this->assertGreaterThanOrEqual(72, DB::table('criterios_evaluacion')->count());
        $this->assertGreaterThanOrEqual(200, DB::table('asistencias')->count());
    }

    public function test_los_seeders_pueden_ejecutarse_dos_veces_sin_duplicar_datos(): void
    {
        $tablas = [
            'usuarios', 'docentes', 'alumnos', 'tutores', 'clases', 'horarios',
            'evaluaciones', 'criterios_evaluacion', 'calificaciones', 'asistencias',
        ];

        $antes = [];
        foreach ($tablas as $tabla) {
            $antes[$tabla] = DB::table($tabla)->count();
        }

        $this->seed(DemoDataSeeder::class);

        foreach ($antes as $tabla => $total) {
            $this->assertSame($total, DB::table($tabla)->count(), 'El seeder duplicó '.$tabla);
        }
    }
}
