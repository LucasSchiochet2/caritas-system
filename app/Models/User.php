<?php

namespace App\Models;

use App\Enums\ParishRole;
use App\Enums\UserRole;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use CrudTrait, HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'system_role',
        'active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'system_role' => UserRole::class,
            'active' => 'boolean',
        ];
    }

    public function parishes(): BelongsToMany
    {
        return $this->belongsToMany(Parish::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function administeredParishes(): BelongsToMany
    {
        return $this->parishes()->wherePivot('role', ParishRole::Admin->value);
    }

    public function isDioceseAdmin(): bool
    {
        return $this->system_role === UserRole::DioceseAdmin;
    }

    public function isParishAdmin(?Parish $parish = null): bool
    {
        if ($parish === null) {
            return $this->administeredParishes()->exists();
        }

        return $this->canManageParish($parish);
    }

    public function canManageParish(Parish|int $parish): bool
    {
        if ($this->isDioceseAdmin()) {
            return true;
        }

        $parishId = $parish instanceof Parish ? $parish->getKey() : $parish;

        return $this->administeredParishes()->whereKey($parishId)->exists();
    }
}
