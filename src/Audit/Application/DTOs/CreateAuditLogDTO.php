<?php

declare(strict_types=1);

namespace Api\Audit\Application\DTOs;

/**
 * DTO para crear un registro de auditoría
 */
final readonly class CreateAuditLogDTO
{
    public function __construct(
        public ?int $userId,
        public string $service,
        public string $method,
        public ?array $requestBody,
        public int $responseCode,
        public ?array $responseBody,
        public string $ipAddress,
        public ?string $userAgent = null
    ) {}
}
