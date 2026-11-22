<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModulosAdministradorTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function administrador(): User
    {
        return User::where('username', 'admin.escolar')->firstOrFail();
    }

    public function test_panel_de_administrador_enlaza_los_modulos_implementados(): void
    {
        $this->actingAs($this->administrador())
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('admin.alumnos.index'))
            ->assertSee(route('admin.docentes.index'))
            ->assertSee(route('admin.tutores.index'))
            ->assertSee(route('admin.portal.noticias'))
            ->assertSee(route('admin.archivos.index'))
            ->assertSee(route('admin.catalogos.index'))
            ->assertSee(route('admin.grupos.index'))
            ->assertSee(route('admin.horarios.index'))
            ->assertSee(route('admin.salones.index'))
            ->assertSee('AMRV (Antonio María de Rivera Vespertina)')
            ->assertDontSee('EBA', false)
            ->assertSee('Acceder al módulo')
            ->assertDontSee('Próximamente');
    }

    public function test_modulos_de_administrador_no_muestran_el_pie_de_pagina(): void
    {
        $this->actingAs($this->administrador());

        foreach ([
            route('admin.alumnos.index'),
            route('admin.docentes.index'),
            route('admin.tutores.index'),
            route('admin.portal.noticias'),
            route('admin.portal.documentos'),
            route('admin.portal.reportes'),
            route('admin.portal.galeria'),
            route('admin.archivos.index'),
            route('admin.catalogos.index'),
            route('admin.catalogos.materias'),
            route('admin.grupos.index'),
            route('admin.horarios.index'),
            route('admin.salones.index'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('site-footer')
                ->assertDontSee('Ubicación');
        }
    }

    public function test_otro_rol_no_puede_acceder_a_los_modulos_de_administrador(): void
    {
        $docente = User::where('username', 'docente.prueba')->firstOrFail();

        $this->actingAs($docente)
            ->get(route('admin.alumnos.index'))
            ->assertForbidden();

        $this->get(route('admin.docentes.index'))->assertForbidden();
        $this->get(route('admin.tutores.index'))->assertForbidden();
        $this->get(route('admin.portal.noticias'))->assertForbidden();
        $this->get(route('admin.catalogos.index'))->assertForbidden();
        $this->get(route('admin.grupos.index'))->assertForbidden();
        $this->get(route('admin.horarios.index'))->assertForbidden();
        $this->get(route('admin.salones.index'))->assertForbidden();
    }

    public function test_administrador_puede_crear_tutor_y_queda_registrado_en_bitacora(): void
    {
        $this->actingAs($this->administrador())
            ->post(route('admin.tutores.store'), [
                'nombre' => 'Marta',
                'apellido_paterno' => 'Jiménez',
                'apellido_materno' => 'Ruiz',
                'telefono' => '228 111 2233',
                'correo' => 'marta.jimenez@ejemplo.com',
                'ine_ruta' => 'private/tutores/ine_jimenez.pdf',
                'username' => 'tutor.jimenez',
                'password' => 'Prueba123!',
                'password_confirmation' => 'Prueba123!',
            ])
            ->assertRedirect(route('admin.tutores.index'));

        $usuario = User::where('username', 'tutor.jimenez')->firstOrFail();

        $this->assertTrue(Hash::check('Prueba123!', $usuario->password_hash));
        $this->assertDatabaseHas('tutores', [
            'id_usuario' => $usuario->id_usuario,
            'correo' => 'marta.jimenez@ejemplo.com',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'tutores',
            'accion' => 'ALTA',
            'descripcion' => 'Alta de Tutor: Marta Jiménez (tutor.jimenez).',
        ]);
    }

    public function test_administrador_puede_crear_alumno_y_darle_baja_definitiva(): void
    {
        $this->actingAs($this->administrador())
            ->post(route('admin.alumnos.store'), [
                'id_grupo' => 1,
                'id_estado_alumno' => 1,
                'id_plan' => 1,
                'id_semestre' => 1,
                'id_capacitacion' => '',
                'id_area_propedeutica' => null,
                'actividad_escolar_apa' => 'Deportes',
                'promedio_general' => 0,
                'nombre' => 'Prueba',
                'apellido_paterno' => 'Academica',
                'apellido_materno' => 'Final',
                'curp' => 'GARC900101HDFRRS01',
                'correo' => 'prueba.academica@eba-amr.edu.mx',
                'acta_nacimiento_ruta' => 'alumnos/acta_prueba_final.pdf',
                'boleta_secundaria_ruta' => 'alumnos/boleta_prueba_final.pdf',
                'username' => 'alumno.prueba.final',
                'password' => 'Prueba123!',
                'password_confirmation' => 'Prueba123!',
            ])
            ->assertRedirect(route('admin.alumnos.index'));

        $alumno = Alumno::where('correo', 'prueba.academica@eba-amr.edu.mx')->firstOrFail();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_grupo' => 1,
            'id_tutor' => null,
            'id_area_propedeutica' => null,
            'actividad_escolar_apa' => 'Deportes',
        ]);
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $alumno->id_usuario,
            'username' => 'alumno.prueba.final',
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'alumnos',
            'accion' => 'ALTA',
            'id_registro_afectado' => $alumno->id_alumno,
        ]);

        $this->delete(route('admin.alumnos.destroy', $alumno))
            ->assertRedirect(route('admin.alumnos.index'));

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_estado_alumno' => 4,
        ]);
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $alumno->id_usuario,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'alumnos',
            'accion' => 'BAJA',
            'id_registro_afectado' => $alumno->id_alumno,
        ]);
    }

    public function test_formulario_de_alumnos_incluye_area_propedeutica_y_apa(): void
    {
        $this->actingAs($this->administrador())
            ->get(route('admin.alumnos.create'))
            ->assertOk()
            ->assertSee('campo-area-propedeutica')
            ->assertSee('campo-apa')
            ->assertSee('Actividad para escolar (APA)');
    }

    public function test_area_propedeutica_es_obligatoria_desde_quinto_semestre(): void
    {
        $this->actingAs($this->administrador());

        $datos = [
            'id_grupo' => 5,
            'id_estado_alumno' => 1,
            'id_plan' => 1,
            'id_semestre' => 5,
            'actividad_escolar_apa' => 'Deportes',
            'promedio_general' => 8,
            'nombre' => 'Area',
            'apellido_paterno' => 'Obligatoria',
            'curp' => 'AREA900102HDFRRS02',
            'correo' => 'area.obligatoria@eba-amr.edu.mx',
            'acta_nacimiento_ruta' => 'alumnos/acta_area.pdf',
            'boleta_secundaria_ruta' => 'alumnos/boleta_area.pdf',
            'username' => 'alumno.area.prueba',
            'password' => 'Prueba123!',
            'password_confirmation' => 'Prueba123!',
        ];

        $this->post(route('admin.alumnos.store'), $datos)
            ->assertSessionHasErrors('id_area_propedeutica');

        $this->post(route('admin.alumnos.store'), array_merge($datos, [
            'id_area_propedeutica' => 2,
        ]))->assertRedirect(route('admin.alumnos.index'));

        $alumno = Alumno::where('correo', 'area.obligatoria@eba-amr.edu.mx')->firstOrFail();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_area_propedeutica' => 2,
            'actividad_escolar_apa' => null,
        ]);
    }

    public function test_apa_es_obligatoria_hasta_cuarto_semestre(): void
    {
        $this->actingAs($this->administrador());

        $datos = [
            'id_grupo' => 2,
            'id_estado_alumno' => 1,
            'id_plan' => 1,
            'id_semestre' => 2,
            'id_area_propedeutica' => 1,
            'promedio_general' => 8,
            'nombre' => 'Apa',
            'apellido_paterno' => 'Obligatoria',
            'curp' => 'APAA900102HDFRRS03',
            'correo' => 'apa.obligatoria@eba-amr.edu.mx',
            'acta_nacimiento_ruta' => 'alumnos/acta_apa.pdf',
            'boleta_secundaria_ruta' => 'alumnos/boleta_apa.pdf',
            'username' => 'alumno.apa.prueba',
            'password' => 'Prueba123!',
            'password_confirmation' => 'Prueba123!',
        ];

        $this->post(route('admin.alumnos.store'), $datos)
            ->assertSessionHasErrors('actividad_escolar_apa');

        $this->post(route('admin.alumnos.store'), array_merge($datos, [
            'actividad_escolar_apa' => 'Tecnología',
        ]))->assertRedirect(route('admin.alumnos.index'));

        $alumno = Alumno::where('correo', 'apa.obligatoria@eba-amr.edu.mx')->firstOrFail();

        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'id_area_propedeutica' => null,
            'actividad_escolar_apa' => 'Tecnología',
        ]);
    }

    public function test_grupo_debe_corresponder_al_semestre_seleccionado(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.alumnos.store'), [
            'id_grupo' => 5,
            'id_estado_alumno' => 1,
            'id_plan' => 1,
            'id_semestre' => 3,
            'actividad_escolar_apa' => 'Deportes',
            'promedio_general' => 8,
            'nombre' => 'Grupo',
            'apellido_paterno' => 'Invalido',
            'curp' => 'GRUP900103HDFRRS04',
            'correo' => 'grupo.invalido@eba-amr.edu.mx',
            'acta_nacimiento_ruta' => 'alumnos/acta_grupo.pdf',
            'boleta_secundaria_ruta' => 'alumnos/boleta_grupo.pdf',
            'username' => 'alumno.grupo.invalido',
            'password' => 'Prueba123!',
            'password_confirmation' => 'Prueba123!',
        ])->assertSessionHasErrors('id_grupo');
    }

    public function test_administrador_puede_gestionar_el_portal_y_las_bajas_logicas(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.portal.noticias.guardar'), [
            'titulo' => 'Noticia de prueba automatizada',
            'contenido' => 'Contenido suficiente para cumplir con la validación del formulario de noticias.',
            'imagen_portada_ruta' => 'imagenes/noticias/prueba.jpg',
            'fecha_publicacion' => '2026-11-20 10:00:00',
            'is_active' => 1,
        ])->assertRedirect(route('admin.portal.noticias'));

        $this->assertDatabaseHas('noticias', ['titulo' => 'Noticia de prueba automatizada', 'is_active' => 1]);
        $this->assertDatabaseHas('bitacora_auditoria', ['tabla_afectada' => 'noticias', 'accion' => 'ALTA']);

        $noticia = \App\Models\Noticia::where('titulo', 'Noticia de prueba automatizada')->firstOrFail();
        $this->delete(route('admin.portal.noticias.eliminar', $noticia))
            ->assertRedirect(route('admin.portal.noticias'));
        $this->assertDatabaseHas('noticias', ['id_noticia' => $noticia->id_noticia, 'is_active' => 0]);
        $this->assertDatabaseHas('bitacora_auditoria', ['tabla_afectada' => 'noticias', 'accion' => 'BAJA']);

        $this->post(route('admin.portal.documentos.guardar'), [
            'titulo' => 'Documento de prueba automatizada',
            'categoria' => 'Formatos',
            'archivo_ruta' => 'documentos/formatos/prueba.pdf',
            'fecha_subida' => '2026-11-20 11:00:00',
            'is_active' => 1,
        ])->assertRedirect(route('admin.portal.documentos'));
        $this->assertDatabaseHas('documentos', ['titulo' => 'Documento de prueba automatizada']);
        $this->assertDatabaseHas('bitacora_auditoria', ['tabla_afectada' => 'documentos', 'accion' => 'ALTA']);

        $this->post(route('admin.portal.reportes.guardar'), [
            'titulo' => 'Reporte financiero',
            'mes' => 11,
            'anio' => 2026,
            'archivo_pdf_ruta' => 'documentos/reportes/reporte-2026-11.pdf',
            'fecha_publicacion' => '2026-11-20 12:00:00',
            'is_active' => 1,
        ])->assertRedirect(route('admin.portal.reportes'));
        $this->assertDatabaseHas('reportes_financieros', ['mes' => 11, 'anio' => 2026]);
        $this->assertDatabaseHas('bitacora_auditoria', ['tabla_afectada' => 'reportes_financieros', 'accion' => 'ALTA']);

        $this->post(route('admin.portal.galeria.guardar'), [
            'titulo' => 'Imagen de prueba automatizada',
            'descripcion' => 'Descripción de prueba.',
            'imagen_ruta' => 'imagenes/galeria/prueba.jpg',
            'fecha_subida' => '2026-11-20 13:00:00',
            'is_active' => 1,
        ])->assertRedirect(route('admin.portal.galeria'));
        $this->assertDatabaseHas('imagenes_galeria', ['titulo' => 'Imagen de prueba automatizada']);
        $this->assertDatabaseHas('bitacora_auditoria', ['tabla_afectada' => 'imagenes_galeria', 'accion' => 'ALTA']);
    }

    public function test_validaciones_de_formularios_de_administrador(): void
    {
        $this->actingAs($this->administrador());

        $this->post(route('admin.tutores.store'), [
            'nombre' => 'Sin datos',
        ])->assertSessionHasErrors(['correo', 'ine_ruta', 'username', 'password']);

        $this->post(route('admin.alumnos.store'), [
            'nombre' => 'Sin datos',
        ])->assertSessionHasErrors(['id_grupo', 'curp', 'username', 'password']);

        $this->post(route('admin.portal.reportes.guardar'), [
            'titulo' => 'Reporte duplicado',
            'mes' => 9,
            'anio' => 2026,
            'archivo_pdf_ruta' => 'documentos/reportes/duplicado.pdf',
            'fecha_publicacion' => '2026-11-20 12:00:00',
            'is_active' => 1,
        ])->assertSessionHasErrors(['mes']);
    }

    public function test_panel_de_administrador_ordena_los_modulos_segun_el_flujo_academico(): void
    {
        $respuesta = $this->actingAs($this->administrador())
            ->get(route('panel'))
            ->assertOk()
            ->getContent();

        $orden = [
            'Alumnos',
            'Docentes',
            'Tutores',
            'Catálogos',
            'Salones',
            'Grupos y clases',
            'Horarios',
            'Portal web',
            'Gestor de archivos',
        ];

        $posicionAnterior = -1;
        foreach ($orden as $titulo) {
            $posicion = mb_strpos($respuesta, '>'.$titulo.'</h3>');
            $this->assertNotFalse($posicion, "No se encontró el módulo {$titulo}.");
            $this->assertGreaterThan($posicionAnterior, $posicion, "El módulo {$titulo} está fuera de orden.");
            $posicionAnterior = $posicion;
        }
    }

    public function test_administrador_puede_crear_docente_con_perfil_academico(): void
    {
        $this->actingAs($this->administrador())
            ->post(route('admin.docentes.store'), [
                'nombre' => 'Rosa',
                'apellido_paterno' => 'Vázquez',
                'apellido_materno' => 'López',
                'curp' => 'VAZL910402MDFRRS05',
                'rfc' => 'VAZL910402GH5',
                'correo_institucional' => 'rosa.vazquez@eba-amr.edu.mx',
                'horas_semanales' => 24,
                'licenciatura' => 'Licenciatura en Física',
                'especialidad' => '',
                'maestria' => 'Maestría en Enseñanza de las Ciencias',
                'doctorado' => '',
                'numero_personal_sep' => 'sep-2026-0099',
                'contacto' => '228 123 6789',
                'nombramiento' => 'Interino',
                'categoria' => 'Profesor de asignatura',
                'plaza' => 'PLZ-FIS-009',
                'sindicato' => 'SNTE',
                'titulo' => 'Título de Licenciada en Física',
                'cedula_profesional' => 'ced-9988776',
                'id_capacitacion' => '',
                'id_area_propedeutica' => 1,
                'username' => 'docente.rosa',
                'password' => 'Prueba123!',
                'password_confirmation' => 'Prueba123!',
            ])
            ->assertRedirect(route('admin.docentes.index'));

        $usuario = User::where('username', 'docente.rosa')->firstOrFail();

        $this->assertTrue(Hash::check('Prueba123!', $usuario->password_hash));
        $this->assertEquals(3, $usuario->id_rol);

        $this->assertDatabaseHas('docentes', [
            'id_usuario' => $usuario->id_usuario,
            'curp' => 'VAZL910402MDFRRS05',
            'rfc' => 'VAZL910402GH5',
            'matricula_rfc' => 'VAZL910402GH5',
            'horas_semanales' => 24,
            'licenciatura' => 'Licenciatura en Física',
            'especialidad' => null,
            'maestria' => 'Maestría en Enseñanza de las Ciencias',
            'doctorado' => null,
            'numero_personal_sep' => 'SEP-2026-0099',
            'contacto' => '228 123 6789',
            'nombramiento' => 'Interino',
            'categoria' => 'Profesor de asignatura',
            'plaza' => 'PLZ-FIS-009',
            'sindicato' => 'SNTE',
            'titulo' => 'Título de Licenciada en Física',
            'cedula_profesional' => 'CED-9988776',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'docentes',
            'accion' => 'ALTA',
        ]);
    }

    public function test_ficha_institucional_del_docente_se_captura_y_se_muestra(): void
    {
        $this->actingAs($this->administrador());

        $this->get(route('admin.docentes.create'))
            ->assertOk()
            ->assertSee('Número de personal (SEP)')
            ->assertSee('Contacto')
            ->assertSee('Nombramiento')
            ->assertSee('Categoría')
            ->assertSee('Plaza')
            ->assertSee('Sindicato')
            ->assertSee('Título')
            ->assertSee('Cédula profesional');

        $docente = Docente::firstOrFail();
        $docente->update([
            'numero_personal_sep' => 'SEP-2026-0100',
            'contacto' => '228 123 0100',
            'nombramiento' => 'Definitivo',
            'categoria' => 'Profesor de tiempo completo',
            'plaza' => 'PLZ-010',
            'sindicato' => 'SNTE',
            'titulo' => 'Título de Maestro en Educación',
            'cedula_profesional' => 'CED-0100200',
        ]);

        $this->get(route('admin.docentes.index', ['q' => 'SEP-2026-0100']))
            ->assertOk()
            ->assertSee('SEP-2026-0100')
            ->assertSee('228 123 0100')
            ->assertSee('Definitivo')
            ->assertSee('Profesor de tiempo completo')
            ->assertSee('PLZ-010')
            ->assertSee('SNTE')
            ->assertSee('Título de Maestro en Educación')
            ->assertSee('CED-0100200');

        $this->get(route('admin.docentes.edit', $docente))
            ->assertOk()
            ->assertSee('SEP-2026-0100')
            ->assertSee('CED-0100200');
    }

    public function test_docente_requiere_curp_rfc_licenciatura_y_tope_de_horas(): void
    {
        $this->actingAs($this->administrador())
            ->post(route('admin.docentes.store'), [
                'nombre' => 'Sin datos',
            ])->assertSessionHasErrors([
                'curp',
                'rfc',
                'horas_semanales',
                'licenciatura',
                'username',
                'password',
            ]);

        $this->post(route('admin.docentes.store'), [
            'nombre' => 'Tope',
            'apellido_paterno' => 'Excedido',
            'curp' => 'TOPE900101HDFRRS06',
            'rfc' => 'TOPE900101IJ6',
            'correo_institucional' => 'tope.excedido@eba-amr.edu.mx',
            'horas_semanales' => 40,
            'licenciatura' => 'Licenciatura en Química',
            'username' => 'docente.tope',
            'password' => 'Prueba123!',
            'password_confirmation' => 'Prueba123!',
        ])->assertSessionHasErrors('horas_semanales');
    }

    public function test_admin_puede_dar_de_baja_a_un_docente_sin_eliminar_su_perfil(): void
    {
        $this->actingAs($this->administrador());
        $docente = Docente::where('rfc', 'TOMV850314AB1')->firstOrFail();

        $this->delete(route('admin.docentes.destroy', $docente))
            ->assertRedirect(route('admin.docentes.index'));

        $this->assertDatabaseHas('docentes', [
            'id_docente' => $docente->id_docente,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $docente->id_usuario,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'docentes',
            'accion' => 'BAJA',
            'id_registro_afectado' => $docente->id_docente,
        ]);
    }
}
