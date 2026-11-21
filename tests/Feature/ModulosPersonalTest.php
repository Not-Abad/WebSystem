<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModulosPersonalTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_panel_de_superusuario_enlaza_los_modulos_ya_implementados(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($super)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('admin.personal.index'))
            ->assertSee(route('admin.bitacora.index'))
            ->assertSee('Acceder al módulo')
            ->assertSee('Próximamente');
    }

    public function test_los_modulos_de_superusuario_no_muestran_el_pie_de_pagina(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($super)
            ->get(route('admin.personal.index'))
            ->assertOk()
            ->assertDontSee('site-footer')
            ->assertDontSee('Ubicación');

        $this->get(route('admin.bitacora.index'))
            ->assertOk()
            ->assertDontSee('site-footer')
            ->assertDontSee('Ubicación');
    }

    public function test_superusuario_puede_crear_administrador_y_queda_en_bitacora(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($super)
            ->post(route('admin.personal.store'), [
                'tipo' => 'Administrador',
                'nombre' => 'Prueba',
                'apellido_paterno' => 'Apellido',
                'apellido_materno' => 'Nuevo',
                'matricula_rfc' => 'ADM-NUEVO-001',
                'correo_institucional' => 'nuevo@eba-amr.edu.mx',
                'id_puesto' => 1,
                'username' => 'admin.nuevo',
                'password' => 'ClaveSegura123!',
                'password_confirmation' => 'ClaveSegura123!',
            ])
            ->assertRedirect(route('admin.personal.index'));

        $usuario = User::where('username', 'admin.nuevo')->firstOrFail();

        $this->assertTrue(Hash::check('ClaveSegura123!', $usuario->password_hash));
        $this->assertEquals(2, $usuario->id_rol);

        $this->assertDatabaseHas('administradores', [
            'id_usuario' => $usuario->id_usuario,
            'matricula_rfc' => 'ADM-NUEVO-001',
            'correo_institucional' => 'nuevo@eba-amr.edu.mx',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'id_usuario' => $super->id_usuario,
            'tabla_afectada' => 'usuarios',
            'accion' => 'ALTA',
            'id_registro_afectado' => $usuario->id_usuario,
            'descripcion' => 'Alta de Administrador: Prueba Apellido (admin.nuevo).',
        ]);
    }

    public function test_superusuario_puede_editar_y_dar_de_baja_logica(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();
        $usuario = User::where('username', 'admin.recursos')->firstOrFail();

        $this->actingAs($super)
            ->put(route('admin.personal.update', $usuario), [
                'nombre' => 'María Actualizada',
                'apellido_paterno' => 'López',
                'apellido_materno' => 'Hernández',
                'matricula_rfc' => 'ADM-RRHH-002',
                'correo_institucional' => 'recursos@eba-amr.edu.mx',
                'id_puesto' => 2,
                'username' => 'admin.recursos',
                'password' => '',
                'password_confirmation' => '',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.personal.index'));

        $this->assertDatabaseHas('administradores', [
            'id_administrador' => 2,
            'nombre' => 'María Actualizada',
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'accion' => 'EDICION',
            'id_registro_afectado' => $usuario->id_usuario,
        ]);

        $this->actingAs($super)
            ->delete(route('admin.personal.destroy', $usuario))
            ->assertRedirect(route('admin.personal.index'));

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $usuario->id_usuario,
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('administradores', [
            'id_administrador' => 2,
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'accion' => 'BAJA',
            'id_registro_afectado' => $usuario->id_usuario,
        ]);
    }

    public function test_usuario_con_otro_rol_no_puede_acceder_a_los_modulos(): void
    {
        $docente = User::where('username', 'docente.prueba')->firstOrFail();

        $this->actingAs($docente)
            ->get(route('admin.personal.index'))
            ->assertForbidden();

        $this->get(route('admin.bitacora.index'))
            ->assertForbidden();
    }

    public function test_bitacora_se_puede_filtrar_y_exportar(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($super)
            ->get(route('admin.bitacora.index', ['accion' => 'ALTA']))
            ->assertOk()
            ->assertSee('ALTA')
            ->assertDontSee('Exportación CSV de bitácora');

        $this->get(route('admin.bitacora.export', ['accion' => 'ALTA']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_no_se_acepta_un_usuario_con_intento_de_inyeccion(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();
        $usuarioMalicioso = "admin' OR 1=1 --";

        $this->actingAs($super)
            ->post(route('admin.personal.store'), [
                'tipo' => 'Administrador',
                'nombre' => 'Prueba',
                'apellido_paterno' => 'Seguridad',
                'matricula_rfc' => 'ADM-SEC-001',
                'correo_institucional' => 'seguridad@eba-amr.edu.mx',
                'id_puesto' => 1,
                'username' => $usuarioMalicioso,
                'password' => 'ClaveSegura123!',
                'password_confirmation' => 'ClaveSegura123!',
            ])
            ->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('usuarios', ['username' => $usuarioMalicioso]);
    }
}
