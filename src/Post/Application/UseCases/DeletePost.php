<?php

namespace Src\Post\Application\UseCases;

use Src\Post\Domain\Repositories\PostRepositoryInterface;
use Src\Post\Domain\ValueObjects\PostId;
use DomainException;

/**
 * Delete Post Use Case
 * 
 * Caso de uso para eliminar un post.
 */
class DeletePost
{
    /**
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Ejecuta el caso de uso de eliminación de post
     * 
     * @param int $id
     * @param int $userId
     * @return void
     * @throws DomainException
     */
    public function execute(int $id, int $userId): void
    {
        $postId = new PostId($id);
        $post = $this->postRepository->findById($postId);

        if (!$post) {
            throw new DomainException("Post not found");
        }

        // Validar autorización usando lógica de dominio (Tell Don't Ask)
        if (!$post->canBeDeletedBy($userId)) {
            throw new DomainException("Unauthorized to delete this post");
        }

        // Eliminar el post
        $this->postRepository->delete($postId);
    }
}
