<?php

namespace Src\Post\Domain\Repositories;

use Src\Post\Domain\Entities\Post;
use Src\Post\Domain\ValueObjects\PostId;

/**
 * Post Repository Interface
 * 
 * Define el contrato para la persistencia de posts.
 * Las implementaciones concretas estarán en la capa de Infrastructure.
 */
interface PostRepositoryInterface
{
    /**
     * Obtiene todos los posts
     * 
     * @return Post[]
     */
    public function findAll(): array;

    /**
     * Busca un post por su ID
     * 
     * @param PostId $id
     * @return Post|null
     */
    public function findById(PostId $id): ?Post;

    /**
     * Guarda un post (crear o actualizar)
     * 
     * @param Post $post
     * @return Post
     */
    public function save(Post $post): Post;

    /**
     * Elimina un post
     * 
     * @param PostId $id
     * @return void
     */
    public function delete(PostId $id): void;

    /**
     * Obtiene todos los posts de un autor específico
     * 
     * @param int $authorId
     * @return Post[]
     */
    public function findByAuthor(int $authorId): array;
}
