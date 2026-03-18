<?php

namespace Src\Auth\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Email Value Object
 * 
 * Representa un email válido en el dominio.
 * Es inmutable y se valida en el constructor.
 */
readonly class Email
{
    /**
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$value}");
        }
    }

    /**
     * Obtiene el valor del email
     * 
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compara si dos emails son iguales
     * 
     * @param Email $other
     * @return bool
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Representación como string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
