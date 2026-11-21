<?php

use App\Http\Controllers\Admin\AlumnosController;
use App\Http\Controllers\Admin\ArchivosController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\PortalController;
use App\Http\Controllers\Admin\SeguridadController;
use App\Http\Controllers\Admin\TutoresController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\Tutor\AlumnosController as TutorAlumnosController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('home');
Route::view('/historia', 'historia')->name('historia');
Route::view('/mision-vision', 'mision-vision')->name('mision.vision');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:SuperUsuario,Administrador,Docente,Alumno,Tutor'])
    ->group(function () {
        Route::get('/panel', [PanelController::class, 'index'])->name('panel');
    });

Route::middleware(['auth', 'role:SuperUsuario'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
        Route::get('/personal/crear', [PersonalController::class, 'create'])->name('personal.create');
        Route::post('/personal', [PersonalController::class, 'store'])->name('personal.store');
        Route::get('/personal/{usuario}/editar', [PersonalController::class, 'edit'])->name('personal.edit');
        Route::put('/personal/{usuario}', [PersonalController::class, 'update'])->name('personal.update');
        Route::delete('/personal/{usuario}', [PersonalController::class, 'destroy'])->name('personal.destroy');

        Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('/bitacora/exportar', [BitacoraController::class, 'export'])->name('bitacora.export');
        Route::get('/bitacora/{bitacora}', [BitacoraController::class, 'show'])->name('bitacora.show');

        Route::get('/seguridad', [SeguridadController::class, 'index'])->name('seguridad.index');
        Route::get('/seguridad/{usuario}', [SeguridadController::class, 'show'])->name('seguridad.show');
        Route::post('/seguridad/{usuario}/desbloquear', [SeguridadController::class, 'desbloquear'])->name('seguridad.desbloquear');
        Route::post('/seguridad/{usuario}/reiniciar-intentos', [SeguridadController::class, 'reiniciarIntentos'])->name('seguridad.reiniciar');
        Route::post('/seguridad/{usuario}/estatus', [SeguridadController::class, 'cambiarEstatus'])->name('seguridad.estatus');
    });

Route::middleware(['auth', 'role:SuperUsuario,Administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/alumnos', [AlumnosController::class, 'index'])->name('alumnos.index');
        Route::get('/alumnos/crear', [AlumnosController::class, 'create'])->name('alumnos.create');
        Route::post('/alumnos', [AlumnosController::class, 'store'])->name('alumnos.store');
        Route::get('/alumnos/{alumno}/editar', [AlumnosController::class, 'edit'])->name('alumnos.edit');
        Route::put('/alumnos/{alumno}', [AlumnosController::class, 'update'])->name('alumnos.update');
        Route::delete('/alumnos/{alumno}', [AlumnosController::class, 'destroy'])->name('alumnos.destroy');

        Route::get('/tutores', [TutoresController::class, 'index'])->name('tutores.index');
        Route::get('/tutores/crear', [TutoresController::class, 'create'])->name('tutores.create');
        Route::post('/tutores', [TutoresController::class, 'store'])->name('tutores.store');
        Route::get('/tutores/{tutor}/alumnos', [TutoresController::class, 'alumnos'])->name('tutores.alumnos');
        Route::post('/tutores/{tutor}/alumnos', [TutoresController::class, 'vincularAlumno'])->name('tutores.alumnos.vincular');
        Route::delete('/tutores/{tutor}/alumnos/{alumno}', [TutoresController::class, 'desasignarAlumno'])->name('tutores.alumnos.desasignar');
        Route::get('/tutores/{tutor}/editar', [TutoresController::class, 'edit'])->name('tutores.edit');
        Route::put('/tutores/{tutor}', [TutoresController::class, 'update'])->name('tutores.update');
        Route::delete('/tutores/{tutor}', [TutoresController::class, 'destroy'])->name('tutores.destroy');

        Route::get('/portal/noticias', [PortalController::class, 'noticias'])->name('portal.noticias');
        Route::get('/portal/noticias/crear', [PortalController::class, 'crearNoticia'])->name('portal.noticias.crear');
        Route::post('/portal/noticias', [PortalController::class, 'guardarNoticia'])->name('portal.noticias.guardar');
        Route::get('/portal/noticias/{noticia}/editar', [PortalController::class, 'editarNoticia'])->name('portal.noticias.editar');
        Route::put('/portal/noticias/{noticia}', [PortalController::class, 'actualizarNoticia'])->name('portal.noticias.actualizar');
        Route::delete('/portal/noticias/{noticia}', [PortalController::class, 'eliminarNoticia'])->name('portal.noticias.eliminar');

        Route::get('/portal/documentos', [PortalController::class, 'documentos'])->name('portal.documentos');
        Route::get('/portal/documentos/crear', [PortalController::class, 'crearDocumento'])->name('portal.documentos.crear');
        Route::post('/portal/documentos', [PortalController::class, 'guardarDocumento'])->name('portal.documentos.guardar');
        Route::get('/portal/documentos/{documento}/editar', [PortalController::class, 'editarDocumento'])->name('portal.documentos.editar');
        Route::put('/portal/documentos/{documento}', [PortalController::class, 'actualizarDocumento'])->name('portal.documentos.actualizar');
        Route::delete('/portal/documentos/{documento}', [PortalController::class, 'eliminarDocumento'])->name('portal.documentos.eliminar');

        Route::get('/portal/reportes', [PortalController::class, 'reportes'])->name('portal.reportes');
        Route::get('/portal/reportes/crear', [PortalController::class, 'crearReporte'])->name('portal.reportes.crear');
        Route::post('/portal/reportes', [PortalController::class, 'guardarReporte'])->name('portal.reportes.guardar');
        Route::get('/portal/reportes/{reporte}/editar', [PortalController::class, 'editarReporte'])->name('portal.reportes.editar');
        Route::put('/portal/reportes/{reporte}', [PortalController::class, 'actualizarReporte'])->name('portal.reportes.actualizar');
        Route::delete('/portal/reportes/{reporte}', [PortalController::class, 'eliminarReporte'])->name('portal.reportes.eliminar');

        Route::get('/portal/galeria', [PortalController::class, 'galeria'])->name('portal.galeria');
        Route::get('/portal/galeria/crear', [PortalController::class, 'crearImagen'])->name('portal.galeria.crear');
        Route::post('/portal/galeria', [PortalController::class, 'guardarImagen'])->name('portal.galeria.guardar');
        Route::get('/portal/galeria/{imagen}/editar', [PortalController::class, 'editarImagen'])->name('portal.galeria.editar');
        Route::put('/portal/galeria/{imagen}', [PortalController::class, 'actualizarImagen'])->name('portal.galeria.actualizar');
        Route::delete('/portal/galeria/{imagen}', [PortalController::class, 'eliminarImagen'])->name('portal.galeria.eliminar');

        Route::get('/archivos', [ArchivosController::class, 'index'])->name('archivos.index');
        Route::get('/archivos/selector', [ArchivosController::class, 'picker'])->name('archivos.picker');
        Route::post('/archivos/carpeta', [ArchivosController::class, 'crearCarpeta'])->name('archivos.carpeta');
        Route::post('/archivos/subir', [ArchivosController::class, 'subir'])->name('archivos.subir');
        Route::get('/archivos/descargar', [ArchivosController::class, 'descargar'])->name('archivos.descargar');
        Route::delete('/archivos', [ArchivosController::class, 'eliminar'])->name('archivos.eliminar');
    });

Route::middleware(['auth', 'role:Tutor'])
    ->prefix('tutor')
    ->name('tutor.')
    ->group(function () {
        Route::get('/alumnos', [TutorAlumnosController::class, 'index'])->name('alumnos.index');
        Route::get('/alumnos/{alumno}', [TutorAlumnosController::class, 'show'])->name('alumnos.show');
    });
