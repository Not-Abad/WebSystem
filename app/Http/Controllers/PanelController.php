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
            ['icono' => 'fa-key', 'titulo' => 'Seguridad de accesos', 'descripcion' => 'Revisión de intentos fallidos y bloqueos temporales.', 'ruta' => 'admin.seguridad.index'],
            ['icono' => 'fa-folder-open', 'titulo' => 'Gestor de archivos', 'descripcion' => 'Carpetas, boletas, certificados y documentos institucionales.', 'ruta' => 'admin.archivos.index'],
        ],
        'Administrador' => [
            ['icono' => 'fa-user-graduate', 'titulo' => 'Alumnos', 'descripcion' => 'Expedientes, grupos y estatus escolar.', 'ruta' => 'admin.alumnos.index'],
            ['icono' => 'fa-chalkboard-user', 'titulo' => 'Docentes', 'descripcion' => 'Perfil profesional, carga horaria y formación académica.', 'ruta' => 'admin.docentes.index'],
            ['icono' => 'fa-users', 'titulo' => 'Tutores', 'descripcion' => 'Vinculación de tutores con estudiantes.', 'ruta' => 'admin.tutores.index'],
            ['icono' => 'fa-book', 'titulo' => 'Catálogos', 'descripcion' => 'Planes de estudio, materias y semestres.', 'ruta' => 'admin.catalogos.index'],
            ['icono' => 'fa-door-open', 'titulo' => 'Salones', 'descripcion' => 'Espacios disponibles para asignar cada clase.', 'ruta' => 'admin.salones.index'],
            ['icono' => 'fa-layer-group', 'titulo' => 'Grupos y clases', 'descripcion' => 'Confección de grupos y cargas de materias.', 'ruta' => 'admin.grupos.index'],
            ['icono' => 'fa-calendar-day', 'titulo' => 'Horarios', 'descripcion' => 'Asignación de horarios sin solapamientos.', 'ruta' => 'admin.horarios.index'],
            ['icono' => 'fa-newspaper', 'titulo' => 'Portal web', 'descripcion' => 'Noticias, documentos, reportes y galería.', 'ruta' => 'admin.portal.noticias'],
            ['icono' => 'fa-folder-open', 'titulo' => 'Gestor de archivos', 'descripcion' => 'Carpetas, boletas, certificados y documentos institucionales.', 'ruta' => 'admin.archivos.index'],
        ],
        'Docente' => [
            ['icono' => 'fa-id-card', 'titulo' => 'Mis datos', 'descripcion' => 'Ficha institucional, correo, CURP e imagen de perfil.', 'ruta' => 'docente.perfil.index'],
            ['icono' => 'fa-clock', 'titulo' => 'Mi horario', 'descripcion' => 'Consulta tus materias, grupos y bloques asignados.', 'ruta' => 'docente.horario.index'],
            ['icono' => 'fa-list-check', 'titulo' => 'Asistencia y evaluación', 'descripcion' => 'Asistencia diaria, criterios por parcial y calificaciones.', 'ruta' => 'docente.horario.index'],
            ['icono' => 'fa-file-excel', 'titulo' => 'Formatos REINCO', 'descripcion' => 'Genera y guarda los formatos 205 y 206 en Excel.', 'ruta' => 'docente.reinco.index'],
            ['icono' => 'fa-rotate-left', 'titulo' => 'Recursamientos (R)', 'descripcion' => 'Exámenes R1 a R4 de tus alumnos, con alumno, materia y semestre identificados.', 'ruta' => 'docente.recursamientos.index'],
        ],
        'Alumno' => [
            ['icono' => 'fa-id-card', 'titulo' => 'Mis datos', 'descripcion' => 'Expediente, grupo, plan de estudio y datos de contacto.', 'ruta' => 'alumno.perfil.index'],
            ['icono' => 'fa-graduation-cap', 'titulo' => 'Kardex escolar', 'descripcion' => 'Retícula por plan, materias cursadas y promedio final.', 'ruta' => 'alumno.kardex.index'],
            ['icono' => 'fa-book-open', 'titulo' => 'Mis materias', 'descripcion' => 'Horario, docentes y grupos de tu semestre.', 'ruta' => 'alumno.materias.index'],
            ['icono' => 'fa-list-check', 'titulo' => 'Criterios y fechas', 'descripcion' => 'Rubros de evaluación y fechas de exámenes publicados.', 'ruta' => 'alumno.materias.index'],
            ['icono' => 'fa-star-half-stroke', 'titulo' => 'Calificaciones y asistencia', 'descripcion' => 'Desglose de porcentajes y asistencia registrada.', 'ruta' => 'alumno.materias.index'],
        ],
        'Tutor' => [
            ['icono' => 'fa-id-card', 'titulo' => 'Mis datos', 'descripcion' => 'Tu información personal y de contacto como tutor.', 'ruta' => 'tutor.perfil.index'],
            ['icono' => 'fa-chart-line', 'titulo' => 'Seguimiento académico', 'descripcion' => 'Desempeño y estatus de los estudiantes bajo tu tutela.', 'ruta' => 'tutor.alumnos.index'],
            ['icono' => 'fa-list-check', 'titulo' => 'Materias y calificaciones', 'descripcion' => 'Docentes, criterios y calificaciones de cada alumno.', 'ruta' => 'tutor.alumnos.index'],
            ['icono' => 'fa-file-archive', 'titulo' => 'Kardex y recursamientos', 'descripcion' => 'Retícula, avance y rondas R de cada estudiante bajo tu tutela.', 'ruta' => 'tutor.alumnos.index'],
        ],
    ];

    public function index()
    {
        $usuario = Auth::user()->load('rol', 'docente');
        $rol = $usuario->rol?->nombre ?? 'Invitado';

        return view('panel.index', [
            'usuario' => $usuario,
            'nombre' => $usuario->nombreCompleto(),
            'rol' => $rol,
            'iconoRol' => self::ICONOS[$rol] ?? 'fa-user',
            'colorRol' => self::COLORES[$rol] ?? '#0066ff',
            'fotoPerfilUrl' => $usuario->docente?->fotoPerfilUrl(),
            'modulos' => self::MODULOS[$rol] ?? [],
        ]);
    }
}
