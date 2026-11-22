<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosTutorTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_panel_de_tutor_enlaza_el_seguimiento_de_sus_alumnos(): void
    {
        $tutor = User::where('username', 'tutor.prueba')->firstOrFail();

        $this->actingAs($tutor)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('tutor.alumnos.index'))
            ->assertSee('Seguimiento académico');
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
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'alumnos',
            'accion' => 'VINCULACION',
            'id_registro_afectado' => $alumno->id_alumno,
        ]);

        $this->delete(route('admin.tutores.alumnos.desasignar', [$tutorDestino, $alumno]))
            ->assertRedirect();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_tutor' => null,
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
