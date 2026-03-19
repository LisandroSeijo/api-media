<?php

declare(strict_types=1);

namespace Api\Media\Application\UseCases;

use Api\Media\Application\DTOs\GetMediaByIdDTO;
use Api\Media\Domain\Repositories\MediaRepositoryInterface;

/**
 * Caso de uso para obtener un media por ID
 */
class GetMediaById
{
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository
    ) {}

    /**
     * Ejecuta la búsqueda de media por ID
     *
     * @param GetMediaByIdDTO $dto
     * @return array|null
     * @throws \RuntimeException Si hay error conectando con el proveedor
     */
    public function execute(GetMediaByIdDTO $dto): ?array
    {
        $mediaItem = $this->mediaRepository->findById($dto->id);

        if ($mediaItem === null) {
            return null;
        }

        return $mediaItem->toArray();
    }
}
