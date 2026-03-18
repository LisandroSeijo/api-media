<?php

namespace Src\Post\Application\UseCases;

use Src\Post\Application\DTOs\PostDTO;
use Src\Post\Domain\Entities\Post;
use Src\Post\Domain\Repositories\PostRepositoryInterface;

/**
 * Create Post Use Case
 * 
 * Caso de uso para crear un nuevo post.
 */
class CreatePost
{
    /**
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Ejecuta el caso de uso de creación de post
     * 
     * @param PostDTO $dto
     * @return Post
     */
    public function execute(PostDTO $dto): Post
    {
        // Crear la entidad de post
        $post = new Post(
            id: null,
            title: $dto->title,
            content: $dto->content,
            authorId: $dto->authorId
        );

        // Persistir el post
        return $this->postRepository->save($post);
    }
}
