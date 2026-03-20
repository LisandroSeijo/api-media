<?php

namespace Api\Auth\Infrastructure\Persistence\Eloquent\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * User Model (Eloquent)
 * 
 * Modelo de Eloquent para la persistencia de usuarios.
 * Mantiene la funcionalidad de Laravel Passport para OAuth2.
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class UserModel extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nombre de la tabla
     * 
     * @var string
     */
    protected $table = 'users';

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
            'role' => \Api\Auth\Domain\ValueObjects\Role::class,
        ];
    }

    public function isAdmin(): bool
    {
        // Manejar tanto string como enum
        if ($this->role instanceof \Api\Auth\Domain\ValueObjects\Role) {
            return $this->role === \Api\Auth\Domain\ValueObjects\Role::ADMIN;
        }
        return $this->role === 'admin';
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
