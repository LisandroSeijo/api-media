<?php

declare(strict_types=1);

namespace Api\Audit\Domain\Entities;

use DateTimeImmutable;

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
        private DateTimeImmutable $createdAt
    ) {}

    public static function create(
        ?int $userId,
        string $service,
        string $method,
        array $requestBody,
        int $responseCode,
        array $responseBody,
        string $ipAddress,
        ?string $userAgent
    ): self {
        return new self(
            id: null,
            userId: $userId,
            service: $service,
            method: $method,
            requestBody: $requestBody,
            responseCode: $responseCode,
            responseBody: $responseBody,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            createdAt: new DateTimeImmutable()
        );
    }

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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isSuccessful(): bool
    {
        return $this->responseCode >= 200 && $this->responseCode < 300;
    }

    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }
}
