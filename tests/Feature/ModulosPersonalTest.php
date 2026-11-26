<?php

namespace Tests\Feature;


use Database\Seeders\Demo\DemoDataSeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModulosPersonalTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = DemoDataSeeder::class;

    public function test_panel_de_superusuario_enlaza_los_modulos_ya_implementados(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($super)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('admin.personal.index'))
            ->assertSee(route('admin.bitacora.index'))
            ->assertSee(route('admin.seguridad.index'))
            ->assertSee('Acceder al módulo')
            ->assertDontSee('Próximamente');
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

        $this->get(route('admin.seguridad.index'))
            ->assertOk()
            ->assertDontSee('site-footer')
            ->assertDontSee('Ubicación');
    }

    public function test_formularios_de_personal_incluyen_el_perfil_docente(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();
        $docente = User::where('username', 'docente.prueba')->firstOrFail();

        $this->actingAs($super)
            ->get(route('admin.personal.create'))
            ->assertOk()
            ->assertSee('curp')
            ->assertSee('horas_semanales')
            ->assertSee('licenciatura')
            ->assertSee('numero_personal_sep')
            ->assertSee('contacto')
            ->assertSee('nombramiento')
            ->assertSee('categoria')
            ->assertSee('plaza')
            ->assertSee('sindicato')
            ->assertSee('titulo')
            ->assertSee('cedula_profesional');

        $this->get(route('admin.personal.edit', $docente))
            ->assertOk()
            ->assertSee('Maestría')
            ->assertSee('Doctorado')
            ->assertSee('35')
            ->assertSee('Número de personal (SEP)')
            ->assertSee('Cédula profesional');
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

        $this->get(route('admin.seguridad.index'))
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

    public function test_seguridad_puede_desbloquear_y_reiniciar_intentos(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();
        $usuario = User::where('username', 'docente.prueba')->firstOrFail();

        $usuario->forceFill([
            'intentos_fallidos' => 5,
            'bloqueado_hasta' => now()->addMinutes(10),
        ])->save();

        $this->actingAs($super)
            ->get(route('admin.seguridad.show', $usuario))
            ->assertOk()
            ->assertSee('docente.prueba')
            ->assertSee('Bloqueado');

        $this->post(route('admin.seguridad.desbloquear', $usuario))
            ->assertRedirect();

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $usuario->id_usuario,
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'accion' => 'DESBLOQUEO',
            'id_registro_afectado' => $usuario->id_usuario,
            'descripcion' => 'Desbloqueo de cuenta: docente.prueba.',
        ]);

        $usuario->refresh();
        $usuario->forceFill(['intentos_fallidos' => 3])->save();

        $this->post(route('admin.seguridad.reiniciar', $usuario))
            ->assertRedirect();

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $usuario->id_usuario,
            'intentos_fallidos' => 0,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'accion' => 'REINICIO_INTENTOS',
            'id_registro_afectado' => $usuario->id_usuario,
        ]);
    }

    public function test_seguridad_puede_suspender_y_activar_cuenta(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();
        $usuario = User::where('username', 'tutor.prueba')->firstOrFail();

        $this->actingAs($super)
            ->post(route('admin.seguridad.estatus', $usuario), ['is_active' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $usuario->id_usuario,
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('tutores', [
            'id_usuario' => $usuario->id_usuario,
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'accion' => 'SUSPENSION',
            'id_registro_afectado' => $usuario->id_usuario,
        ]);

        $this->post(route('admin.seguridad.estatus', $usuario), ['is_active' => 1])
            ->assertRedirect();

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $usuario->id_usuario,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('tutores', [
            'id_usuario' => $usuario->id_usuario,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'accion' => 'ACTIVACION',
            'id_registro_afectado' => $usuario->id_usuario,
        ]);
    }

    public function test_seguridad_no_permite_modificar_la_propia_cuenta(): void
    {
        $super = User::where('username', 'superadmin')->firstOrFail();

        $super->forceFill([
            'intentos_fallidos' => 3,
            'bloqueado_hasta' => now()->addMinutes(10),
        ])->save();

        $this->actingAs($super)
            ->post(route('admin.seguridad.desbloquear', $super))
            ->assertSessionHasErrors('seguridad');

        $super->refresh();

        $this->assertEquals(3, $super->intentos_fallidos);
        $this->assertNotNull($super->bloqueado_hasta);
    }
}
