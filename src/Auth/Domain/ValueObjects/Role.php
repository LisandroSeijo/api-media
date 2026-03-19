<?php

declare(strict_types=1);

namespace Api\Auth\Domain\ValueObjects;

/**
 * Role Enum
 * 
 * Define los roles disponibles en el sistema.
 * Usa enum nativo de PHP 8.1+ para type safety.
 */
enum Role: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Obtiene el rol por defecto para nuevos usuarios
     */
    public static function default(): self
    {
        return self::USER;
    }

    /**
     * Verifica si el rol es administrador
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Verifica si el rol es usuario normal
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }

    /**
     * Obtiene el nombre legible del rol
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::USER => 'Usuario',
        };
    }

    /**
     * Crea una instancia desde un string
     * 
     * @throws \ValueError Si el valor no es válido
     */
    public static function fromString(string $value): self
    {
        return self::from(strtolower($value));
    }

    /**
     * Intenta crear una instancia desde un string, retorna null si es inválido
     */
    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }
}
