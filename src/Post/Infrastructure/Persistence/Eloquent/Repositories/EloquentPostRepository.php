<?php

namespace Src\Post\Infrastructure\Persistence\Eloquent\Repositories;

use Src\Post\Domain\Entities\Post;
use Src\Post\Domain\Repositories\PostRepositoryInterface;
use Src\Post\Domain\ValueObjects\PostId;
use Src\Post\Infrastructure\Persistence\Eloquent\Models\PostModel;
use DateTime;

/**
 * Eloquent Post Repository
 * 
 * Implementación concreta del repositorio de posts usando Eloquent ORM.
 * Mapea entre PostModel (Eloquent) y Post (Domain Entity).
 */
class EloquentPostRepository implements PostRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        $models = PostModel::orderBy('created_at', 'desc')->get();
        
        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function findById(PostId $id): ?Post
    {
        $model = PostModel::find($id->value());
        
        return $model ? $this->toDomain($model) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function save(Post $post): Post
    {
        // Si el post tiene ID, es una actualización
        $model = $post->getId()
            ? PostModel::find($post->getId()->value())
            : new PostModel();

        // Si no se encontró el modelo, crear uno nuevo
        if (!$model) {
            $model = new PostModel();
        }

        // Mapear datos de la entidad de dominio al modelo
        $model->title = $post->getTitle();
        $model->content = $post->getContent();
        $model->user_id = $post->getAuthorId();
        
        // Guardar en la base de datos
        $model->save();

        // Retornar la entidad de dominio actualizada
        return $this->toDomain($model);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(PostId $id): void
    {
        PostModel::destroy($id->value());
    }

    /**
     * {@inheritDoc}
     */
    public function findByAuthor(int $authorId): array
    {
        $models = PostModel::where('user_id', $authorId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }

    /**
     * Mapea un PostModel (Eloquent) a una entidad Post (Domain)
     * 
     * @param PostModel $model
     * @return Post
     */
    private function toDomain(PostModel $model): Post
    {
        return new Post(
            id: new PostId($model->id),
            title: $model->title,
            content: $model->content,
            authorId: $model->user_id,
            createdAt: DateTime::createFromFormat('Y-m-d H:i:s', $model->created_at),
            updatedAt: DateTime::createFromFormat('Y-m-d H:i:s', $model->updated_at)
        );
    }
}
