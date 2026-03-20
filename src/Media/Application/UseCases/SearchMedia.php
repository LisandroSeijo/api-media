<?php

declare(strict_types=1);

namespace Api\Media\Application\UseCases;

use Api\Media\Application\DTOs\SearchMediaDTO;
use Api\Media\Domain\Repositories\MediaRepositoryInterface;
use Api\Media\Domain\ValueObjects\SearchQuery;
use Api\Media\Domain\ValueObjects\Limit;
use Api\Media\Domain\ValueObjects\Offset;
use Api\Shared\Domain\Services\CacheServiceInterface;

/**
 * Caso de uso para buscar media con soporte de cache
 */
class SearchMedia
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly CacheServiceInterface $cacheService,
        private readonly bool $cacheEnabled,
        private readonly int $cacheTtlMinutes
    ) {}

    /**
     * Ejecuta la búsqueda de media con cache
     *
     * @return array{data: array, pagination: array, meta: array}
     * @throws \InvalidArgumentException Si los parámetros son inválidos
     */
    public function execute(SearchMediaDTO $dto): array
    {
        // Generar cache key usando hash MD5
        $cacheKey = $this->generateCacheKey($dto);

        // Intentar obtener desde cache
        if ($this->cacheEnabled && $this->cacheService->has($cacheKey)) {
            return $this->cacheService->get($cacheKey);
        }

        // Cache miss: buscar en repositorio
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

        $response = [
            'data' => $data,
            'pagination' => $result['pagination'],
            'meta' => $result['meta'],
        ];

        // Guardar en cache
        if ($this->cacheEnabled) {
            $this->cacheService->put($cacheKey, $response, $this->cacheTtlMinutes);
        }

        return $response;
    }

    /**
     * Genera la clave de cache usando hash MD5 de los parámetros
     */
    private function generateCacheKey(SearchMediaDTO $dto): string
    {
        $params = [
            'query' => $dto->query,
            'limit' => $dto->limit ?? 25,
            'offset' => $dto->offset ?? 0,
        ];
        
        $hash = md5(json_encode($params));
        
        return "media:search:{$hash}";
    }
}
