<?php

namespace Api\Auth\Domain\Entities;

use Api\Auth\Domain\ValueObjects\Email;
use Api\Auth\Domain\ValueObjects\Password;
use Api\Auth\Domain\ValueObjects\Role;
use DateTime;

/**
 * User Domain Entity
 * 
 * Representa un usuario en el dominio del negocio.
 * No tiene dependencias de frameworks, solo lógica de negocio pura.
 */
class User
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private Password $password,
        private Role $role = Role::USER,
        private ?DateTime $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    // Domain logic - Tell Don't Ask
    
    /**
     * Cambia la contraseña del usuario
     */
    public function changePassword(Password $newPassword): void
    {
        $this->password = $newPassword;
    }

    /**
     * Verifica si la contraseña proporcionada es correcta
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password->verify($plainPassword);
    }

    /**
     * Verifica si este usuario tiene el email especificado
     */
    public function hasEmail(Email $email): bool
    {
        return $this->email->equals($email);
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    /**
     * Verifica si el usuario es usuario normal
     */
    public function isUser(): bool
    {
        return $this->role->isUser();
    }

    /**
     * Cambia el rol del usuario
     */
    public function changeRole(Role $newRole): void
    {
        $this->role = $newRole;
    }

    /**
     * Asegura que el usuario tenga permisos de administrador
     * 
     * @throws \DomainException Si el usuario no es administrador
     */
    public function ensureIsAdmin(): void
    {
        if (!$this->isAdmin()) {
            throw new \DomainException('User does not have admin privileges');
        }
    }
}
