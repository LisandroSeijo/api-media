<?php

declare(strict_types=1);

namespace Api\Media\Application\DTOs;

/**
 * DTO para obtener un media por ID
 */
final readonly class GetMediaByIdDTO
{
    public function __construct(
        public string $id,
    ) {}
}
