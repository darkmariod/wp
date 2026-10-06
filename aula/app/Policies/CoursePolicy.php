<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/**
 * `Course::scopeVisibleTo` es la misma regla de `view` en SQL: se cambian juntas.
 */
class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canUseBiblioteca();
    }

    /**
     * Personal: todos. Guía: los que dicta. Estudiante: los activos donde
     * está matriculado.
     */
    public function view(User $user, Course $course): bool
    {
        if (! $user->canUseBiblioteca()) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        if ($user->isGuia()) {
            return $user->teachesCourse($course);
        }

        return $user->isEstudiante() && $course->is_active && $user->isEnrolledIn($course);
    }

    public function create(User $user): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    /**
     * Agregar y ordenar recursos dentro del curso: personal o la guía que lo dicta.
     */
    public function manageResources(User $user, Course $course): bool
    {
        if (! $user->canUseBiblioteca()) {
            return false;
        }

        return $user->isStaff() || ($user->isGuia() && $user->teachesCourse($course));
    }
}
