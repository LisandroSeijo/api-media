<?php

declare(strict_types=1);

namespace Api\Audit\Domain\Entities;

use DateTime;

/**
 * AuditLog Entity
 * 
 * Representa un registro de auditoría del sistema.
 */
class AuditLog
{
    public function __construct(
        private ?int $id,
        private ?int $userId,
        private string $service,
        private string $method,
        private ?array $requestBody,
        private int $responseCode,
        private ?array $responseBody,
        private string $ipAddress,
        private ?string $userAgent,
        private DateTime $createdAt
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestBody(): ?array
    {
        return $this->requestBody;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Verifica si la petición fue exitosa (2xx)
     */
    public function isSuccessful(): bool
    {
        return $this->responseCode >= 200 && $this->responseCode < 300;
    }

    /**
     * Verifica si la petición fue de un usuario autenticado
     */
    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }
}
