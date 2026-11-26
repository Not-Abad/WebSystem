<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\AceptacionCriterio;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\CriterioEvaluacion;
use App\Models\Evaluacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAlumnoTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    public function test_mis_datos_muestra_el_expediente_sin_opcion_de_foto(): void
    {

        $usuario = User::where('username', 'alumno.prueba')->firstOrFail();
        $response = $this->actingAs($usuario)->get(route('alumno.perfil.index'));

        $response->assertOk()
            ->assertSee('Mis datos')
            ->assertSee($usuario->alumno->nombre)
            ->assertSee($usuario->alumno->curp)
            ->assertSee('Grupo')
            ->assertDontSee('Subir foto')
            ->assertDontSee('site-footer');
    }

    public function test_horario_muestra_las_clases_del_grupo_y_enlaza_al_detalle(): void
    {
        $usuario = User::where('username', 'alumno.prueba')->firstOrFail();
        $clase = Clase::where('id_grupo', $usuario->alumno->id_grupo)
            ->where('is_active', true)
            ->has('horarios')
            ->firstOrFail();

        $this->actingAs($usuario)
            ->get(route('alumno.materias.index'))
            ->assertOk()
            ->assertSee('Mi horario')
            ->assertSee(route('alumno.materias.show', $clase))
            ->assertSee('15:00 a 15:50')
            ->assertSee('Receso 20 minutos')
            ->assertDontSee('site-footer');
    }

    public function test_aceptar_criterios_solo_una_vez_y_calcular_calificacion_final(): void
    {
        $usuario = User::where('username', 'alumno.prueba')->firstOrFail();
        $alumno = $usuario->alumno;
        $clase = Clase::where('id_grupo', $alumno->id_grupo)
            ->where('is_active', true)
            ->firstOrFail();

        $evaluaciones = collect();
        foreach ([1, 2, 3] as $parcial) {
            $evaluacion = Evaluacion::create([
                'id_clase' => $clase->id_clase,
                'parcial' => $parcial,
                'estatus' => 'publicada',
                'formato_calificacion' => 'decimal',
            ]);
            foreach ([40, 60] as $porcentaje) {
                CriterioEvaluacion::create([
                    'id_clase' => $clase->id_clase,
                    'parcial' => $parcial,
                    'id_evaluacion' => $evaluacion->id_evaluacion,
                    'nombre_rubro' => 'Examen',
                    'tipo_evidencia' => 'Examen',
                    'porcentaje' => $porcentaje,
                ]);
            }
            $evaluaciones->push($evaluacion);
        }

        foreach ($evaluaciones as $evaluacion) {
            foreach ($evaluacion->criterios as $criterio) {
                Calificacion::create([
                    'id_criterio' => $criterio->id_criterio,
                    'id_alumno' => $alumno->id_alumno,
                    'porcentaje_obtenido' => 80,
                    'puntaje_obtenido' => 8 * ($criterio->porcentaje / 100),
                ]);
            }
        }

        $this->actingAs($usuario)
            ->get(route('alumno.materias.show', $clase))
            ->assertOk()
            ->assertSee('Aceptar criterios');

        $this->post(route('alumno.materias.aceptarCriterios', [$clase, $evaluaciones[0]]), [
            'acepto' => 1,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('aceptaciones_criterios', [
            'id_alumno' => $alumno->id_alumno,
            'id_evaluacion' => $evaluaciones[0]->id_evaluacion,
        ]);

        $response = $this->get(route('alumno.materias.show', $clase))
            ->assertOk()
            ->assertSee('Criterios aceptados')
            ->assertSee('Pendiente')
            ->assertSee('Calificación final de la materia');

        $this->assertStringNotContainsString(
            route('alumno.materias.aceptarCriterios', [$clase, $evaluaciones[0]]),
            $response->getContent()
        );

        foreach ($evaluaciones->skip(1) as $evaluacion) {
            $this->post(route('alumno.materias.aceptarCriterios', [$clase, $evaluacion]), [
                'acepto' => 1,
            ])->assertRedirect();
        }

        $response = $this->get(route('alumno.materias.show', $clase))
            ->assertOk()
            ->assertDontSee('Aceptar criterios')
            ->assertSee('Calificación total')
            ->assertSee('8.00')
            ->assertDontSee('Aceptar criterios');

        $this->assertSame(3, AceptacionCriterio::where('id_alumno', $alumno->id_alumno)->count());
    }

    public function test_programa_pdf_se_genera_para_la_materia_del_alumno(): void
    {
        $usuario = User::where('username', 'alumno.prueba')->firstOrFail();
        $clase = Clase::where('id_grupo', $usuario->alumno->id_grupo)
            ->where('is_active', true)
            ->firstOrFail();

        $response = $this->actingAs($usuario)
            ->get(route('alumno.materias.programaPdf', $clase));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertSee('%PDF');
    }

    public function test_alumno_no_puede_consultar_materia_de_otro_grupo(): void
    {
        $usuario = User::where('username', 'alumno.prueba')->firstOrFail();
        $clase = Clase::where('id_grupo', '!=', $usuario->alumno->id_grupo)->firstOrFail();

        $this->actingAs($usuario)
            ->get(route('alumno.materias.show', $clase))
            ->assertNotFound();
    }
}
