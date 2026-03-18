<?php

namespace Src\Post\Application\UseCases;

use Src\Post\Domain\Entities\Post;
use Src\Post\Domain\Repositories\PostRepositoryInterface;
use Src\Post\Domain\ValueObjects\PostId;
use DomainException;

/**
 * Get Post Use Case
 * 
 * Caso de uso para obtener un post específico por ID.
 */
class GetPost
{
    /**
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    /**
     * Ejecuta el caso de uso de obtención de post
     * 
     * @param int $id
     * @return Post
     * @throws DomainException
     */
    public function execute(int $id): Post
    {
        $postId = new PostId($id);
        $post = $this->postRepository->findById($postId);

        if (!$post) {
            throw new DomainException("Post not found");
        }

        return $post;
    }
}
