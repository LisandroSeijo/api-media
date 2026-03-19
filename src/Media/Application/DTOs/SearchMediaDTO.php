<?php

declare(strict_types=1);

namespace Api\Media\Application\DTOs;

/**
 * DTO para búsqueda de media
 */
final readonly class SearchMediaDTO
{
    public function __construct(
        public string $query,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {}
}
