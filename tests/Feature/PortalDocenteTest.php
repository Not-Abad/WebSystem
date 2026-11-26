<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\Clase;
use App\Models\ReincoExportacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class PortalDocenteTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    private function usuario(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    private function configurarEvaluaciones(User $docente): Clase
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($docente)
            ->post(route('docente.clases.evaluaciones', $clase), [
                'evaluaciones' => [
                    ['parcial' => '1', 'tipo' => 'ambos', 'fecha_examen' => '2026-11-30', 'fecha_entrega_proyecto' => '2026-12-05'],
                    ['parcial' => '2', 'tipo' => 'examen', 'fecha_examen' => '2027-01-20', 'fecha_entrega_proyecto' => ''],
                    ['parcial' => '3', 'tipo' => 'proyecto', 'fecha_examen' => '', 'fecha_entrega_proyecto' => '2027-03-10'],
                ],
            ])->assertRedirect();

        return $clase;
    }

    public function test_las_fechas_guardadas_dependen_del_tipo_de_evaluacion(): void
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($this->usuario('docente.historia'))
            ->post(route('docente.clases.evaluaciones', $clase), [
                'evaluaciones' => [
                    ['parcial' => '1', 'tipo' => 'examen', 'fecha_examen' => '2026-11-30', 'fecha_entrega_proyecto' => '2026-12-05'],
                    ['parcial' => '2', 'tipo' => 'proyecto', 'fecha_examen' => '2027-01-20', 'fecha_entrega_proyecto' => '2027-02-10'],
                    ['parcial' => '3', 'tipo' => 'ambos', 'fecha_examen' => '2027-03-01', 'fecha_entrega_proyecto' => '2027-03-10'],
                ],
            ])->assertRedirect();

        $evaluaciones = DB::table('evaluaciones')->where('id_clase', 1)->orderBy('parcial')->get()->keyBy('parcial');

        $this->assertSame('examen', $evaluaciones[1]->tipo);
        $this->assertSame('2026-11-30', $evaluaciones[1]->fecha_examen);
        $this->assertNull($evaluaciones[1]->fecha_entrega_proyecto);

        $this->assertSame('proyecto', $evaluaciones[2]->tipo);
        $this->assertNull($evaluaciones[2]->fecha_examen);
        $this->assertSame('2027-02-10', $evaluaciones[2]->fecha_entrega_proyecto);

        $this->assertSame('ambos', $evaluaciones[3]->tipo);
        $this->assertSame('2027-03-01', $evaluaciones[3]->fecha_examen);
        $this->assertSame('2027-03-10', $evaluaciones[3]->fecha_entrega_proyecto);
    }

    public function test_el_tipo_de_evaluacion_exige_solo_su_fecha_correspondiente(): void
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($this->usuario('docente.historia'))
            ->post(route('docente.clases.evaluaciones', $clase), [
                'evaluaciones' => [
                    ['parcial' => '1', 'tipo' => 'examen', 'fecha_examen' => '', 'fecha_entrega_proyecto' => '2026-12-05'],
                    ['parcial' => '2', 'tipo' => 'proyecto', 'fecha_examen' => '2027-01-20', 'fecha_entrega_proyecto' => ''],
                    ['parcial' => '3', 'tipo' => 'examen', 'fecha_examen' => '2027-03-01', 'fecha_entrega_proyecto' => ''],
                ],
            ])->assertSessionHasErrors([
                'evaluaciones.0.fecha_examen',
                'evaluaciones.1.fecha_entrega_proyecto',
            ]);
    }

    public function test_el_formulario_muestra_solo_las_fechas_del_tipo_seleccionado(): void
    {
        $docente = $this->usuario('docente.historia');
        $this->configurarEvaluaciones($docente);

        $html = $this->actingAs($docente)
            ->get(route('docente.clases.show', 1))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/data-fecha="examen"[^>]*>/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/data-fecha="proyecto"[^>]*hidden/',
            $html
        );
        $this->assertStringContainsString('name="evaluaciones[0][parcial]"', $html);
        $this->assertStringContainsString('name="evaluaciones[0][tipo]"', $html);
        $this->assertStringContainsString('name="evaluaciones[0][fecha_examen]"', $html);
    }

    public function test_panel_del_docente_enlaza_sus_modulos_reales(): void
    {
        $this->actingAs($this->usuario('docente.historia'))
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('docente.horario.index'))
            ->assertSee(route('docente.reinco.index'))
            ->assertSee('Mi horario')
            ->assertSee('Formatos REINCO')
            ->assertDontSee('Próximamente')
            ->assertDontSee('site-footer');
    }

    public function test_horario_del_docente_solo_muestra_sus_materias(): void
    {
        $this->actingAs($this->usuario('docente.historia'))
            ->get(route('docente.horario.index'))
            ->assertOk()
            ->assertSee('GEO-204')
            ->assertSee('LAB-01')
            ->assertDontSee('FIL-204')
            ->assertSee('Viernes')
            ->assertDontSee('site-footer');
    }

    public function test_la_clase_muestra_materia_grupo_alumnos_y_bloque_elegido(): void
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($this->usuario('docente.historia'))
            ->get(route('docente.clases.show', $clase).'?horario=1')
            ->assertOk()
            ->assertSee('Geografía')
            ->assertSee('Manuel Eduardo')
            ->assertSee('4-A')
            ->assertSee('Bloque seleccionado')
            ->assertDontSee('site-footer');
    }

    public function test_otro_docente_no_puede_consultar_la_clase(): void
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($this->usuario('docente.espanol'))
            ->get(route('docente.clases.show', $clase))
            ->assertNotFound();
    }

    public function test_registra_la_asistencia_diaria_del_alumno_de_esa_hora_y_materia(): void
    {
        $clase = Clase::findOrFail(1);
        $fecha = now()->toDateString();

        $this->actingAs($this->usuario('docente.historia'))
            ->post(route('docente.clases.asistencia', $clase), [
                'fecha' => $fecha,
                'asistencias' => [[
                    'id_alumno' => 1,
                    'estatus' => 'falta',
                    'observaciones' => 'No asistió a la sesión',
                ]],
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('asistencias', [
            'id_clase' => 1,
            'id_alumno' => 1,
            'fecha' => $fecha,
            'estatus' => 'falta',
            'observaciones' => 'No asistió a la sesión',
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'asistencias',
            'accion' => 'ALTA',
        ]);
    }

    public function test_no_se_puede_registrar_asistencia_de_un_alumno_ajeno_al_grupo(): void
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($this->usuario('docente.historia'))
            ->post(route('docente.clases.asistencia', $clase), [
                'fecha' => now()->toDateString(),
                'asistencias' => [[
                    'id_alumno' => 2,
                    'estatus' => 'presente',
                ]],
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('asistencias', ['id_clase' => 1, 'id_alumno' => 2]);
    }

    public function test_criterios_son_multiples_y_no_pueden_superar_cien(): void
    {
        $docente = $this->usuario('docente.historia');
        $clase = $this->configurarEvaluaciones($docente);
        $evaluacion = $clase->evaluaciones()->where('parcial', 1)->firstOrFail();
        $ruta = route('docente.clases.criterios', [$clase, $evaluacion]);

        $this->actingAs($docente)
            ->post($ruta, [
                'criterios' => [
                    ['seleccionado' => 1, 'nombre_rubro' => 'Examen', 'tipo_evidencia' => 'Examen', 'porcentaje' => 40, 'fecha_entrega' => ''],
                    ['seleccionado' => 1, 'nombre_rubro' => 'Tareas', 'tipo_evidencia' => 'Tareas', 'porcentaje' => 20, 'fecha_entrega' => ''],
                    ['seleccionado' => 1, 'nombre_rubro' => 'Asistencia', 'tipo_evidencia' => 'Asistencia', 'porcentaje' => 10, 'fecha_entrega' => ''],
                    ['seleccionado' => 1, 'nombre_rubro' => 'Proyecto', 'tipo_evidencia' => 'Proyecto', 'porcentaje' => 30, 'fecha_entrega' => '2026-12-05'],
                    ['seleccionado' => 0, 'nombre_rubro' => 'Actividades en clase', 'tipo_evidencia' => 'Actividades en clase', 'porcentaje' => 99, 'fecha_entrega' => ''],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('criterios_evaluacion', [
            'id_evaluacion' => $evaluacion->id_evaluacion,
            'nombre_rubro' => 'Examen',
            'porcentaje' => 40.00,
        ]);
        $this->assertDatabaseMissing('criterios_evaluacion', [
            'id_evaluacion' => $evaluacion->id_evaluacion,
            'nombre_rubro' => 'Actividades en clase',
        ]);

        $this->post($ruta, [
            'criterios' => [
                ['seleccionado' => 1, 'nombre_rubro' => 'Examen', 'tipo_evidencia' => 'Examen', 'porcentaje' => 60, 'fecha_entrega' => ''],
                ['seleccionado' => 1, 'nombre_rubro' => 'Proyecto', 'tipo_evidencia' => 'Proyecto', 'porcentaje' => 30, 'fecha_entrega' => ''],
            ],
        ])->assertRedirect();

        $this->assertSame(90.0, (float) $evaluacion->criterios()->sum('porcentaje'));

        $this->post($ruta, [
            'criterios' => [
                ['seleccionado' => 1, 'nombre_rubro' => 'Examen', 'tipo_evidencia' => 'Examen', 'porcentaje' => 60, 'fecha_entrega' => ''],
                ['seleccionado' => 1, 'nombre_rubro' => 'Proyecto', 'tipo_evidencia' => 'Proyecto', 'porcentaje' => 50, 'fecha_entrega' => ''],
            ],
        ])->assertSessionHasErrors('criterios');
    }

    public function test_calificaciones_se_calculan_y_el_alumno_puede_consultarlas(): void
    {
        $docente = $this->usuario('docente.historia');
        $clase = $this->configurarEvaluaciones($docente);
        $evaluacion = $clase->evaluaciones()->where('parcial', 1)->firstOrFail();

        $this->actingAs($docente)
            ->post(route('docente.clases.criterios', [$clase, $evaluacion]), [
                'criterios' => [
                    ['seleccionado' => 1, 'nombre_rubro' => 'Examen', 'tipo_evidencia' => 'Examen', 'porcentaje' => 70, 'fecha_entrega' => ''],
                    ['seleccionado' => 1, 'nombre_rubro' => 'Proyecto', 'tipo_evidencia' => 'Proyecto', 'porcentaje' => 30, 'fecha_entrega' => ''],
                ],
            ])->assertRedirect();

        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get();
        $calificaciones = [];
        foreach ($clase->alumnosActivos() as $alumno) {
            $esAlumnoDemo = $alumno->id_alumno === 1;
            $calificaciones[$alumno->id_alumno] = [
                $criterios[0]->id_criterio => $esAlumnoDemo ? 80 : 75,
                $criterios[1]->id_criterio => $esAlumnoDemo ? 90 : 85,
            ];
        }

        $this->post(route('docente.clases.calificaciones', [$clase, $evaluacion]), [
            'calificaciones' => $calificaciones,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('calificaciones', [
            'id_criterio' => $criterios[0]->id_criterio,
            'id_alumno' => 1,
            'porcentaje_obtenido' => 80.00,
            'puntaje_obtenido' => 5.60,
        ]);
        $this->assertDatabaseHas('calificaciones', [
            'id_criterio' => $criterios[1]->id_criterio,
            'id_alumno' => 1,
            'puntaje_obtenido' => 2.70,
        ]);

        $this->actingAs($this->usuario('alumno.prueba'))
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('alumno.materias.index'))
            ->assertDontSee('Próximamente');

        $this->post(route('alumno.materias.aceptarCriterios', [$clase, $evaluacion]), [
            'acepto' => 1,
        ])->assertRedirect();

        $this->get(route('alumno.materias.show', $clase))
            ->assertOk()
            ->assertSee('Examen')
            ->assertSee('30/11/2026')
            ->assertSee('8.30')
            ->assertDontSee('site-footer');
    }

    public function test_genera_reinco_excel_y_lo_guarda_en_el_gestor_de_archivos(): void
    {
        Storage::fake('local');

        $docente = $this->usuario('docente.historia');
        $clase = $this->configurarEvaluaciones($docente);
        $evaluacion = $clase->evaluaciones()->where('parcial', 1)->firstOrFail();

        $this->actingAs($docente)
            ->post(route('docente.clases.criterios', [$clase, $evaluacion]), [
                'criterios' => [[
                    'seleccionado' => 1,
                    'nombre_rubro' => 'Examen',
                    'tipo_evidencia' => 'Examen',
                    'porcentaje' => 100,
                    'fecha_entrega' => '2026-12-05',
                ]],
            ])->assertRedirect();

        $this->post(route('docente.clases.reinco', [$clase, $evaluacion]), ['destino' => 'descargar'])
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->post(route('docente.clases.reinco', [$clase, $evaluacion]), [
            'destino' => 'guardar',
            'estatus' => 'R1',
            'formato_calificacion' => 'entero',
            'turno' => 'Vespertino',
            'periodo_escolar' => '2026-2027',
        ])->assertRedirect()->assertSessionHas('status');

        $exportacion = ReincoExportacion::firstOrFail();
        $this->assertSame('R1', $exportacion->estatus);
        $this->assertSame('entero', $exportacion->formato_calificacion);
        $this->assertSame('Vespertino', $exportacion->turno);
        $this->assertSame('2026-2027', $exportacion->periodo_escolar);
        $this->assertSame(5, $exportacion->hojas);
        Storage::disk('local')->assertExists($exportacion->archivo_ruta);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open(Storage::disk('local')->path($exportacion->archivo_ruta)) === true);
        $workbook = (string) $zip->getFromName('xl/workbook.xml');
        $styles = (string) $zip->getFromName('xl/styles.xml');
        $sheet205 = (string) $zip->getFromName('xl/worksheets/sheet2.xml');
        $sheet206b = (string) $zip->getFromName('xl/worksheets/sheet5.xml');
        $zip->close();

        foreach (['Datos generales', 'REINCO 205', 'REINCO 206-A', '206-A reverso', 'REINCO 206-B'] as $hoja) {
            $this->assertStringContainsString('name="'.$hoja.'"', $workbook);
        }
        $this->assertStringContainsString('CCT: 30EBH0389X', $sheet205);
        $this->assertStringContainsString('Hoja 2 de 5', $sheet205);
        $this->assertStringContainsString('CURP (clave de control)', $sheet205);
        $this->assertStringContainsString('Firmas para archivo y cotejo DGB', $sheet205);
        $this->assertStringContainsString('Control escolar / Subdirección', $sheet205);
        $this->assertStringContainsString('Estatus', $sheet206b);
        $this->assertStringContainsString('R1', $sheet206b);
        $this->assertMatchesRegularExpression('/<pageSetup[^>]*orientation="landscape"[^>]*fitToWidth="1"/', $sheet206b);
        $this->assertStringContainsString('<pageMargins', $sheet206b);
        $this->assertStringContainsString('Hoja &amp;P de &amp;N', $sheet206b);
        $this->assertStringContainsString('formatCode="0.00"', $styles);
        $this->assertMatchesRegularExpression('/s="11"><v>\d+<\/v>/', $sheet206b);

        $this->get(route('docente.reinco.index'))
            ->assertOk()
            ->assertSee($exportacion->archivo_ruta)
            ->assertDontSee('site-footer');

        $this->get(route('docente.reinco.descargar', $exportacion))->assertOk();
    }

    public function test_dto_reinco_separa_evaluacion_de_presentacion(): void
    {
        $docente = $this->usuario('docente.historia');
        $clase = $this->configurarEvaluaciones($docente);
        $evaluacion = $clase->evaluaciones()->where('parcial', 1)->firstOrFail();

        $datos = \App\Data\Reinco\ReincoExportData::desdeClase($clase, $evaluacion, [
            'formato_calificacion' => 'decimal',
        ]);
        $arreglo = $datos->toArray();

        $this->assertSame('30EBH0389X', $arreglo['institucional']['cct']);
        $this->assertSame('Bachillerato', $arreglo['institucional']['nivel']);
        $this->assertArrayHasKey('area_componente', $arreglo['grupo']);
        $this->assertArrayHasKey('evidencias', $arreglo);
        $this->assertSame(2, $arreglo['presentacion']['decimales']);
        $this->assertSame(5, $arreglo['presentacion']['hojas']);
        $this->assertCount($datos->alumnos->count(), $arreglo['alumnos']);

        $esperados = $clase->alumnosActivos()
            ->map(fn ($alumno) => mb_strtoupper(trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre)))
            ->all();
        $this->assertSame(
            $esperados,
            $datos->alumnos->map(fn ($alumno) => mb_strtoupper($alumno->nombreCompleto))->all()
        );

        $curps = $datos->alumnos->map(fn ($alumno) => $alumno->curp)->all();
        $this->assertNotEmpty($curps);
        $this->assertSame(strtoupper($curps[0]), $curps[0]);
        $this->assertArrayHasKey('general', $datos->alumnos->first()->asistenciasPorParcial);
    }

    public function test_las_opciones_reinco_aparecen_una_sola_vez_por_evaluacion(): void
    {
        $docente = $this->usuario('docente.historia');
        $clase = $this->configurarEvaluaciones($docente);

        foreach ($clase->evaluaciones()->orderBy('parcial')->get() as $evaluacion) {
            $this->actingAs($docente)
                ->post(route('docente.clases.criterios', [$clase, $evaluacion]), [
                    'criterios' => [[
                        'seleccionado' => 1,
                        'nombre_rubro' => 'Examen',
                        'tipo_evidencia' => 'Examen',
                        'porcentaje' => 100,
                        'fecha_entrega' => '2026-12-05',
                    ]],
                ])->assertRedirect();
        }

        $html = (string) $this->get(route('docente.clases.show', $clase))
            ->assertOk()
            ->getContent();

        $this->assertSame(3, substr_count($html, 'name="estatus"'));
        $this->assertSame(3, substr_count($html, 'name="formato_calificacion"'));
        $this->assertSame(6, substr_count($html, 'name="destino"'));
    }

    public function test_roles_ajenos_no_pueden_entrar_al_portal_docente(): void
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($this->usuario('admin.escolar'))
            ->get(route('docente.horario.index'))
            ->assertForbidden();

        $this->actingAs($this->usuario('alumno.prueba'))
            ->get(route('docente.clases.show', $clase))
            ->assertForbidden();

        $this->actingAs($this->usuario('docente.historia'))
            ->get(route('alumno.materias.index'))
            ->assertForbidden();
    }
}
