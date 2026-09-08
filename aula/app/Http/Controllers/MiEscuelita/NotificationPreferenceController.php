<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        return view('mi-escuelita.notificaciones', [
            'preferencias' => [
                'content_published' => $user->wantsNotification('content_published'),
                'guide_responded' => $user->wantsNotification('guide_responded'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content_published' => ['required', 'boolean'],
            'guide_responded' => ['required', 'boolean'],
        ]);

        Auth::user()->update(['notification_preferences' => $data]);

        return back()->with('success', 'Preferencias guardadas.');
    }
}
