<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    use ResolvesCurrentChild;

    /**
     * La vista monta un componente Livewire que resuelve el nino de la
     * sesion y muestra el mes por dia mas el resumen anual del ultimo
     * ciclo cerrado (nunca del ciclo en curso).
     */
    public function index(): View
    {
        return view('mi-escuelita.asistencia');
    }
}