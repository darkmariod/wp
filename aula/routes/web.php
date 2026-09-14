<?php

use App\Http\Controllers\MiEscuelita\AsistenciaController;
use App\Http\Controllers\MiEscuelita\EvidenceController;
use App\Http\Controllers\MiEscuelita\ExperienceController;
use App\Http\Controllers\MiEscuelita\HomeController;
use App\Http\Controllers\MiEscuelita\MediaController;
use App\Http\Controllers\MiEscuelita\NotificationPreferenceController;
use App\Http\Controllers\MiEscuelita\PrivacyPolicyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/privacidad', [PrivacyPolicyController::class, 'show'])
    ->name('privacidad');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Mi Escuelita — Fases 3 a 10 del prompt maestro
|--------------------------------------------------------------------------
| Solo para role=familia (middleware 'familia', ver EnsureUserIsFamilia).
| Un guía o admin autenticado que entre acá también se rechaza: cada rol
| tiene su propia puerta.
*/
Route::middleware(['auth', 'familia'])->prefix('mi-escuelita')->name('mi-escuelita.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/nino/{child}', [HomeController::class, 'switchChild'])->name('nino.cambiar');

    Route::get('/experiencias', [ExperienceController::class, 'index'])->name('experiencias.index');
    Route::get('/experiencias/{content:slug}', [ExperienceController::class, 'show'])->name('experiencias.show');
    Route::post('/experiencias/{content:slug}/evidencias', [EvidenceController::class, 'store'])->middleware('throttle:10,1')->name('evidencias.store');

    Route::get('/mis-experiencias', [ExperienceController::class, 'historial'])->name('historial');

    Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia');

    Route::get('/notificaciones', [NotificationPreferenceController::class, 'edit'])->name('notificaciones.edit');
    Route::patch('/notificaciones', [NotificationPreferenceController::class, 'update'])->name('notificaciones.update');
});

/*
|--------------------------------------------------------------------------
| Storage privado — Fase 10
|--------------------------------------------------------------------------
| Única puerta a un archivo de evidencia/contenido: pasa por la misma
| Policy del modelo dueño, nunca por conocer la ruta en disco.
*/
Route::get('/storage-privado/{medium}', [MediaController::class, 'show'])
    ->middleware('auth')
    ->name('media.show');

require __DIR__.'/preview.php';

require __DIR__.'/auth.php';
