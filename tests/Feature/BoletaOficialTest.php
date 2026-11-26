<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\Clase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class BoletaOficialTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    private function usuario(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    /** @return array{0: Clase, 1: \App\Models\Evaluacion} */
    private function prepararEvaluacion(User $docente): array
    {
        $clase = Clase::findOrFail(1);

        $this->actingAs($docente)
            ->post(route('docente.clases.evaluaciones', $clase), [
                'evaluaciones' => [
                    ['parcial' => '1', 'tipo' => 'examen', 'fecha_examen' => '2026-11-30', 'fecha_entrega_proyecto' => ''],
                    ['parcial' => '2', 'tipo' => 'examen', 'fecha_examen' => '2027-01-20', 'fecha_entrega_proyecto' => ''],
                    ['parcial' => '3', 'tipo' => 'examen', 'fecha_examen' => '2027-03-10', 'fecha_entrega_proyecto' => ''],
                ],
            ])->assertRedirect();

        $evaluacion = $clase->evaluaciones()->where('parcial', 1)->firstOrFail();

        $this->post(route('docente.clases.criterios', [$clase, $evaluacion]), [
            'criterios' => [
                ['seleccionado' => 1, 'nombre_rubro' => 'Examen', 'tipo_evidencia' => 'Examen', 'porcentaje' => 70, 'fecha_entrega' => ''],
                ['seleccionado' => 1, 'nombre_rubro' => 'Proyecto', 'tipo_evidencia' => 'Proyecto', 'porcentaje' => 30, 'fecha_entrega' => ''],
            ],
        ])->assertRedirect();

        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get();
        $calificaciones = [];
        foreach ($clase->alumnosActivos() as $alumno) {
            $calificaciones[$alumno->id_alumno] = [
                $criterios[0]->id_criterio => 80,
                $criterios[1]->id_criterio => 90,
            ];
        }

        $this->post(route('docente.clases.calificaciones', [$clase, $evaluacion]), [
            'calificaciones' => $calificaciones,
        ])->assertRedirect()->assertSessionHas('status');

        return [$clase, $evaluacion];
    }

    public function test_la_boleta_se_exporta_con_las_calificaciones_del_parcial(): void
    {
        $docente = $this->usuario('docente.historia');
        [$clase, $evaluacion] = $this->prepararEvaluacion($docente);

        $respuesta = $this->actingAs($docente)
            ->post(route('docente.clases.boleta', [$clase, $evaluacion]), [
                'columna' => 'AA',
                'periodo_escolar' => '26-27',
            ])
            ->assertOk()
            ->assertHeader('content-disposition');

        $contenido = $respuesta->streamedContent();
        $this->assertStringStartsWith('PK', $contenido);

        $temporal = tempnam(sys_get_temp_dir(), 'boleta_test_');
        file_put_contents($temporal, $contenido);

        try {
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($temporal) === true);

            $hojas = [];
            foreach (['xl/workbook.xml', 'xl/worksheets/sheet1.xml', 'xl/worksheets/sheet4.xml'] as $parte) {
                $hojas[$parte] = $zip->getFromName($parte);
            }
            $zip->close();

            $this->assertStringContainsString('BOLETA parcial', $hojas['xl/workbook.xml']);
            $this->assertStringContainsString('CONCATENATE(DATOS!G11', $hojas['xl/worksheets/sheet4.xml']);

            $datos = new \DOMDocument();
            $datos->loadXML($hojas['xl/worksheets/sheet1.xml']);
            $x = new \DOMXPath($datos);

            $valor = $x->query('//*[local-name()="c" and @r="AA11"]/*[local-name()="v"]')->item(0);
            $this->assertNotNull($valor, 'Falta la calificación en la columna oficial.');
            $this->assertEquals(8.3, (float) $valor->textContent, '', 0.01);

            $nombre = $x->query('//*[local-name()="c" and @r="G11"]/*[local-name()="is"]/*[local-name()="t"]')->item(0);
            $this->assertNotNull($nombre, 'Falta el apellido del alumno.');
            $this->assertNotSame('', trim($nombre->textContent));

            $periodo = $x->query('//*[local-name()="c" and @r="I5"]/*[local-name()="is"]/*[local-name()="t"]')->item(0);
            $this->assertNotNull($periodo);
            $this->assertSame('26-27', $periodo->textContent);
        } finally {
            @unlink($temporal);
        }
    }

    public function test_la_columna_de_la_boleta_debe_ser_valida(): void
    {
        $docente = $this->usuario('docente.historia');
        [$clase, $evaluacion] = $this->prepararEvaluacion($docente);

        $this->actingAs($docente)
            ->post(route('docente.clases.boleta', [$clase, $evaluacion]), ['columna' => 'ZZ'])
            ->assertSessionHasErrors('columna');
    }

    public function test_un_docente_no_puede_exportar_la_clase_de_otro(): void
    {
        $docente = $this->usuario('docente.historia');
        [$clase] = $this->prepararEvaluacion($docente);

        $idDocente = $docente->docente()->firstOrFail()->id_docente;
        $otra = Clase::where('id_docente', '!=', $idDocente)->firstOrFail();
        $evaluacion = \App\Models\Evaluacion::create([
            'id_clase' => $otra->id_clase,
            'parcial' => 1,
            'tipo' => 'examen',
            'fecha_examen' => '2026-11-30',
        ]);

        $this->actingAs($docente)
            ->post(route('docente.clases.boleta', [$otra, $evaluacion]), ['columna' => 'AA'])
            ->assertNotFound();
    }
}
