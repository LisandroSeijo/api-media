<?php

declare(strict_types=1);

namespace Api\Media\Domain\Specifications;

/**
 * Especificación para validar el offset de paginación
 */
final readonly class OffsetSpecification
{
    private const MIN_OFFSET = 0;
    private const MAX_OFFSET = 4999;
    private const DEFAULT_OFFSET = 0;

    /**
     * Verifica si el offset satisface la especificación
     */
    public function isSatisfiedBy(int $offset): bool
    {
        return $offset >= self::MIN_OFFSET && $offset <= self::MAX_OFFSET;
    }

    /**
     * Obtiene el mensaje de error si no satisface
     */
    public function getErrorMessage(int $offset): string
    {
        if ($offset < self::MIN_OFFSET) {
            return sprintf('Offset must be at least %d', self::MIN_OFFSET);
        }
        
        if ($offset > self::MAX_OFFSET) {
            return sprintf('Offset cannot exceed %d', self::MAX_OFFSET);
        }
        
        return 'Invalid offset';
    }

    /**
     * Obtiene el offset por defecto
     */
    public function getDefaultOffset(): int
    {
        return self::DEFAULT_OFFSET;
    }

    /**
     * Obtiene el offset mínimo
     */
    public function getMinOffset(): int
    {
        return self::MIN_OFFSET;
    }

    /**
     * Obtiene el offset máximo
     */
    public function getMaxOffset(): int
    {
        return self::MAX_OFFSET;
    }
}
