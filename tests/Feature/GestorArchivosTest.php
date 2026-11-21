<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GestorArchivosTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->makeDirectory('alumnos');
    }

    private function administrador(): User
    {
        return User::where('username', 'admin.escolar')->firstOrFail();
    }

    public function test_panel_de_administrador_enlaza_el_gestor_de_archivos(): void
    {
        $this->actingAs($this->administrador())
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('admin.archivos.index'))
            ->assertSee('Gestor de archivos');
    }

    public function test_administrador_puede_crear_carpetas_subir_descargar_y_eliminar_archivos(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.archivos.carpeta'), [
            'ruta' => '',
            'nombre' => 'Boletas 2026',
        ])->assertRedirect();

        $this->assertTrue(Storage::disk('local')->directoryExists('Boletas 2026'));
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'archivos',
            'accion' => 'ALTA',
        ]);

        $this->post(route('admin.archivos.subir'), [
            'ruta' => 'Boletas 2026',
            'archivo' => UploadedFile::fake()->create('boleta.pdf', 120, 'application/pdf'),
        ])->assertRedirect();

        $archivos = Storage::disk('local')->allFiles('Boletas 2026');
        $this->assertCount(1, $archivos);

        $ruta = $archivos[0];

        $this->get(route('admin.archivos.descargar', ['archivo' => $ruta]))
            ->assertOk();

        $this->delete(route('admin.archivos.eliminar'), [
            'tipo' => 'archivo',
            'ruta' => $ruta,
        ])->assertRedirect();

        Storage::disk('local')->assertMissing($ruta);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'archivos',
            'accion' => 'BAJA',
        ]);
    }

    public function test_no_se_pueden_crear_carpetas_con_ruta_traversal(): void
    {
        $this->actingAs($this->administrador())
            ->post(route('admin.archivos.carpeta'), [
                'ruta' => '',
                'nombre' => '../salir',
            ])
            ->assertSessionHasErrors('nombre');

        $this->assertFalse(Storage::disk('local')->directoryExists('salir'));
    }

    public function test_selector_de_archivos_devuelve_la_funcion_para_llenar_el_campo(): void
    {
        Storage::disk('local')->put('alumnos/acta.pdf', 'contenido de prueba');

        $this->actingAs($this->administrador())
            ->get(route('admin.archivos.picker', [
                'campo' => 'acta_nacimiento_ruta',
                'ruta' => 'alumnos',
            ]))
            ->assertOk()
            ->assertSee('parent.seleccionarArchivo', false)
            ->assertDontSee('site-footer');
    }

    public function test_otro_rol_no_puede_acceder_al_gestor_de_archivos(): void
    {
        $docente = User::where('username', 'docente.prueba')->firstOrFail();

        $this->actingAs($docente)
            ->get(route('admin.archivos.index'))
            ->assertForbidden();
    }
}
