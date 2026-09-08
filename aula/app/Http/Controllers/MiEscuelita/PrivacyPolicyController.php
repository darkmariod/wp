<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Política de privacidad pública (Bloque 4). Contrato de manejo de datos
 * de menores alineado con la ley ecuatoriana de protección de datos
 * personales (Ley Orgánica de Protección de Datos Personales, LOPDP).
 */
class PrivacyPolicyController extends Controller
{
    public function show(): View
    {
        return view('mi-escuelita.privacidad');
    }
}
