<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\Evaluacion;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosTutorTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    public function test_panel_de_tutor_enlaza_el_seguimiento_de_sus_alumnos(): void
    {
        $tutor = User::where('username', 'tutor.prueba')->firstOrFail();

        $this->actingAs($tutor)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('tutor.alumnos.index'))
            ->assertSee('Seguimiento académico');
    }

    public function test_hay_varios_tutores_de_prueba_con_alumnos_asignados(): void
    {
        $tutores = Tutor::withCount('alumnos')->get();

        $this->assertGreaterThanOrEqual(10, $tutores->count());
        $this->assertGreaterThanOrEqual(1, (int) $tutores->min('alumnos_count'));
        $this->assertLessThanOrEqual(10, (int) $tutores->max('alumnos_count'));

        $tutorDemo = User::where('username', 'tutor.demo001')->firstOrFail();
        $this->assertSame(2, (int) $tutorDemo->tutor->alumnos()->count());
    }

    public function test_tutor_solo_ve_a_los_alumnos_bajo_su_tutela(): void
    {
        $tutor = User::where('username', 'tutor.prueba')->firstOrFail();

        $this->actingAs($tutor)
            ->get(route('tutor.alumnos.index', ['q' => 'Manuel Eduardo']))
            ->assertOk()
            ->assertSee('#1')
            ->assertSee('Manuel Eduardo')
            ->assertDontSee('#2</strong>')
            ->assertDontSee('Ana Sofía')
            ->assertDontSee('site-footer');
    }

    public function test_tutor_no_puede_consultar_expedientes_de_otros_tutores(): void
    {
        $tutor = User::where('username', 'tutor.prueba')->firstOrFail();
        $alumnoPropio = Alumno::findOrFail(1);
        $alumnoAjeno = Alumno::findOrFail(2);

        $this->actingAs($tutor)
            ->get(route('tutor.alumnos.show', $alumnoPropio))
            ->assertOk()
            ->assertSee('Manuel Eduardo')
            ->assertSee('4-A');

        $this->get(route('tutor.alumnos.show', $alumnoAjeno))
            ->assertNotFound();

        $this->get(route('admin.alumnos.index'))
            ->assertForbidden();
    }

    public function test_tutor_ve_sus_datos_personales_sin_opcion_de_foto(): void
    {
        $usuario = User::where('username', 'tutor.prueba')->firstOrFail();
        $tutor = $usuario->tutor;

        $this->actingAs($usuario)
            ->get(route('tutor.perfil.index'))
            ->assertOk()
            ->assertSee('Mis datos')
            ->assertSee($tutor->nombre)
            ->assertSee($tutor->apellido_paterno)
            ->assertSee($tutor->correo)
            ->assertSee($tutor->telefono)
            ->assertSee($usuario->username)
            ->assertDontSee('Subir foto')
            ->assertDontSee('site-footer');
    }

    public function test_tutor_ve_materias_criterios_y_calificaciones_del_alumno(): void
    {
        $usuario = User::where('username', 'tutor.prueba')->firstOrFail();
        $alumno = Alumno::findOrFail(1);
        $clase = Clase::where('id_grupo', $alumno->id_grupo)
            ->where('is_active', true)
            ->firstOrFail();

        $evaluacion = Evaluacion::firstOrCreate(
            ['id_clase' => $clase->id_clase, 'parcial' => 1],
            ['tipo' => 'ambos', 'estatus' => 'publicada', 'formato_calificacion' => 'decimal']
        );
        Calificacion::whereIn('id_criterio', $evaluacion->criterios()->pluck('id_criterio'))->delete();
        $evaluacion->criterios()->delete();

        $criterio = CriterioEvaluacion::create([
            'id_clase' => $clase->id_clase,
            'parcial' => 1,
            'id_evaluacion' => $evaluacion->id_evaluacion,
            'nombre_rubro' => 'Examen',
            'tipo_evidencia' => 'Examen',
            'porcentaje' => 70,
        ]);
        Calificacion::create([
            'id_criterio' => $criterio->id_criterio,
            'id_alumno' => $alumno->id_alumno,
            'porcentaje_obtenido' => 80,
            'puntaje_obtenido' => 5.60,
        ]);

        $this->actingAs($usuario)
            ->get(route('tutor.alumnos.show', $alumno))
            ->assertOk()
            ->assertSee('Materias del alumno')
            ->assertSee(route('tutor.alumnos.materia', [$alumno, $clase]));

        $this->get(route('tutor.alumnos.materia', [$alumno, $clase]))
            ->assertOk()
            ->assertSee('Correo institucional')
            ->assertSee($clase->docente?->correo_institucional ?? '—')
            ->assertSee('Examen')
            ->assertSee('Criterio de evaluación')
            ->assertDontSee('<th>Evidencia</th>', false)
            ->assertDontSee('<th>Rubro</th>', false)
            ->assertSee('70.00 %')
            ->assertSee('8.00')
            ->assertSee('Calificación obtenida en el parcial')
            ->assertDontSee('80.00 %')
            ->assertDontSee('Calificación (0–10)')
            ->assertDontSee('Aportación al parcial')
            ->assertDontSee('5.60')
            ->assertDontSee('site-footer');
    }

    public function test_tutor_no_puede_ver_materias_de_otro_alumno_ni_de_otro_grupo(): void
    {
        $usuario = User::where('username', 'tutor.prueba')->firstOrFail();
        $alumnoPropio = Alumno::findOrFail(1);
        $alumnoAjeno = Alumno::findOrFail(2);
        $clasePropia = Clase::where('id_grupo', $alumnoPropio->id_grupo)->firstOrFail();
        $claseAjena = Clase::where('id_grupo', '!=', $alumnoPropio->id_grupo)->firstOrFail();

        $this->actingAs($usuario)
            ->get(route('tutor.alumnos.materia', [$alumnoAjeno, $clasePropia]))
            ->assertNotFound();

        $this->get(route('tutor.alumnos.materia', [$alumnoPropio, $claseAjena]))
            ->assertNotFound();
    }

    public function test_administrador_puede_vincular_y_desasignar_alumnos_desde_tutores(): void
    {
        $admin = User::where('username', 'admin.escolar')->firstOrFail();
        $tutorDestino = Tutor::findOrFail(2);
        $alumno = Alumno::findOrFail(1);

        $this->actingAs($admin)
            ->get(route('admin.tutores.alumnos', $tutorDestino))
            ->assertOk()
            ->assertSee('Ana Sofía')
            ->assertSee('Vincular alumno');

        $this->post(route('admin.tutores.alumnos.vincular', $tutorDestino), [
            'id_alumno' => $alumno->id_alumno,
        ])->assertRedirect();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => $tutorDestino->id_tutor,
        ]);
        $this->assertDatabaseHas('alumno_tutores', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => $tutorDestino->id_tutor,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'alumnos',
            'accion' => 'VINCULACION',
            'id_registro_afectado' => $alumno->id_alumno,
        ]);

        $this->delete(route('admin.tutores.alumnos.desasignar', [$tutorDestino, $alumno]))
            ->assertRedirect();

        $this->assertDatabaseMissing('alumno_tutores', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => $tutorDestino->id_tutor,
        ]);
        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => 1,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'alumnos',
            'accion' => 'DESASIGNACION',
            'id_registro_afectado' => $alumno->id_alumno,
        ]);
    }

    public function test_busqueda_rapida_de_alumnos_para_vincular(): void
    {
        $admin = User::where('username', 'admin.escolar')->firstOrFail();
        $tutor = Tutor::findOrFail(1);

        $this->actingAs($admin)
            ->get(route('admin.tutores.alumnos', $tutor).'?q=Ana')
            ->assertOk()
            ->assertSee('Ana Sofía')
            ->assertSee('1 alumno(s) disponible(s) para vincular')
            ->assertSee('Buscar alumno')
            ->assertSee('Resultados');

        $alumno = Alumno::findOrFail(2);

        $this->post(route('admin.tutores.alumnos.vincular', $tutor), [
            'id_alumno' => $alumno->id_alumno,
        ])->assertRedirect();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => $tutor->id_tutor,
        ]);
    }

    public function test_no_se_puede_desasignar_un_alumno_de_otro_tutor(): void
    {
        $admin = User::where('username', 'admin.escolar')->firstOrFail();
        $tutor = Tutor::findOrFail(2);
        $alumno = Alumno::findOrFail(1);

        $this->actingAs($admin)
            ->delete(route('admin.tutores.alumnos.desasignar', [$tutor, $alumno]))
            ->assertNotFound();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => $alumno->id_tutor,
        ]);
    }
}
