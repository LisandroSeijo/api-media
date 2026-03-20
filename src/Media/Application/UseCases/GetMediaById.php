<?php

declare(strict_types=1);

namespace Api\Media\Application\UseCases;

use Api\Media\Application\DTOs\GetMediaByIdDTO;
use Api\Media\Domain\Entities\MediaItem;
use Api\Media\Domain\Exceptions\EntityNotFoundException;
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
     * @throws EntityNotFoundException Si el media no es encontrado
     * @throws \RuntimeException Si hay error conectando con el proveedor
     */
    public function execute(GetMediaByIdDTO $dto): MediaItem
    {
        $mediaItem = $this->mediaRepository->findById($dto->id);

        if ($mediaItem === null) {
            throw new EntityNotFoundException('MediaItem', $dto->id);
        }

        return $mediaItem;
    }
}
