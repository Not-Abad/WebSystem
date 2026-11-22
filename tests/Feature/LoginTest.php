<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_pagina_de_login_carga(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Portal')
            ->assertSee('Acceso institucional')
            ->assertSeeText('Bienvenido de nuevo')
            ->assertDontSee('Perfiles con acceso al sistema')
            ->assertDontSee('Prueba123!');
    }

    public function test_usuario_puede_iniciar_sesion_y_ve_su_panel(): void
    {
        $usuario = User::where('username', 'admin.escolar')->firstOrFail();

        $this->post('/login', [
            'username' => 'admin.escolar',
            'password' => 'Prueba123!',
        ])->assertRedirect(route('panel'));

        $this->assertAuthenticatedAs($usuario);

        $this->get(route('panel'))
            ->assertOk()
            ->assertSee('Administrador')
            ->assertSee('Expedientes');
    }

    public function test_login_siempre_abre_mi_panel_aunque_haya_una_url_intendida(): void
    {
        $this->get(route('admin.alumnos.index'));

        $this->post('/login', [
            'username' => 'admin.escolar',
            'password' => 'Prueba123!',
        ])->assertRedirect(route('panel'));
    }

    public function test_credenciales_incorrectas_no_autentican(): void
    {
        $this->from('/login')->post('/login', [
            'username' => 'admin.escolar',
            'password' => 'contraseña-incorrecta',
        ])->assertRedirect('/login')->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_ingresar(): void
    {
        $usuario = User::where('username', 'docente.prueba')->firstOrFail();
        $usuario->is_active = false;
        $usuario->save();

        $this->from('/login')->post('/login', [
            'username' => $usuario->username,
            'password' => 'Prueba123!',
        ])->assertRedirect('/login')->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_cierre_de_sesion(): void
    {
        $this->post('/login', [
            'username' => 'alumno.prueba',
            'password' => 'Prueba123!',
        ])->assertRedirect(route('panel'));

        $this->assertAuthenticated();

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
