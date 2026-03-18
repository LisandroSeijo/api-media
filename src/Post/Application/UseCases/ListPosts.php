<?php

namespace Src\Post\Application\UseCases;

use Src\Post\Domain\Repositories\PostRepositoryInterface;

/**
 * List Posts Use Case
 * 
 * Caso de uso para listar todos los posts.
 */
class ListPosts
{
    /**
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Ejecuta el caso de uso de listado de posts
     * 
     * @return array
     */
    public function execute(): array
    {
        return $this->postRepository->findAll();
    }
}
