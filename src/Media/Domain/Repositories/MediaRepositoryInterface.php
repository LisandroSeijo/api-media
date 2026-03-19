<?php

declare(strict_types=1);

namespace Api\Media\Domain\Repositories;

use Api\Media\Domain\Entities\MediaItem;
use Api\Media\Domain\ValueObjects\SearchQuery;
use Api\Media\Domain\ValueObjects\Limit;
use Api\Media\Domain\ValueObjects\Offset;

/**
 * Interface para el repositorio de Media
 * Define el contrato para buscar media desde proveedores externos (GIPHY, etc)
 */
interface MediaRepositoryInterface
{
    /**
     * Busca media por una consulta
     *
     * @param SearchQuery $query Término o frase de búsqueda
     * @param Limit $limit Número máximo de resultados
     * @param Offset $offset Posición inicial de resultados
     * @return array{data: MediaItem[], pagination: array, meta: array}
     */
    public function search(
        SearchQuery $query,
        Limit $limit,
        Offset $offset
    ): array;

    /**
     * Obtiene un elemento de media por su ID
     *
     * @param string $id ID del elemento
     * @return MediaItem|null
     */
    public function findById(string $id): ?MediaItem;
}
