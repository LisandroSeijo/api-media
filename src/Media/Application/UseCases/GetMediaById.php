<?php

declare(strict_types=1);

namespace Api\Media\Application\UseCases;

use Api\Media\Application\DTOs\GetMediaByIdDTO;
use Api\Media\Domain\Entities\MediaItem;
use Api\Media\Domain\Exceptions\EntityNotFoundException;
use Api\Media\Domain\Repositories\MediaRepositoryInterface;
use Api\Shared\Domain\Services\CacheServiceInterface;

/**
 * Caso de uso para obtener un media por ID con soporte de cache
 */
class GetMediaById
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly CacheServiceInterface $cacheService,
        private readonly bool $cacheEnabled,
        private readonly int $cacheTtlMinutes
    ) {}

    /**
     * Ejecuta la búsqueda de media por ID con cache
     *
     * @throws EntityNotFoundException Si el media no es encontrado
     * @throws \RuntimeException Si hay error conectando con el proveedor
     */
    public function execute(GetMediaByIdDTO $dto): MediaItem
    {
        $cacheKey = "media:id:{$dto->id}";

        // Intentar obtener desde cache
        if ($this->cacheEnabled && $this->cacheService->has($cacheKey)) {
            $cachedData = $this->cacheService->get($cacheKey);
            return MediaItem::fromApiResponse($cachedData);
        }

        // Cache miss: buscar en repositorio
        $mediaItem = $this->mediaRepository->findById($dto->id);

        if ($mediaItem === null) {
            throw new EntityNotFoundException('MediaItem', $dto->id);
        }

        // Guardar en cache
        if ($this->cacheEnabled) {
            $this->cacheService->put($cacheKey, $mediaItem->toArray(), $this->cacheTtlMinutes);
        }

        return $mediaItem;
    }
}
