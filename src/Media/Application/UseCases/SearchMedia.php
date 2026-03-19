<?php

declare(strict_types=1);

namespace Api\Media\Application\UseCases;

use Api\Media\Application\DTOs\SearchMediaDTO;
use Api\Media\Domain\Repositories\MediaRepositoryInterface;
use Api\Media\Domain\ValueObjects\SearchQuery;
use Api\Media\Domain\ValueObjects\Limit;
use Api\Media\Domain\ValueObjects\Offset;

/**
 * Caso de uso para buscar media
 */
class SearchMedia
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository
    ) {}

    /**
     * Ejecuta la búsqueda de media
     *
     * @param SearchMediaDTO $dto
     * @return array{data: array, pagination: array, meta: array}
     * @throws \InvalidArgumentException Si los parámetros son inválidos
     */
    public function execute(SearchMediaDTO $dto): array
    {
        // Crear Value Objects con validación
        $query = new SearchQuery($dto->query);
        $limit = $dto->limit !== null ? new Limit($dto->limit) : Limit::default();
        $offset = $dto->offset !== null ? new Offset($dto->offset) : Offset::default();

        // Buscar media a través del repositorio
        $result = $this->mediaRepository->search($query, $limit, $offset);

        // Transformar las entidades a arrays para la respuesta
        $data = array_map(
            fn($mediaItem) => $mediaItem->toArray(),
            $result['data']
        );

        return [
            'data' => $data,
            'pagination' => $result['pagination'],
            'meta' => $result['meta'],
        ];
    }
}
