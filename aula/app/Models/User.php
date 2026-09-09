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

    /** Roles válidos de la app (coinciden con los roles de Spatie/Shield). */
    public const ROLES = [
        self::ROLE_ADMINISTRADOR,
        self::ROLE_COORDINACION,
        self::ROLE_GUIA,
        self::ROLE_FAMILIA,
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
     * Filament: acá se cierra de verdad la puerta del panel para las
     * familias, no alcanza con nunca mandarles el link. Sin esto,
     * CUALQUIER usuario autenticado puede entrar a /admin escribiendo
     * la URL a mano.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && ! $this->isFamilia();
    }
}
