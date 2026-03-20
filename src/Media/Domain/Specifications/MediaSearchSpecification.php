<?php

declare(strict_types=1);

namespace Api\Media\Domain\Specifications;

/**
 * Composite Specification para validar todos los parámetros de búsqueda de media
 * 
 * Agrupa las validaciones de query, limit y offset
 */
final readonly class MediaSearchSpecification
{
    private SearchQuerySpecification $querySpec;
    private LimitSpecification $limitSpec;
    private OffsetSpecification $offsetSpec;

    public function __construct()
    {
        $this->querySpec = new SearchQuerySpecification();
        $this->limitSpec = new LimitSpecification();
        $this->offsetSpec = new OffsetSpecification();
    }

    /**
     * Verifica si los parámetros de búsqueda satisfacen todas las especificaciones
     *
     * @param string $query Query de búsqueda
     * @param int|null $limit Límite de resultados (null usa default)
     * @param int|null $offset Offset de paginación (null usa default)
     */
    public function isSatisfiedBy(string $query, ?int $limit = null, ?int $offset = null): bool
    {
        // Validar query (obligatorio)
        if (!$this->querySpec->isSatisfiedBy($query)) {
            return false;
        }

        // Validar limit si se proporciona
        if ($limit !== null && !$this->limitSpec->isSatisfiedBy($limit)) {
            return false;
        }

        // Validar offset si se proporciona
        if ($offset !== null && !$this->offsetSpec->isSatisfiedBy($offset)) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene todos los errores de validación
     *
     * @return array<string, string> Array asociativo con campo => mensaje de error
     */
    public function getValidationErrors(string $query, ?int $limit = null, ?int $offset = null): array
    {
        $errors = [];

        // Validar query
        if (!$this->querySpec->isSatisfiedBy($query)) {
            $errors['query'] = $this->querySpec->getErrorMessage($query);
        }

        // Validar limit
        if ($limit !== null && !$this->limitSpec->isSatisfiedBy($limit)) {
            $errors['limit'] = $this->limitSpec->getErrorMessage($limit);
        }

        // Validar offset
        if ($offset !== null && !$this->offsetSpec->isSatisfiedBy($offset)) {
            $errors['offset'] = $this->offsetSpec->getErrorMessage($offset);
        }

        return $errors;
    }

    /**
     * Verifica si hay errores de validación
     */
    public function hasErrors(string $query, ?int $limit = null, ?int $offset = null): bool
    {
        return !empty($this->getValidationErrors($query, $limit, $offset));
    }
}
