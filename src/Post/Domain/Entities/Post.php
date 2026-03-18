<?php

namespace Src\Post\Domain\Entities;

use Src\Post\Domain\ValueObjects\PostId;
use DateTime;

/**
 * Post Domain Entity
 * 
 * Representa un post en el dominio del negocio.
 * Contiene lógica de negocio pura sin dependencias de frameworks.
 */
class Post
{
    /**
     * @param PostId|null $id
     * @param string $title
     * @param string $content
     * @param int $authorId
     * @param DateTime|null $createdAt
     * @param DateTime|null $updatedAt
     */
    public function __construct(
        private ?PostId $id,
        private string $title,
        private string $content,
        private int $authorId,
        private ?DateTime $createdAt = null,
        private ?DateTime $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    // Getters
    public function getId(): ?PostId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Domain logic - Tell Don't Ask
    
    /**
     * Actualiza el contenido del post
     * 
     * @param string $title
     * @param string $content
     * @return void
     */
    public function update(string $title, string $content): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->updatedAt = new DateTime();
    }

    /**
     * Verifica si el post pertenece al usuario especificado
     * 
     * @param int $userId
     * @return bool
     */
    public function isOwnedBy(int $userId): bool
    {
        return $this->authorId === $userId;
    }

    /**
     * Verifica si el post puede ser editado por el usuario
     * El post solo puede ser editado por su autor
     * 
     * @param int $userId
     * @return bool
     */
    public function canBeEditedBy(int $userId): bool
    {
        return $this->isOwnedBy($userId);
    }

    /**
     * Verifica si el post puede ser eliminado por el usuario
     * El post solo puede ser eliminado por su autor
     * 
     * @param int $userId
     * @return bool
     */
    public function canBeDeletedBy(int $userId): bool
    {
        return $this->isOwnedBy($userId);
    }
}
