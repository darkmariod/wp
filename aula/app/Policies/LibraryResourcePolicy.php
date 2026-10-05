<?php

namespace App\Policies;

use App\Enums\ResourceStatus;
use App\Models\LibraryResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reglas de acceso a los recursos de la Biblioteca. Toda habilidad exige
 * antes `canUseBiblioteca()` (cuenta activa con un rol permitido).
 *
 * Importante: `LibraryResource::scopeVisibleTo` expresa la regla de `view`
 * en SQL. Si cambias una, cambia la otra; la prueba de paridad las cruza.
 */
class LibraryResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canUseBiblioteca();
    }

    /**
     * El personal y quien creó el recurso lo ven siempre. El resto, solo si
     * está publicado y es general, de un curso suyo o con permiso explícito:
     * los borradores y archivados ajenos nunca se ven.
     */
    public function view(User $user, LibraryResource $resource): bool
    {
        if (! $user->canUseBiblioteca()) {
            return false;
        }

        if ($user->isStaff() || $resource->isOwnedBy($user)) {
            return true;
        }

        if ($resource->status !== ResourceStatus::Published) {
            return false;
        }

        return $resource->isGeneral()
            || ($user->isEstudiante() && $this->hasCourseAccess($user, $resource))
            || ($user->isGuia() && $this->hasTeachingAccess($user, $resource))
            || $this->hasGrant($user, $resource, 'can_view');
    }

    public function create(User $user): bool
    {
        return $user->canUseBiblioteca() && ($user->isStaff() || $user->isGuia());
    }

    public function update(User $user, LibraryResource $resource): bool
    {
        if (! $user->canUseBiblioteca()) {
            return false;
        }

        return $user->isStaff() || ($user->isGuia() && $resource->isOwnedBy($user));
    }

    public function delete(User $user, LibraryResource $resource): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function archive(User $user, LibraryResource $resource): bool
    {
        return $this->update($user, $resource);
    }

    public function publish(User $user, LibraryResource $resource): bool
    {
        return $this->update($user, $resource);
    }

    public function duplicate(User $user, LibraryResource $resource): bool
    {
        return $this->update($user, $resource);
    }

    /**
     * Descargar presupone poder ver y tener un archivo guardado: un enlace o
     * un video por URL no se descarga nunca.
     */
    public function download(User $user, LibraryResource $resource): bool
    {
        if (! $this->view($user, $resource) || ! $resource->hasDownloadableFile()) {
            return false;
        }

        if ($user->isStaff() || $resource->isOwnedBy($user)) {
            return true;
        }

        return $resource->is_downloadable || $this->hasGrant($user, $resource, 'can_download');
    }

    /**
     * El estudiante está matriculado en algún curso activo del recurso.
     */
    private function hasCourseAccess(User $user, LibraryResource $resource): bool
    {
        return $resource->courses()
            ->where('courses.is_active', true)
            ->whereHas('students', fn (Builder $students) => $students->whereKey($user->id))
            ->exists();
    }

    /**
     * La guía dicta algún curso del recurso.
     */
    private function hasTeachingAccess(User $user, LibraryResource $resource): bool
    {
        return $resource->courses()->where('courses.teacher_id', $user->id)->exists();
    }

    /**
     * Permiso explícito para el usuario o para su rol. Las filas solo
     * conceden: no existen negaciones.
     */
    private function hasGrant(User $user, LibraryResource $resource, string $ability): bool
    {
        return $resource->permissions()
            ->where($ability, true)
            ->where(fn (Builder $grant) => $grant->where('user_id', $user->id)->orWhere('role', $user->role))
            ->exists();
    }
}
