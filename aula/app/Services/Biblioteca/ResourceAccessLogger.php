<?php

namespace App\Services\Biblioteca;

use App\Models\LibraryResource;
use App\Models\ResourceAccessLog;
use App\Models\User;

/**
 * Anota las consultas y descargas de los recursos. Una consulta repetida
 * dentro de la ventana configurada no se vuelve a anotar (recargar la página
 * no debe inflar las estadísticas); una descarga se anota siempre.
 */
class ResourceAccessLogger
{
    /**
     * @return ResourceAccessLog|null null si es un visitante o si ya se anotó dentro de la ventana
     */
    public function viewed(LibraryResource $resource, ?User $user): ?ResourceAccessLog
    {
        if ($user === null) {
            return null;
        }

        $window = max(0, (int) config('biblioteca.view_log_window_minutes'));

        if ($window > 0 && $this->viewedRecently($resource, $user, $window)) {
            return null;
        }

        return $this->record($resource, $user, ResourceAccessLog::ACTION_VIEWED);
    }

    public function downloaded(LibraryResource $resource, ?User $user): ResourceAccessLog
    {
        return $this->record($resource, $user, ResourceAccessLog::ACTION_DOWNLOADED);
    }

    private function viewedRecently(LibraryResource $resource, User $user, int $minutes): bool
    {
        return ResourceAccessLog::query()
            ->where('resource_id', $resource->id)
            ->where('user_id', $user->id)
            ->where('action', ResourceAccessLog::ACTION_VIEWED)
            ->where('created_at', '>', now()->subMinutes($minutes))
            ->exists();
    }

    private function record(LibraryResource $resource, ?User $user, string $action): ResourceAccessLog
    {
        return ResourceAccessLog::create([
            'resource_id' => $resource->id,
            'user_id' => $user?->id,
            'action' => $action,
        ]);
    }
}
