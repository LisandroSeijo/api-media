<?php

namespace Src\Post\Application\DTOs;

/**
 * Post DTO
 * 
 * Data Transfer Object para operaciones con posts.
 * Transporta datos entre la capa de Infrastructure y Application.
 */
readonly class PostDTO
{
    /**
     * @param string $title
     * @param string $content
     * @param int $authorId
     */
    public function __construct(
        public string $title,
        public string $content,
        public int $authorId,
    ) {}
}
