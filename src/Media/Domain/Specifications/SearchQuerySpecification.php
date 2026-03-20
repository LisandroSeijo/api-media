<?php

declare(strict_types=1);

namespace Api\Media\Domain\Specifications;

/**
 * Especificación para validar query de búsqueda
 */
final readonly class SearchQuerySpecification
{
    private const MAX_LENGTH = 50;
    private const MIN_LENGTH = 1;

    /**
     * Verifica si el query satisface la especificación
     */
    public function isSatisfiedBy(string $query): bool
    {
        $length = mb_strlen($query);
        
        return $length >= self::MIN_LENGTH 
            && $length <= self::MAX_LENGTH
            && !empty(trim($query));
    }

    /**
     * Obtiene el mensaje de error si no satisface
     */
    public function getErrorMessage(string $query): string
    {
        $length = mb_strlen($query);
        
        if (empty(trim($query))) {
            return 'Search query cannot be empty';
        }
        
        if ($length < self::MIN_LENGTH) {
            return sprintf('Search query must be at least %d character', self::MIN_LENGTH);
        }
        
        if ($length > self::MAX_LENGTH) {
            return sprintf('Search query cannot exceed %d characters', self::MAX_LENGTH);
        }
        
        return 'Invalid search query';
    }

    /**
     * Obtiene la longitud máxima permitida
     */
    public function getMaxLength(): int
    {
        return self::MAX_LENGTH;
    }

    /**
     * Obtiene la longitud mínima permitida
     */
    public function getMinLength(): int
    {
        return self::MIN_LENGTH;
    }
}
