<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AlumnoTutor;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\EstadoAlumno;
use App\Models\Evaluacion;
use App\Models\Grupo;
use App\Models\KardexMateria;
use App\Models\Materia;
use App\Models\Recursamiento;
use App\Models\Rol;
use App\Models\Tutor;
use App\Models\User;
use App\Services\TutorVinculacionService;
use Database\Seeders\Demo\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RecursamientosTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    private function crearAlumno(string $username, ?int $idTutor = null): Alumno
    {
        $usuario = User::create([
            'id_rol' => Rol::ALUMNO,
            'username' => $username,
            'password_hash' => bcrypt('Prueba123!'),
            'is_active' => true,
        ]);

        return Alumno::create([
            'id_usuario' => $usuario->id_usuario,
            'id_tutor' => $idTutor,
            'id_plan' => 1,
            'id_estado_alumno' => EstadoAlumno::ACTIVO,
            'nombre' => 'Recursamiento',
            'apellido_paterno' => 'Prueba',
        ]);
    }

    private function crearClaseCalificada(int $idGrupo, int $idMateria, int $idAlumno, int $porcentaje): Clase
    {
        $docente = User::where('username', 'docente.prueba')->firstOrFail()->docente;
        $clase = Clase::create([
            'id_materia' => $idMateria,
            'id_docente' => $docente->id_docente,
            'id_grupo' => $idGrupo,
            'is_active' => true,
        ]);

        foreach ([1, 2, 3] as $parcial) {
            $evaluacion = Evaluacion::create([
                'id_clase' => $clase->id_clase,
                'parcial' => $parcial,
                'tipo' => 'examen',
                'estatus' => 'publicada',
                'formato_calificacion' => 'decimal',
            ]);
            $criterio = CriterioEvaluacion::create([
                'id_clase' => $clase->id_clase,
                'parcial' => $parcial,
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'nombre_rubro' => 'Examen',
                'tipo_evidencia' => 'Examen',
                'porcentaje' => 100,
            ]);
            Calificacion::create([
                'id_criterio' => $criterio->id_criterio,
                'id_alumno' => $idAlumno,
                'porcentaje_obtenido' => $porcentaje,
                'puntaje_obtenido' => $porcentaje / 10,
            ]);
        }

        return $clase;
    }

    private function abrirRonda(Alumno $alumno, int $idMateria, int $ronda, ?int $idDocente = null, ?float $calificacion = null): Recursamiento
    {
        $kardex = KardexMateria::create([
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $idMateria,
            'id_plan' => 1,
            'semestre' => 1,
            'estatus' => 'r'.$ronda,
            'calificacion_final' => $calificacion,
        ]);

        return Recursamiento::create([
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $idMateria,
            'id_plan' => 1,
            'semestre' => 1,
            'id_grupo_original' => null,
            'id_docente' => $idDocente,
            'ronda' => $ronda,
            'estatus' => Recursamiento::PENDIENTE,
            'calificacion_final' => $calificacion,
        ]);
    }

    public function test_vencer_grupo_con_promedio_menor_a_seis_abre_r1(): void
    {
        $grupo = Grupo::create([
            'id_plan' => 1,
            'id_semestre' => 1,
            'nombre' => '1-R1',
            'cupo_maximo' => 10,
            'fecha_conclusion' => now()->subDay()->toDateString(),
        ]);
        $alumno = $this->crearAlumno('alumno.r1.auto');
        $alumno->update(['id_grupo' => $grupo->id_grupo, 'id_semestre' => 1]);
        $materia = Materia::where('id_plan', 1)->where('id_semestre', 1)->firstOrFail();
        $clase = $this->crearClaseCalificada($grupo->id_grupo, $materia->id_materia, $alumno->id_alumno, 50);

        Artisan::call('grupos:vencer', ['--fecha' => now()->toDateString()]);

        $this->assertDatabaseHas('kardex_materias', [
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $materia->id_materia,
            'estatus' => 'r1',
            'calificacion_final' => 5.00,
        ]);
        $this->assertDatabaseHas('recursamientos', [
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $materia->id_materia,
            'ronda' => 1,
            'estatus' => Recursamiento::PENDIENTE,
            'id_docente' => $clase->id_docente,
        ]);

        $alumno->refresh();
        $this->assertNull($alumno->id_grupo);
        $this->assertSame(EstadoAlumno::REINSCRIPCION, (int) $alumno->id_estado_alumno);
    }

    public function test_r1_se_aprueba_con_seis_y_el_kardex_queda_aprobado(): void
    {
        $alumno = $this->crearAlumno('alumno.r1.aprobado');
        $materia = Materia::where('id_plan', 1)->firstOrFail();
        $recursamiento = $this->abrirRonda($alumno, $materia->id_materia, 1, null, 5.0);

        $recursamiento = app(\App\Services\RecursamientoService::class)->calificar($recursamiento, 60);

        $this->assertSame(Recursamiento::APROBADA, $recursamiento->estatus);
        $this->assertSame(6.0, $recursamiento->calificacion_final);
        $this->assertDatabaseHas('kardex_materias', [
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $materia->id_materia,
            'estatus' => KardexMateria::APROBADA_R,
            'calificacion_final' => 6.00,
        ]);
        $this->assertSame(0, Recursamiento::where('id_alumno', $alumno->id_alumno)->where('ronda', 2)->count());
    }

    public function test_r1_reprobado_pasa_a_r2_y_r2_tiene_tope_de_ochenta(): void
    {
        $alumno = $this->crearAlumno('alumno.r1.reprobado');
        $materia = Materia::where('id_plan', 1)->firstOrFail();
        $servicio = app(\App\Services\RecursamientoService::class);
        $r1 = $this->abrirRonda($alumno, $materia->id_materia, 1, null, 5.0);

        $r1 = $servicio->calificar($r1, 50);

        $this->assertSame(Recursamiento::REPROBADA, $r1->estatus);
        $this->assertDatabaseHas('kardex_materias', [
            'id_alumno' => $alumno->id_alumno,
            'estatus' => 'r2',
        ]);
        $r2 = Recursamiento::where('id_alumno', $alumno->id_alumno)->where('ronda', 2)->firstOrFail();

        try {
            $servicio->calificar($r2, 90);
            $this->fail('R2 no debe permitir más de 80 %.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('80', $exception->getMessage());
        }

        $r2 = $servicio->calificar($r2, 80);
        $this->assertSame(Recursamiento::APROBADA, $r2->estatus);
        $this->assertSame(8.0, $r2->calificacion_final);
    }

    public function test_reprobar_r4_da_de_baja_temporal_al_alumno(): void
    {
        $alumno = $this->crearAlumno('alumno.r4.reprobado');
        $materia = Materia::where('id_plan', 1)->firstOrFail();
        $r4 = $this->abrirRonda($alumno, $materia->id_materia, 4, null, 5.0);

        $r4 = app(\App\Services\RecursamientoService::class)->calificar($r4, 50);

        $this->assertSame(Recursamiento::BAJA_TEMPORAL, $r4->estatus);
        $this->assertDatabaseHas('kardex_materias', [
            'id_alumno' => $alumno->id_alumno,
            'estatus' => KardexMateria::BAJA_TEMPORAL,
        ]);
        $alumno->refresh();
        $this->assertSame(EstadoAlumno::BAJA_TEMPORAL, (int) $alumno->id_estado_alumno);
        $this->assertFalse((bool) $alumno->usuario->is_active);
    }

    public function test_docente_ve_sus_r_y_no_puede_calificar_otro(): void
    {
        $docente = User::where('username', 'docente.prueba')->firstOrFail();
        $recursamiento = Recursamiento::where('id_docente', $docente->docente->id_docente)
            ->where('estatus', Recursamiento::PENDIENTE)
            ->firstOrFail();

        $this->actingAs($docente)
            ->get(route('docente.recursamientos.index'))
            ->assertOk()
            ->assertSee('Recursamientos asignados')
            ->assertSee($recursamiento->alumno->nombreCompleto())
            ->assertSee($recursamiento->materia->nombre)
            ->assertDontSee('site-footer');

        $this->post(route('docente.recursamientos.calificar', $recursamiento), [
            'porcentaje' => $recursamiento->maximoPorcentaje() + 1,
        ])->assertSessionHasErrors('porcentaje');

        $otroDocente = User::where('username', 'docente.historia')->firstOrFail();
        $this->actingAs($otroDocente)
            ->get(route('docente.recursamientos.show', $recursamiento))
            ->assertNotFound();
    }

    public function test_tutor_ve_kardex_y_recursamientos_de_sus_alumnos(): void
    {
        $tutor = User::where('username', 'tutor.prueba')->firstOrFail();
        $alumno = Alumno::whereHas('usuario', fn ($q) => $q->where('username', 'alumno.r1'))->firstOrFail();

        $this->actingAs($tutor)
            ->get(route('tutor.alumnos.kardex', $alumno))
            ->assertOk()
            ->assertSee('Historial de recursamientos (R)')
            ->assertSee('R1')
            ->assertSee($alumno->nombreCompleto())
            ->assertDontSee('site-footer');

        $otroTutor = User::where('username', 'tutor.ramirez')->firstOrFail();
        $this->actingAs($otroTutor)
            ->get(route('tutor.alumnos.kardex', $alumno))
            ->assertNotFound();
    }

    public function test_un_alumno_no_puede_tener_mas_de_tres_tutores(): void
    {
        $alumno = $this->crearAlumno('alumno.tres.tutores');
        $servicio = app(TutorVinculacionService::class);
        $tutores = Tutor::orderBy('id_tutor')->limit(3)->get();

        foreach ($tutores as $tutor) {
            $servicio->vincular($alumno, $tutor);
        }

        $this->assertSame(3, $alumno->tutores()->count());

        try {
            $servicio->vincular($alumno, Tutor::orderByDesc('id_tutor')->firstOrFail());
            $this->fail('No se debe permitir un cuarto tutor.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('3 tutores', $exception->getMessage());
        }

        $this->assertSame(3, $alumno->tutores()->count());
        $this->assertSame(AlumnoTutor::MAXIMO_TUTORES_POR_ALUMNO, 3);
    }

    public function test_administrador_no_puede_reinscribir_alumno_con_r_pendiente(): void
    {
        $admin = User::where('username', 'admin.escolar')->firstOrFail();
        $alumno = Alumno::whereHas('usuario', fn ($q) => $q->where('username', 'alumno.r1'))->firstOrFail();
        $grupo = Grupo::where('id_semestre', 2)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.alumnos.update', $alumno), [
                'username' => $alumno->usuario->username,
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'id_plan' => 1,
                'id_semestre' => 2,
                'id_grupo' => $grupo->id_grupo,
                'id_estado_alumno' => EstadoAlumno::REINSCRIPCION,
            ])
            ->assertSessionHasErrors(['id_grupo', 'id_semestre']);

        $this->assertNull($alumno->fresh()->id_grupo);
    }
}
