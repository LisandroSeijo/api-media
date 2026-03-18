<?php

namespace Src\Post\Application\UseCases;

use Src\Post\Application\DTOs\PostDTO;
use Src\Post\Domain\Entities\Post;
use Src\Post\Domain\Repositories\PostRepositoryInterface;
use Src\Post\Domain\ValueObjects\PostId;
use DomainException;

/**
 * Update Post Use Case
 * 
 * Caso de uso para actualizar un post existente.
 */
class UpdatePost
{
    /**
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Ejecuta el caso de uso de actualización de post
     * 
     * @param int $id
     * @param PostDTO $dto
     * @return Post
     * @throws DomainException
     */
    public function execute(int $id, PostDTO $dto): Post
    {
        $postId = new PostId($id);
        $post = $this->postRepository->findById($postId);

        if (!$post) {
            throw new DomainException("Post not found");
        }

        // Validar autorización usando lógica de dominio (Tell Don't Ask)
        if (!$post->canBeEditedBy($dto->authorId)) {
            throw new DomainException("Unauthorized to update this post");
        }

        // Actualizar el post usando lógica de dominio
        $post->update($dto->title, $dto->content);
        
        // Persistir cambios
        return $this->postRepository->save($post);
    }
}
