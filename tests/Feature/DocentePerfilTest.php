<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocentePerfilTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function docente(): User
    {
        return User::where('username', 'docente.historia')->firstOrFail();
    }

    public function test_docente_ve_sus_datos_institucionales_completos(): void
    {
        $usuario = $this->docente();
        $docente = $usuario->docente()->firstOrFail();

        $this->actingAs($usuario)
            ->get(route('docente.perfil.index'))
            ->assertOk()
            ->assertSee('Mis datos')
            ->assertSee($docente->nombreCompleto())
            ->assertSee($docente->curp)
            ->assertSee($docente->rfc)
            ->assertSee($docente->correo_institucional)
            ->assertSee($docente->numero_personal_sep)
            ->assertSee($docente->cedula_profesional)
            ->assertSee('Imagen de perfil')
            ->assertDontSee('password_hash');
    }

    public function test_docente_puede_subir_imagen_de_perfil_y_se_muestra_en_el_panel(): void
    {
        Storage::fake('public');

        $usuario = $this->docente();

        $this->actingAs($usuario)
            ->post(route('docente.perfil.foto'), [
                'foto_perfil' => UploadedFile::fake()->createWithContent(
                    'perfil.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==')
                ),
            ])
            ->assertRedirect();

        $docente = $usuario->docente()->firstOrFail();
        $this->assertNotNull($docente->foto_perfil_ruta);
        Storage::disk('public')->assertExists($docente->foto_perfil_ruta);

        $this->get(route('panel'))
            ->assertOk()
            ->assertSee($docente->foto_perfil_ruta)
            ->assertSee('panel-avatar-foto');
    }

    public function test_rechaza_archivos_que_no_son_imagenes(): void
    {
        Storage::fake('public');

        $usuario = $this->docente();

        $this->actingAs($usuario)
            ->post(route('docente.perfil.foto'), [
                'foto_perfil' => UploadedFile::fake()->create('perfil.txt', 8, 'text/plain'),
            ])
            ->assertSessionHasErrors('foto_perfil');

        $this->assertNull($usuario->docente()->firstOrFail()->foto_perfil_ruta);
    }

    public function test_otros_roles_no_pueden_consultar_mis_datos(): void
    {
        $alumno = User::where('username', 'alumno.prueba')->firstOrFail();

        $this->actingAs($alumno)
            ->get(route('docente.perfil.index'))
            ->assertForbidden();
    }
}
