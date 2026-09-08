<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\Content;
use App\Models\Evidence;
use App\Models\User;

class EvidencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Evidence $evidence): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isGuia()) {
            return $evidence->content->teacher_id === $user->id
                || $evidence->child->environment?->teacher_id === $user->id;
        }

        if ($user->isFamilia()) {
            return $evidence->family_id === $user->family_id;
        }

        return false;
    }

    /**
     * Se llama como Gate::authorize('create', [Evidence::class, $content, $child]).
     * Nunca alcanza con "sos familia": el niño tiene que ser SUYO, la
     * experiencia tiene que pedir evidencia y estar publicada.
     */
    public function create(User $user, Content $content, Child $child): bool
    {
        if (! $user->isFamilia()) {
            return false;
        }

        if ($child->family_id !== $user->family_id) {
            return false;
        }

        if (! $content->requires_evidence) {
            return false;
        }

        return $content->status === Content::STATUS_PUBLISHED;
    }

    public function update(User $user, Evidence $evidence): bool
    {
        // La familia puede editar su envío mientras la guía no la vio.
        return $user->isFamilia()
            && $evidence->family_id === $user->family_id
            && $evidence->status === Evidence::STATUS_SUBMITTED;
    }

    /**
     * La guía responde (deja observación/retroalimentación) a una
     * evidencia de SU ambiente, no de cualquiera.
     */
    public function respond(User $user, Evidence $evidence): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isGuia()
            && ($evidence->content->teacher_id === $user->id
                || $evidence->child->environment?->teacher_id === $user->id);
    }

    public function delete(User $user, Evidence $evidence): bool
    {
        return $user->isStaff()
            || ($user->isFamilia() && $evidence->family_id === $user->family_id && $evidence->status === Evidence::STATUS_SUBMITTED);
    }

    public function restore(User $user, Evidence $evidence): bool
    {
        return $user->isStaff();
    }

    public function forceDelete(User $user, Evidence $evidence): bool
    {
        return $user->isAdministrador();
    }
}
