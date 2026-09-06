<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';

    /**
     * Roles that can be managed via the admin panel (Super Admin is never here).
     *
     * @var array<int, string>
     */
    public const MANAGEABLE_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
    ];

    /**
     * Roles that an Admin user can manage (Manager only).
     *
     * @var array<int, string>
     */
    public const ADMIN_MANAGEABLE_ROLES = [
        self::ROLE_MANAGER,
    ];

    /**
     * Get the roles the given user is allowed to manage.
     */
    public static function rolesManageableBy(User $actor): array
    {
        if ($actor->isSuperAdmin()) {
            return self::MANAGEABLE_ROLES; // admin + manager
        }

        return self::ADMIN_MANAGEABLE_ROLES; // manager only
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_image',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * The canonical identity of the protected Super Admin account.
     */
    public static function superAdminEmail(): string
    {
        return (string) config('auth.super_admin.email');
    }

    /**
     * True if this account is the hardcoded Super Admin. The email is the
     * canonical check so a malicious role edit can never escape the guard.
     */
    public function isSuperAdmin(): bool
    {
        return $this->email === self::superAdminEmail()
            || $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * True if the account is allowed into the admin panel.
     */
    public function isStaff(): bool
    {
        return $this->is_active
            && in_array($this->role, [
                self::ROLE_SUPER_ADMIN,
                self::ROLE_ADMIN,
                self::ROLE_MANAGER,
            ]);
    }

    protected static function booted(): void
    {
        // Never allow the Super Admin to be deleted through any in-app path.
        static::deleting(function (User $user) {
            if (self::rowIsSuperAdmin($user)) {
                \App\Services\SecurityLogService::log(
                    'super_admin_guard.delete_blocked',
                    $user->getOriginal('email') ?: $user->email,
                    auth()->id()
                );
                throw new \App\Exceptions\SuperAdminGuardException(
                    'The Super Admin account cannot be deleted.'
                );
            }
        });

        // Never allow the Super Admin's email, role, or activation state to be
        // changed through any in-app path. Own-password changes are unaffected.
        static::updating(function (User $user) {
            if (! self::rowIsSuperAdmin($user)) {
                return;
            }

            $blocked = $user->isDirty('email')
                || $user->isDirty('role')
                || ($user->isDirty('is_active') && ! $user->is_active);

            if ($blocked) {
                \App\Services\SecurityLogService::log(
                    'super_admin_guard.update_blocked',
                    $user->getOriginal('email') ?: $user->email,
                    auth()->id(),
                    ['dirty' => array_keys($user->getDirty())]
                );
                throw new \App\Exceptions\SuperAdminGuardException(
                    'The Super Admin account cannot have its email, role, or '
                    .'activation state changed.'
                );
            }
        });
    }

    /**
     * Guard check that relies on the persisted (original) row values so a
     * combined email+role change can never escape the protection.
     */
    protected static function rowIsSuperAdmin(User $user): bool
    {
        $originalEmail = $user->getOriginal('email');
        $originalRole = $user->getOriginal('role');

        return $originalEmail === self::superAdminEmail()
            || $originalRole === self::ROLE_SUPER_ADMIN;
    }
}
