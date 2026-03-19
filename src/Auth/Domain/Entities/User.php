<?php

namespace Api\Auth\Domain\Entities;

use Api\Auth\Domain\ValueObjects\Email;
use Api\Auth\Domain\ValueObjects\Password;
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
}
