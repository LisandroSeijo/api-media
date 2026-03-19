<?php

namespace Api\Auth\Domain\ValueObjects;

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
     * @throws InvalidArgumentException
     */
    public function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$value}");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compara si dos emails son iguales
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
