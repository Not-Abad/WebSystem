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

    public function test_todos_los_docentes_tienen_evaluaciones_criterios_y_calificaciones(): void
    {
        $docentes = DB::table('docentes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->where('usuarios.id_rol', 3)
            ->pluck('docentes.id_docente');

        $this->assertGreaterThanOrEqual(23, $docentes->count());

        foreach ($docentes as $idDocente) {
            $evaluaciones = DB::table('evaluaciones')
                ->join('clases', 'clases.id_clase', '=', 'evaluaciones.id_clase')
                ->where('clases.id_docente', $idDocente)
                ->count();

            $criterios = DB::table('criterios_evaluacion')
                ->join('clases', 'clases.id_clase', '=', 'criterios_evaluacion.id_clase')
                ->where('clases.id_docente', $idDocente)
                ->count();

            $calificaciones = DB::table('calificaciones')
                ->join('criterios_evaluacion', 'criterios_evaluacion.id_criterio', '=', 'calificaciones.id_criterio')
                ->join('clases', 'clases.id_clase', '=', 'criterios_evaluacion.id_clase')
                ->where('clases.id_docente', $idDocente)
                ->count();

            $this->assertGreaterThanOrEqual(3, $evaluaciones, 'Docente '.$idDocente.' sin evaluaciones');
            $this->assertGreaterThanOrEqual(12, $criterios, 'Docente '.$idDocente.' sin criterios');
            $this->assertGreaterThanOrEqual(100, $calificaciones, 'Docente '.$idDocente.' con calificaciones incompletas');
        }
    }

    public function test_no_hay_grupos_vacios_ni_grupos_sin_calificaciones(): void
    {
        foreach (DB::table('grupos')->pluck('id_grupo') as $idGrupo) {
            $alumnos = DB::table('alumnos')->where('id_grupo', $idGrupo)->count();
            $clases = DB::table('clases')
                ->where('id_grupo', $idGrupo)
                ->where('is_active', 1)
                ->count();
            $calificaciones = DB::table('calificaciones')
                ->join('criterios_evaluacion', 'criterios_evaluacion.id_criterio', '=', 'calificaciones.id_criterio')
                ->join('clases', 'clases.id_clase', '=', 'criterios_evaluacion.id_clase')
                ->where('clases.id_grupo', $idGrupo)
                ->count();

            $this->assertGreaterThanOrEqual(15, $alumnos, 'Grupo '.$idGrupo.' vacío');
            $this->assertGreaterThanOrEqual(1, $clases, 'Grupo '.$idGrupo.' sin clases');
            $this->assertGreaterThan(0, $calificaciones, 'Grupo '.$idGrupo.' sin calificaciones');
        }
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
