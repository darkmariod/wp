<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active && ! $user->isFamilia();
    }

    /**
     * Familia: solo contenido publicado y del ambiente de alguno de sus
     * hijos (o sin ambiente = aviso general del colegio). Nunca borradores
     * ni contenido de otro ambiente, sin importar qué id pida el frontend.
     */
    public function view(User $user, Content $content): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isGuia()) {
            return $content->teacher_id === $user->id
                || $content->environment_id === null
                || $user->environments()->where('id', $content->environment_id)->exists();
        }

        if ($user->isFamilia()) {
            if ($content->status !== Content::STATUS_PUBLISHED) {
                return false;
            }

            if ($content->published_at !== null && $content->published_at->isFuture()) {
                return false;
            }

            if ($content->environment_id === null) {
                return true;
            }

            return $user->family
                ?->children()
                ->where('environment_id', $content->environment_id)
                ->exists() ?? false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isGuia();
    }

    public function update(User $user, Content $content): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isGuia() && $content->teacher_id === $user->id;
    }

    public function delete(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    public function restore(User $user, Content $content): bool
    {
        return $user->isStaff();
    }

    public function forceDelete(User $user, Content $content): bool
    {
        return $user->isAdministrador();
    }
}
