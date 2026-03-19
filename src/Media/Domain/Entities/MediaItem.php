<?php

declare(strict_types=1);

namespace Api\Media\Domain\Entities;

/**
 * Entidad MediaItem del dominio
 * Representa un elemento de media (GIF, sticker, video) con sus propiedades principales
 */
class MediaItem
{
    public function __construct(
        private readonly string $id,
        private readonly string $title,
        private readonly string $url,
        private readonly array $images,
        private readonly string $rating,
        private readonly ?string $username = null,
        private readonly ?array $analytics = null,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getRating(): string
    {
        return $this->rating;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getAnalytics(): ?array
    {
        return $this->analytics;
    }

    /**
     * Obtiene la URL del media en formato original
     */
    public function getOriginalUrl(): ?string
    {
        return $this->images['original']['url'] ?? null;
    }

    /**
     * Obtiene la URL del media en formato preview (fixed_height)
     */
    public function getPreviewUrl(): ?string
    {
        return $this->images['fixed_height']['url'] ?? null;
    }

    /**
     * Obtiene la URL del media en formato MP4 (recomendado por GIPHY)
     */
    public function getMp4Url(): ?string
    {
        return $this->images['original']['mp4'] ?? null;
    }

    /**
     * Obtiene la URL del media en formato WEBP
     */
    public function getWebpUrl(): ?string
    {
        return $this->images['original']['webp'] ?? null;
    }

    /**
     * Convierte la entidad a un array para respuestas API
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'rating' => $this->rating,
            'username' => $this->username,
            'images' => [
                'original' => $this->getOriginalUrl(),
                'preview' => $this->getPreviewUrl(),
                'mp4' => $this->getMp4Url(),
                'webp' => $this->getWebpUrl(),
            ],
            'analytics' => $this->analytics,
        ];
    }

    /**
     * Crea una instancia desde el array de respuesta de la API de GIPHY
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'] ?? '',
            url: $data['url'] ?? '',
            images: $data['images'] ?? [],
            rating: $data['rating'] ?? 'g',
            username: $data['username'] ?? null,
            analytics: $data['analytics'] ?? null,
        );
    }
}
