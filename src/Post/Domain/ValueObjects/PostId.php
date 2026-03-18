<?php

namespace Src\Post\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * PostId Value Object
 * 
 * Representa un ID de post válido en el dominio.
 * Es inmutable y se valida en el constructor.
 */
readonly class PostId
{
    /**
     * @param int $value
     * @throws InvalidArgumentException
     */
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Post ID must be a positive integer");
        }
    }

    /**
     * Obtiene el valor del ID
     * 
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Compara si dos PostIds son iguales
     * 
     * @param PostId $other
     * @return bool
     */
    public function equals(PostId $other): bool
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
        return (string) $this->value;
    }
}
