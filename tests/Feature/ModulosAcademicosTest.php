<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\Alumno;
use App\Models\Docente;
use App\Models\EstadoAlumno;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\PlanEstudio;
use App\Models\Rol;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosAcademicosTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    private function administrador(): User
    {
        return User::where('username', 'admin.escolar')->firstOrFail();
    }

    private function crearAlumnoEnGrupo(int $idGrupo, string $sufijo): Alumno
    {
        $usuario = User::create([
            'id_rol' => Rol::ALUMNO,
            'username' => 'cupo.'.$sufijo,
            'password_hash' => 'Prueba123!',
            'is_active' => true,
        ]);

        return Alumno::create([
            'id_usuario' => $usuario->id_usuario,
            'id_grupo' => $idGrupo,
            'id_estado_alumno' => EstadoAlumno::ACTIVO,
            'id_plan' => 1,
            'id_semestre' => 3,
            'nombre' => 'Cupo',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => $sufijo,
            'curp' => strtoupper(str_pad($sufijo, 18, 'X', STR_PAD_RIGHT)),
            'acta_nacimiento_ruta' => 'alumnos/acta_'.$sufijo.'.pdf',
            'boleta_secundaria_ruta' => 'alumnos/boleta_'.$sufijo.'.pdf',
            'correo' => 'cupo.'.$sufijo.'@eba-amr.edu.mx',
        ]);
    }

    public function test_administrador_puede_dar_de_alta_un_plan_y_sus_materias_por_semestre(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.catalogos.store'), [
            'clave' => 'DGB-2026',
            'nombre' => 'Plan de Estudios Actualizado',
            'anio_inicio' => 2026,
        ])->assertRedirect(route('admin.catalogos.index'));

        $plan = PlanEstudio::where('clave', 'DGB-2026')->firstOrFail();

        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'planes_estudio',
            'accion' => 'ALTA',
            'id_registro_afectado' => $plan->id_plan,
        ]);

        $this->post(route('admin.catalogos.materias.guardar', $plan), [
            'id_semestre' => 3,
            'clave' => 'MAT-NUEVA',
            'nombre' => 'Matemáticas actualizadas',
            'tipo_materia' => 'Basica',
            'horas_semana' => 5,
            'descripcion' => 'Materia de prueba.',
            'objetivo' => 'Validar el alta por semestre.',
            'programa_pdf_ruta' => null,
        ])->assertRedirect(route('admin.catalogos.materias.plan', $plan));

        $materia = Materia::where('clave', 'MAT-NUEVA')->firstOrFail();

        $this->assertDatabaseHas('materias', [
            'id_materia' => $materia->id_materia,
            'id_plan' => $plan->id_plan,
            'id_semestre' => 3,
            'tipo_materia' => 'Basica',
            'es_tronco_comun' => 0,
            'id_capacitacion' => null,
            'id_area_propedeutica' => null,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'materias',
            'accion' => 'ALTA',
            'id_registro_afectado' => $materia->id_materia,
        ]);

        $this->get(route('admin.catalogos.materias.plan', [
            'plan' => $plan,
            'id_semestre' => 3,
        ]))->assertOk()
            ->assertSee('Matemáticas actualizadas')
            ->assertDontSee('site-footer');
    }

    public function test_materia_de_tipo_capacitacion_requiere_capacitacion(): void
    {
        $this->actingAs($this->administrador());

        $plan = PlanEstudio::firstOrFail();

        $this->post(route('admin.catalogos.materias.guardar', $plan), [
            'id_semestre' => 5,
            'clave' => 'CAP-SIN-CAT',
            'nombre' => 'Capacitación sin catalogo',
            'tipo_materia' => 'Capacitacion',
            'es_tronco_comun' => 1,
            'horas_semana' => 4,
        ])->assertSessionHasErrors('id_capacitacion');

        $this->post(route('admin.catalogos.materias.guardar', $plan), [
            'id_semestre' => 5,
            'clave' => 'CAP-SIN-CAT',
            'nombre' => 'Capacitación sin catalogo',
            'tipo_materia' => 'Capacitacion',
            'es_tronco_comun' => 1,
            'horas_semana' => 4,
            'id_capacitacion' => 1,
        ])->assertRedirect(route('admin.catalogos.materias.plan', $plan));

        $this->assertDatabaseHas('materias', [
            'clave' => 'CAP-SIN-CAT',
            'id_plan' => $plan->id_plan,
            'id_capacitacion' => 1,
            'id_area_propedeutica' => null,
            'es_tronco_comun' => 1,
        ]);
    }

    public function test_materia_de_quinto_semestre_requiere_area_o_ser_tronco_comun(): void
    {
        $this->actingAs($this->administrador());

        $plan = PlanEstudio::firstOrFail();

        $this->post(route('admin.catalogos.materias.guardar', $plan), [
            'id_semestre' => 5,
            'clave' => 'HUM-NUEVA',
            'nombre' => 'Humanidades nueva',
            'tipo_materia' => 'Propedeutica',
            'horas_semana' => 6,
        ])->assertSessionHasErrors('id_area_propedeutica');

        $this->post(route('admin.catalogos.materias.guardar', $plan), [
            'id_semestre' => 5,
            'clave' => 'HUM-NUEVA',
            'nombre' => 'Humanidades nueva',
            'tipo_materia' => 'Propedeutica',
            'horas_semana' => 6,
            'id_area_propedeutica' => 2,
        ])->assertRedirect(route('admin.catalogos.materias.plan', $plan));

        $this->assertDatabaseHas('materias', [
            'clave' => 'HUM-NUEVA',
            'id_area_propedeutica' => 2,
            'es_tronco_comun' => 0,
        ]);
    }

    public function test_grupos_se_crean_por_plan_semestre_y_area_sin_periodo(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.grupos.store'), [
            'id_plan' => 1,
            'id_semestre' => 3,
            'nombre' => '3-C',
            'cupo_maximo' => 30,
        ])->assertRedirect(route('admin.grupos.index'));

        $grupo = Grupo::where('nombre', '3-C')->firstOrFail();

        $this->assertDatabaseHas('grupos', [
            'id_grupo' => $grupo->id_grupo,
            'id_plan' => 1,
            'id_semestre' => 3,
            'id_area_propedeutica' => null,
            'cupo_maximo' => 30,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'grupos',
            'accion' => 'ALTA',
            'id_registro_afectado' => $grupo->id_grupo,
        ]);

        $this->post(route('admin.grupos.store'), [
            'id_plan' => 1,
            'id_semestre' => 3,
            'nombre' => '3-C',
            'cupo_maximo' => 30,
        ])->assertSessionHasErrors('nombre');

        $this->post(route('admin.grupos.store'), [
            'id_plan' => 1,
            'id_semestre' => 5,
            'nombre' => 'C',
            'cupo_maximo' => 30,
        ])->assertSessionHasErrors('id_area_propedeutica');

        $this->post(route('admin.grupos.store'), [
            'id_plan' => 1,
            'id_semestre' => 5,
            'id_area_propedeutica' => 2,
            'nombre' => 'C',
            'cupo_maximo' => 30,
        ])->assertRedirect(route('admin.grupos.index'));

        $grupoArea = Grupo::where('nombre', 'C')->firstOrFail();
        $this->assertSame('C de Humanidades', $grupoArea->etiqueta());

        $this->get(route('admin.grupos.edit', $grupo))
            ->assertOk()
            ->assertSee('3-C')
            ->assertDontSee('Periodo académico')
            ->assertDontSee('site-footer');
    }

    public function test_horario_muestra_materias_del_semestre_y_los_recesos_fijos(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]))
            ->assertOk()
            ->assertSee('Literatura')
            ->assertSee('LIT-203')
            ->assertDontSee('LEN-101')
            ->assertSee('15:00 a 15:50')
            ->assertSee('Receso 20 minutos')
            ->assertSee('Receso 10 minutos')
            ->assertSee('21:20')
            ->assertSee('celda-vacia')
            ->assertSee('A-101')
            ->assertDontSee('site-footer');
    }

    public function test_selector_de_docentes_muestra_formacion_academica_y_carga(): void
    {
        $this->actingAs($this->administrador())
            ->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 5]))
            ->assertOk()
            ->assertSee('Licenciatura: Licenciatura en Matemáticas')
            ->assertSee('Maestría: Maestría en Educación Media Superior')
            ->assertSee('Tope: 35 h')
            ->assertSee('Carga actual: 0 / 35 h')
            ->assertDontSee('RFC HEVC800920MV4')
            ->assertDontSee('HEVC800920MV4')
            ->assertDontSee('CURP')
            ->assertDontSee('site-footer');
    }

    public function test_horario_de_otro_grupo_bloquea_docente_y_salon_en_la_misma_hora(): void
    {
        $this->actingAs($this->administrador());

        $materia = Materia::where('clave', 'LIT-203')->firstOrFail();

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 3,
            'celdas' => [[
                'dia' => 'Lunes',
                'inicio' => '15:00:00',
                'fin' => '15:50:00',
                'id_materia' => $materia->id_materia,
                'id_docente' => 1,
                'id_salon' => 1,
            ]],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]));

        $this->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 9]))
            ->assertOk()
            ->assertSee('Lunes|15:00:00|15:50:00', false)
            ->assertSee('Las celdas marcadas ya están ocupadas en otro horario')
            ->assertDontSee('RFC HEVC800920MV4')
            ->assertDontSee('site-footer');
    }

    public function test_horario_no_supera_el_tope_de_horas_semanales_del_docente(): void
    {
        $this->actingAs($this->administrador());

        $docente = Docente::findOrFail(2);
        $docente->update(['horas_semanales' => 1]);

        $lengua = Materia::where('clave', 'LEN-101')->firstOrFail();
        $matematicas = Materia::where('clave', 'MAT-101')->firstOrFail();

        $celdas = [
            [
                'dia' => 'Lunes',
                'inicio' => '15:00:00',
                'fin' => '15:50:00',
                'id_materia' => $lengua->id_materia,
                'id_docente' => $docente->id_docente,
                'id_salon' => 1,
            ],
            [
                'dia' => 'Martes',
                'inicio' => '15:00:00',
                'fin' => '15:50:00',
                'id_materia' => $matematicas->id_materia,
                'id_docente' => $docente->id_docente,
                'id_salon' => 1,
            ],
        ];

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 1,
            'celdas' => $celdas,
        ])->assertSessionHasErrors('celdas');

        $docente->update(['horas_semanales' => 2]);

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 1,
            'celdas' => $celdas,
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 1]));

        $this->assertSame(2, \App\Models\Horario::whereHas('clase', fn ($q) => $q->where('id_grupo', 1))->count());
    }

    public function test_horario_guarda_y_actualiza_celdas_con_materia_docente_y_salon(): void
    {
        $this->actingAs($this->administrador());

        $materia = Materia::where('clave', 'LIT-203')->firstOrFail();

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 3,
            'celdas' => [
                [
                    'dia' => 'Lunes',
                    'inicio' => '15:00:00',
                    'fin' => '15:50:00',
                    'id_materia' => $materia->id_materia,
                    'id_docente' => 1,
                    'id_salon' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]));

        $this->assertDatabaseHas('horarios', [
            'dia_semana' => 'Lunes',
            'hora_inicio' => '15:00:00',
            'hora_fin' => '15:50:00',
            'id_salon' => 1,
        ]);
        $this->assertDatabaseHas('clases', [
            'id_materia' => $materia->id_materia,
            'id_docente' => 1,
            'id_grupo' => 3,
        ]);

        $this->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]))
            ->assertOk()
            ->assertSee('Literatura')
            ->assertSee('Felipe Martin Sánchez Cortés')
            ->assertSee('A-101')
            ->assertDontSee('site-footer');

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 3,
            'celdas' => [
                [
                    'dia' => 'Martes',
                    'inicio' => '15:50:00',
                    'fin' => '16:40:00',
                    'id_materia' => $materia->id_materia,
                    'id_docente' => 1,
                    'id_salon' => 1,
                ],
            ],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]));

        $this->assertDatabaseHas('horarios', [
            'dia_semana' => 'Martes',
            'hora_inicio' => '15:50:00',
            'hora_fin' => '16:40:00',
        ]);
        // La celda anterior desaparece del horario de este grupo (otros grupos
        // pueden seguir usando ese mismo día y hora en salones distintos).
        $this->assertSame(0, \App\Models\Horario::whereHas('clase', fn ($q) => $q->where('id_grupo', 3))
            ->where('dia_semana', 'Lunes')
            ->where('hora_inicio', '15:00:00')
            ->count());
    }

    public function test_horario_rechaza_materia_de_otro_semestre_docente_solapado_y_salon_ocupado(): void
    {
        $this->actingAs($this->administrador());

        $materiaSemestreUno = Materia::where('clave', 'LEN-101')->firstOrFail();
        $materiaSemestreTres = Materia::where('clave', 'LIT-203')->firstOrFail();

        $celda = [
            'dia' => 'Lunes',
            'inicio' => '15:00:00',
            'fin' => '15:50:00',
            'id_materia' => $materiaSemestreTres->id_materia,
            'id_docente' => 1,
            'id_salon' => 1,
        ];

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 3,
            'celdas' => [array_merge($celda, ['id_materia' => $materiaSemestreUno->id_materia])],
        ])->assertSessionHasErrors('celdas.0.id_materia');

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 3,
            'celdas' => [$celda],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]));

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 9,
            'celdas' => [$celda],
        ])->assertSessionHasErrors('celdas.0.id_docente');

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 9,
            'celdas' => [array_merge($celda, ['id_docente' => 2])],
        ])->assertSessionHasErrors('celdas.0.id_salon');
    }

    public function test_horario_de_area_solo_usa_materias_del_area_y_tronco_comun(): void
    {
        $this->actingAs($this->administrador());

        $grupo = Grupo::findOrFail(5);
        $materiaArea = Materia::where('clave', 'HUM-305')->firstOrFail();
        $materiaOtraArea = Materia::where('clave', 'FMT-305')->firstOrFail();
        $materiaTronco = Materia::where('clave', 'LEN-305')->firstOrFail();

        $this->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => $grupo->id_grupo]))
            ->assertOk()
            ->assertSee('Humanidades')
            ->assertSee('Lengua y comunicación III')
            ->assertSee('LEN-305')
            ->assertDontSee('FMT-305')
            ->assertDontSee('CSO-305');

        $celdaBase = [
            'dia' => 'Lunes',
            'inicio' => '15:00:00',
            'fin' => '15:50:00',
            'id_docente' => 1,
            'id_salon' => 1,
        ];

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => $grupo->id_grupo,
            'celdas' => [array_merge($celdaBase, ['id_materia' => $materiaOtraArea->id_materia])],
        ])->assertSessionHasErrors('celdas.0.id_materia');

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => $grupo->id_grupo,
            'celdas' => [array_merge($celdaBase, ['id_materia' => $materiaArea->id_materia])],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => $grupo->id_grupo]));

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => $grupo->id_grupo,
            'celdas' => [array_merge($celdaBase, ['id_materia' => $materiaTronco->id_materia])],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => $grupo->id_grupo]));
    }

    public function test_horario_no_supera_el_tope_de_horas_semana_de_la_materia(): void
    {
        $this->actingAs($this->administrador());

        $materia = Materia::where('clave', 'HUM-305')->firstOrFail();
        $bloques = [
            ['15:00:00', '15:50:00'],
            ['15:50:00', '16:40:00'],
            ['16:40:00', '17:30:00'],
            ['17:50:00', '18:40:00'],
            ['18:40:00', '19:30:00'],
            ['19:40:00', '20:30:00'],
            ['20:30:00', '21:20:00'],
        ];

        $celdas = array_map(fn (array $bloque) => [
            'dia' => 'Lunes',
            'inicio' => $bloque[0],
            'fin' => $bloque[1],
            'id_materia' => $materia->id_materia,
            'id_docente' => 1,
            'id_salon' => 1,
        ], $bloques);

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 5,
            'celdas' => $celdas,
        ])->assertSessionHasErrors('celdas');

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 5,
            'celdas' => array_slice($celdas, 0, 6),
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 5]));

        $this->assertSame(6, \App\Models\Horario::whereHas('clase', fn ($q) => $q->where('id_grupo', 5))->count());
    }

    public function test_salon_puede_tener_menos_capacidad_que_el_cupo_si_el_grupo_no_tiene_alumnos(): void
    {
        $this->actingAs($this->administrador());

        $grupo = Grupo::findOrFail(9);
        $this->assertSame(0, $grupo->alumnos()->count());
        $this->assertGreaterThan(35, $grupo->cupo_maximo);

        $materia = Materia::where('clave', 'LIT-203')->firstOrFail();

        $this->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => $grupo->id_grupo]))
            ->assertOk()
            ->assertSee('Alumnos activos');

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => $grupo->id_grupo,
            'celdas' => [[
                'dia' => 'Viernes',
                'inicio' => '20:30:00',
                'fin' => '21:20:00',
                'id_materia' => $materia->id_materia,
                'id_docente' => 1,
                // Capacidad 35, menor al cupo máximo 45, pero el grupo no tiene alumnos.
                'id_salon' => 5,
            ]],
        ])->assertRedirect(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => $grupo->id_grupo]));

        $this->assertDatabaseHas('horarios', [
            'dia_semana' => 'Viernes',
            'hora_inicio' => '20:30:00',
            'id_salon' => 5,
        ]);
    }

    public function test_salon_si_valida_capacidad_contra_los_alumnos_activos_del_grupo(): void
    {
        $this->actingAs($this->administrador());

        $this->crearAlumnoEnGrupo(3, 'uno');
        $this->crearAlumnoEnGrupo(3, 'dos');

        $salon = Salon::create([
            'clave' => 'PEQ-01',
            'nombre' => 'Salón pequeño',
            'capacidad' => 1,
            'edificio' => 'Prueba',
            'is_active' => true,
        ]);

        $materia = Materia::where('clave', 'LIT-203')->firstOrFail();

        $this->post(route('admin.horarios.guardar'), [
            'id_plan' => 1,
            'id_grupo' => 3,
            'celdas' => [[
                'dia' => 'Viernes',
                'inicio' => '20:30:00',
                'fin' => '21:20:00',
                'id_materia' => $materia->id_materia,
                'id_docente' => 1,
                'id_salon' => $salon->id_salon,
            ]],
        ])->assertSessionHasErrors('celdas.0.id_salon');

        $this->get(route('admin.horarios.index', ['id_plan' => 1, 'id_grupo' => 3]))
            ->assertOk()
            ->assertSee('Alumnos activos');
    }

    public function test_administrador_puede_gestionar_salones_y_se_registra_en_bitacora(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.salones.store'), [
            'clave' => 'D-301',
            'nombre' => 'Salón de física',
            'capacidad' => 30,
            'edificio' => 'Bloque D',
        ])->assertRedirect(route('admin.salones.index'));

        $salon = \App\Models\Salon::where('clave', 'D-301')->firstOrFail();

        $this->assertDatabaseHas('salones', [
            'id_salon' => $salon->id_salon,
            'nombre' => 'Salón de física',
            'capacidad' => 30,
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'salones',
            'accion' => 'ALTA',
            'id_registro_afectado' => $salon->id_salon,
        ]);

        $this->put(route('admin.salones.update', $salon), [
            'clave' => 'D-302',
            'nombre' => 'Salón de física II',
            'capacidad' => 35,
            'edificio' => 'Bloque D',
            'is_active' => 1,
        ])->assertRedirect(route('admin.salones.index'));

        $this->assertDatabaseHas('salones', [
            'id_salon' => $salon->id_salon,
            'clave' => 'D-302',
            'capacidad' => 35,
        ]);

        $this->delete(route('admin.salones.destroy', $salon))
            ->assertRedirect(route('admin.salones.index'));

        $this->assertDatabaseHas('salones', [
            'id_salon' => $salon->id_salon,
            'is_active' => 0,
        ]);
    }
}
