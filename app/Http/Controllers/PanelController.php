<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    private const ICONOS = [
        'SuperUsuario' => 'fa-shield-halved',
        'Administrador' => 'fa-user-gear',
        'Docente' => 'fa-chalkboard-user',
        'Alumno' => 'fa-graduation-cap',
        'Tutor' => 'fa-users',
    ];

    private const COLORES = [
        'SuperUsuario' => '#ff5c8a',
        'Administrador' => '#0066ff',
        'Docente' => '#ff9f43',
        'Alumno' => '#a29bfe',
        'Tutor' => '#ffca3a',
    ];

    private const MODULOS = [
        'SuperUsuario' => [
            ['icono' => 'fa-user-shield', 'titulo' => 'Control de personal', 'descripcion' => 'Alta, baja y suspensión de cuentas de administradores y docentes.', 'ruta' => 'admin.personal.index'],
            ['icono' => 'fa-file-circle-check', 'titulo' => 'Bitácora de auditoría', 'descripcion' => 'Consulta, filtrado y exportación de la bitácora inalterable.', 'ruta' => 'admin.bitacora.index'],
            ['icono' => 'fa-key', 'titulo' => 'Seguridad de accesos', 'descripcion' => 'Revisión de intentos fallidos y bloqueos temporales.'],
        ],
        'Administrador' => [
            ['icono' => 'fa-user-graduate', 'titulo' => 'Alumnos', 'descripcion' => 'Expedientes, matrículas y estatus escolar.'],
            ['icono' => 'fa-users', 'titulo' => 'Tutores', 'descripcion' => 'Vinculación de tutores con estudiantes.'],
            ['icono' => 'fa-layer-group', 'titulo' => 'Grupos y clases', 'descripcion' => 'Confección de grupos y cargas de materias.'],
            ['icono' => 'fa-calendar-day', 'titulo' => 'Horarios', 'descripcion' => 'Asignación de horarios sin solapamientos.'],
            ['icono' => 'fa-book', 'titulo' => 'Catálogos', 'descripcion' => 'Planes de estudio, materias y semestres.'],
            ['icono' => 'fa-newspaper', 'titulo' => 'Portal web', 'descripcion' => 'Noticias, documentos, reportes y galería.'],
        ],
        'Docente' => [
            ['icono' => 'fa-list-check', 'titulo' => 'Criterios de evaluación', 'descripcion' => 'Rubros por parcial con ponderación al 100 %.'],
            ['icono' => 'fa-star-half-stroke', 'titulo' => 'Calificaciones', 'descripcion' => 'Captura de calificaciones de 0.00 a 10.00.'],
            ['icono' => 'fa-clock', 'titulo' => 'Mi horario', 'descripcion' => 'Consulta de la carga horaria asignada.'],
        ],
        'Alumno' => [
            ['icono' => 'fa-address-card', 'titulo' => 'Expediente académico', 'descripcion' => 'Datos personales, plan de estudio y carga horaria.'],
            ['icono' => 'fa-list-check', 'titulo' => 'Criterios', 'descripcion' => 'Rubros de evaluación de cada materia.'],
            ['icono' => 'fa-star-half-stroke', 'titulo' => 'Calificaciones', 'descripcion' => 'Desglose de calificaciones por parcial.'],
            ['icono' => 'fa-file-pdf', 'titulo' => 'Boletas', 'descripcion' => 'Descarga de boletas oficiales del semestre.'],
        ],
        'Tutor' => [
            ['icono' => 'fa-chart-line', 'titulo' => 'Seguimiento académico', 'descripcion' => 'Desempeño y estatus de los estudiantes bajo tu tutela.'],
            ['icono' => 'fa-file-pdf', 'titulo' => 'Boletas', 'descripcion' => 'Consulta y descarga de boletas de evaluación.'],
        ],
    ];

    public function index()
    {
        $usuario = Auth::user()->load('rol');
        $rol = $usuario->rol?->nombre ?? 'Invitado';

        return view('panel.index', [
            'usuario' => $usuario,
            'nombre' => $usuario->nombreCompleto(),
            'rol' => $rol,
            'iconoRol' => self::ICONOS[$rol] ?? 'fa-user',
            'colorRol' => self::COLORES[$rol] ?? '#0066ff',
            'modulos' => self::MODULOS[$rol] ?? [],
        ]);
    }
}
