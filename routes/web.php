<?php

use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PanelController;
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
    });
