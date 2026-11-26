<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalPublicoTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    public function test_la_portada_muestra_avisos_documentos_y_cifras(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('La Antonio en cifras')
            ->assertSee('Estudiantes activos')
            ->assertSee('Avisos para la comunidad')
            ->assertSee('Entrega de boletas del segundo parcial')
            ->assertSee('Documentos que probablemente necesitas')
            ->assertSee(route('avisos'))
            ->assertSee(route('documentos'));
    }

    public function test_los_avisos_se_pueden_buscar_y_consultar(): void
    {
        $this->get(route('avisos'))
            ->assertOk()
            ->assertSee('Taller de orientación vocacional para sexto semestre')
            ->assertDontSee('Actualización del calendario de exámenes parciales');

        $this->get(route('avisos', ['q' => 'orientación vocacional']))
            ->assertOk()
            ->assertSee('Taller de orientación vocacional para sexto semestre')
            ->assertDontSee('Entrega de boletas del segundo parcial');

        $noticia = Noticia::where('is_active', true)->firstOrFail();
        $this->get(route('avisos.show', $noticia))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    public function test_un_aviso_inactivo_no_es_publico(): void
    {
        $noticia = Noticia::where('is_active', false)->firstOrFail();

        $this->get(route('avisos.show', $noticia))->assertNotFound();
        $this->get(route('avisos'))->assertDontSee($noticia->titulo);
    }

    public function test_los_documentos_se_pueden_filtrar_y_descargar(): void
    {
        $this->get(route('documentos'))
            ->assertOk()
            ->assertSee('Formatos de evaluación REINCO')
            ->assertSee('Convocatoria de inscripción 2027')
            ->assertSee('Reportes financieros')
            ->assertDontSee('Guía de uso de la plataforma educativa');

        $this->get(route('documentos', ['categoria' => 'Formatos']))
            ->assertOk()
            ->assertSee('Formatos de evaluación REINCO')
            ->assertSee('Formato oficial de boleta de calificaciones')
            ->assertDontSee('Convocatoria de inscripción 2027');

        $this->get(route('documentos', ['categoria' => 'Inexistente']))->assertNotFound();

        $this->get(route('documentos.descargar', 2))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->get(route('documentos.descargar', 4))->assertNotFound();
    }
}
