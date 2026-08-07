<?php

use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PropertyController::class, 'home'])->name('home');
Route::get('/propiedades', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/propiedades/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/api/sectores/{city}', [PropertyController::class, 'sectoresPorCiudad'])->name('api.sectores.por-ciudad');
