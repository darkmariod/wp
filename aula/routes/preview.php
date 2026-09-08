<?php

use App\Http\Controllers\MiEscuelita\ContentPreviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vista previa firmada — Fase 22
|--------------------------------------------------------------------------
| Enlace temporal que abre la vista REAL de la familia en una pestaña
| nueva. La genera el staff desde ContentResource; el middleware 'signed'
| valida la firma y su expiración, y 'auth' exige estar autenticado.
*/
Route::get('/mi-escuelita/vista-previa/{content}', [ContentPreviewController::class, 'show'])
    ->middleware(['auth', 'signed'])
    ->name('mi-escuelita.preview.show');
