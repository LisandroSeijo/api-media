<?php

declare(strict_types=1);

namespace Api\Media\Domain\Specifications;

/**
 * Especificación para validar el límite de resultados
 */
final readonly class LimitSpecification
{
    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 50;
    private const DEFAULT_LIMIT = 25;

    /**
     * Verifica si el límite satisface la especificación
     */
    public function isSatisfiedBy(int $limit): bool
    {
        return $limit >= self::MIN_LIMIT && $limit <= self::MAX_LIMIT;
    }

    /**
     * Obtiene el mensaje de error si no satisface
     */
    public function getErrorMessage(int $limit): string
    {
        if ($limit < self::MIN_LIMIT) {
            return sprintf('Limit must be at least %d', self::MIN_LIMIT);
        }
        
        if ($limit > self::MAX_LIMIT) {
            return sprintf('Limit cannot exceed %d', self::MAX_LIMIT);
        }
        
        return 'Invalid limit';
    }

    /**
     * Obtiene el límite por defecto
     */
    public function getDefaultLimit(): int
    {
        return self::DEFAULT_LIMIT;
    }

    /**
     * Obtiene el límite mínimo
     */
    public function getMinLimit(): int
    {
        return self::MIN_LIMIT;
    }

    /**
     * Obtiene el límite máximo
     */
    public function getMaxLimit(): int
    {
        return self::MAX_LIMIT;
    }
}
