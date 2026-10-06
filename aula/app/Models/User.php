<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'photo_path', 'email', 'password', 'role', 'family_id', 'active', 'notification_preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const ROLE_ADMINISTRADOR = 'administrador';

    public const ROLE_COORDINACION = 'coordinacion';

    public const ROLE_GUIA = 'guia';

    public const ROLE_FAMILIA = 'familia';

    public const ROLE_ESTUDIANTE = 'estudiante';

    /** Roles válidos de la app (coinciden con los roles de Spatie/Shield). */
    public const ROLES = [
        self::ROLE_ADMINISTRADOR,
        self::ROLE_COORDINACION,
        self::ROLE_GUIA,
        self::ROLE_FAMILIA,
        self::ROLE_ESTUDIANTE,
    ];

    /** Roles del lado del personal: los únicos que entran al panel /admin. */
    public const PANEL_ROLES = [
        self::ROLE_ADMINISTRADOR,
        self::ROLE_COORDINACION,
        self::ROLE_GUIA,
    ];

    /** Roles que pueden usar la Biblioteca (la familia queda fuera). */
    public const BIBLIOTECA_ROLES = [
        self::ROLE_ADMINISTRADOR,
        self::ROLE_COORDINACION,
        self::ROLE_GUIA,
        self::ROLE_ESTUDIANTE,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Todas las notificaciones empiezan activas: solo se apagan si el
     * usuario explícitamente las desmarcó (Fase 9).
     */
    public function wantsNotification(string $type): bool
    {
        return (bool) ($this->notification_preferences[$type] ?? true);
    }

    public function isAdministrador(): bool
    {
        return $this->role === self::ROLE_ADMINISTRADOR;
    }

    public function isCoordinacion(): bool
    {
        return $this->role === self::ROLE_COORDINACION;
    }

    public function isGuia(): bool
    {
        return $this->role === self::ROLE_GUIA;
    }

    public function isFamilia(): bool
    {
        return $this->role === self::ROLE_FAMILIA;
    }

    public function isEstudiante(): bool
    {
        return $this->role === self::ROLE_ESTUDIANTE;
    }

    /**
     * Administrador, Coordinación y Guía: todo el que trabaja del lado del
     * colegio. Es una lista blanca a propósito: un rol nuevo no hereda
     * acceso de personal por no ser familia.
     */
    public function isPanelRole(): bool
    {
        return in_array($this->role, self::PANEL_ROLES, true);
    }

    /**
     * Cuenta activa con un rol que puede entrar a la Biblioteca.
     */
    public function canUseBiblioteca(): bool
    {
        return $this->active && in_array($this->role, self::BIBLIOTECA_ROLES, true);
    }

    /**
     * Administrador y Coordinación gestionan la plataforma completa.
     */
    public function isStaff(): bool
    {
        return $this->isAdministrador() || $this->isCoordinacion();
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Ambientes a cargo de esta guía.
     */
    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class, 'teacher_id');
    }

    /**
     * Cursos de la Biblioteca que este usuario dicta.
     */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Cursos de la Biblioteca en los que está matriculado.
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'user_id', 'course_id')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    /**
     * Es el docente asignado a ese curso.
     */
    public function teachesCourse(Course $course): bool
    {
        return $course->teacher_id !== null && (int) $course->teacher_id === (int) $this->id;
    }

    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrolledCourses()->whereKey($course->id)->exists();
    }

    /**
     * Recursos de la Biblioteca que este usuario creó.
     */
    public function libraryResources(): HasMany
    {
        return $this->hasMany(LibraryResource::class, 'created_by');
    }

    /**
     * Filament: acá se cierra de verdad la puerta del panel, no alcanza
     * con nunca mandar el link. Sin esto, CUALQUIER usuario autenticado
     * puede entrar a /admin escribiendo la URL a mano. Lista blanca: solo
     * el personal entra; familia y estudiante nunca.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && $this->isPanelRole();
    }
}
