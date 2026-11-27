<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\EstadoAlumno;
use App\Models\Evaluacion;
use App\Models\Grupo;
use App\Models\KardexMateria;
use App\Models\Materia;
use App\Models\Rol;
use App\Models\User;
use App\Services\KardexService;
use Database\Seeders\Demo\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class KardexVencimientoTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    private function crearAlumno(string $username, ?int $idGrupo, ?int $idSemestre): Alumno
    {
        $usuario = User::create([
            'id_rol' => Rol::ALUMNO,
            'username' => $username,
            'password_hash' => bcrypt('Prueba123!'),
            'is_active' => true,
        ]);

        return Alumno::create([
            'id_usuario' => $usuario->id_usuario,
            'id_plan' => 1,
            'id_semestre' => $idSemestre,
            'id_grupo' => $idGrupo,
            'id_estado_alumno' => EstadoAlumno::ACTIVO,
            'nombre' => 'Prueba',
            'apellido_paterno' => 'Kardex',
        ]);
    }

    private function crearClaseCalificada(int $idGrupo, int $idMateria, int $idAlumno): Clase
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
                'porcentaje_obtenido' => 80,
                'puntaje_obtenido' => 8,
            ]);
        }

        return $clase;
    }

    public function test_vencer_grupo_guarda_kardex_y_fuerza_el_siguiente_semestre(): void
    {
        $grupo = Grupo::create([
            'id_plan' => 1,
            'id_semestre' => 1,
            'nombre' => '1-VENC',
            'cupo_maximo' => 10,
            'fecha_conclusion' => now()->subDay()->toDateString(),
        ]);
        $alumno = $this->crearAlumno('alumno.vencimiento', $grupo->id_grupo, 1);
        $materia = Materia::where('id_plan', 1)->where('id_semestre', 1)->firstOrFail();
        $clase = $this->crearClaseCalificada($grupo->id_grupo, $materia->id_materia, $alumno->id_alumno);

        Artisan::call('grupos:vencer', ['--fecha' => now()->toDateString()]);

        $this->assertDatabaseHas('kardex_materias', [
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $materia->id_materia,
            'estatus' => 'cursada',
            'calificacion_final' => 8.00,
        ]);

        $alumno->refresh();
        $this->assertNull($alumno->id_grupo);
        $this->assertSame(2, (int) $alumno->id_semestre);
        $this->assertSame(EstadoAlumno::REINSCRIPCION, (int) $alumno->id_estado_alumno);
        $this->assertNotNull($grupo->fresh()->vencimiento_procesado_at);

        $this->actingAs($alumno->usuario)
            ->get(route('alumno.kardex.index'))
            ->assertOk()
            ->assertSee('Cursada')
            ->assertSee('Sin cursar')
            ->assertDontSee('site-footer');

        $this->get(route('alumno.materias.show', $clase))->assertNotFound();
    }

    public function test_no_se_puede_graduar_sin_todas_las_materias_o_sin_el_sexto_semestre(): void
    {
        $alumno = $this->crearAlumno('alumno.incompleto', null, 6);
        $materias = Materia::where('id_plan', 1)->get();

        foreach ($materias as $indice => $materia) {
            if ($indice === 0) {
                continue;
            }

            KardexMateria::create([
                'id_alumno' => $alumno->id_alumno,
                'id_materia' => $materia->id_materia,
                'id_plan' => 1,
                'semestre' => 6,
                'estatus' => 'cursada',
                'calificacion_final' => 8,
            ]);
        }

        $servicio = app(KardexService::class);
        $this->assertFalse($servicio->puedeGraduarse($alumno));
        $this->assertFalse($servicio->graduarSiCorresponde($alumno));

        KardexMateria::create([
            'id_alumno' => $alumno->id_alumno,
            'id_materia' => $materias->first()->id_materia,
            'id_plan' => 1,
            'semestre' => 1,
            'estatus' => 'cursada',
            'calificacion_final' => 9,
        ]);

        $this->assertTrue($servicio->puedeGraduarse($alumno));
        $this->assertTrue($servicio->graduarSiCorresponde($alumno));
        $this->assertSame(EstadoAlumno::GRADUADO, (int) $alumno->fresh()->id_estado_alumno);
    }

    public function test_alumno_graduado_solo_ve_su_promedio_final(): void
    {
        $alumno = $this->crearAlumno('alumno.titulado', null, null);
        $materias = Materia::where('id_plan', 1)->get();

        foreach ($materias as $materia) {
            KardexMateria::create([
                'id_alumno' => $alumno->id_alumno,
                'id_materia' => $materia->id_materia,
                'id_plan' => 1,
                'semestre' => (int) $materia->semestre?->numero_semestre ?: 6,
                'estatus' => 'cursada',
                'calificacion_final' => 8,
            ]);
        }

        app(KardexService::class)->graduarSiCorresponde($alumno);

        $this->actingAs($alumno->usuario->fresh())
            ->get(route('alumno.kardex.index'))
            ->assertOk()
            ->assertSee('Promedio final de todas las calificaciones')
            ->assertSee('8.00')
            ->assertDontSee('Sin cursar');
    }

    public function test_administrador_no_puede_marcar_graduado_sin_completar_el_plan(): void
    {
        $alumno = $this->crearAlumno('alumno.sinplan', null, 6);
        $admin = User::where('username', 'admin.escolar')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.alumnos.update', $alumno), [
                'username' => $alumno->usuario->username,
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'id_plan' => 1,
                'id_semestre' => 6,
                'id_estado_alumno' => EstadoAlumno::GRADUADO,
            ])
            ->assertSessionHasErrors('id_estado_alumno');

        $this->assertNotSame(EstadoAlumno::GRADUADO, (int) $alumno->fresh()->id_estado_alumno);
    }

    public function test_no_se_puede_crear_un_alumno_como_graduado(): void
    {
        $admin = User::where('username', 'admin.escolar')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.alumnos.store'), [
                'username' => 'alumno.nuevo.graduado',
                'password' => 'Prueba123!',
                'password_confirmation' => 'Prueba123!',
                'nombre' => 'Nuevo',
                'apellido_paterno' => 'Graduado',
                'id_plan' => 1,
                'id_estado_alumno' => EstadoAlumno::GRADUADO,
            ])
            ->assertSessionHasErrors('id_estado_alumno');

        $this->assertDatabaseMissing('usuarios', ['username' => 'alumno.nuevo.graduado']);
    }

    public function test_el_docente_no_puede_capturar_menos_del_cincuenta_por_ciento_por_criterio(): void
    {
        $usuario = User::where('username', 'docente.prueba')->firstOrFail();
        $docente = $usuario->docente;
        $clase = $docente->clases()->where('is_active', true)->firstOrFail();
        $evaluacion = $clase->evaluaciones()->orderBy('parcial')->firstOrFail();

        if ($evaluacion->criterios()->count() === 0) {
            CriterioEvaluacion::create([
                'id_clase' => $clase->id_clase,
                'parcial' => $evaluacion->parcial,
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'nombre_rubro' => 'Examen',
                'tipo_evidencia' => 'Examen',
                'porcentaje' => 60,
            ]);
        }

        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get();
        $porcentajeOriginal = (float) $criterios->first()->porcentaje;
        $alumnos = $clase->alumnosActivos();
        $alumno = $alumnos->firstOrFail();
        $calificaciones = [];

        foreach ($alumnos as $alumnoActual) {
            $calificaciones[$alumnoActual->id_alumno] = [];
            foreach ($criterios as $criterio) {
                $calificaciones[$alumnoActual->id_alumno][$criterio->id_criterio] = 80;
            }
        }

        $calificaciones[$alumno->id_alumno][$criterios->first()->id_criterio] = 49;

        $this->actingAs($usuario)
            ->post(route('docente.clases.calificaciones', [$clase, $evaluacion]), [
                'calificaciones' => $calificaciones,
            ])
            ->assertSessionHasErrors('calificaciones.'.$alumno->id_alumno.'.'.$criterios->first()->id_criterio);

        $calificaciones[$alumno->id_alumno][$criterios->first()->id_criterio] = 50;

        $this->post(route('docente.clases.calificaciones', [$clase, $evaluacion]), [
            'calificaciones' => $calificaciones,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('calificaciones', [
            'id_criterio' => $criterios->first()->id_criterio,
            'id_alumno' => $alumno->id_alumno,
            'porcentaje_obtenido' => 50.00,
        ]);
        $this->assertSame($porcentajeOriginal, (float) $criterios->first()->fresh()->porcentaje);
    }
}
