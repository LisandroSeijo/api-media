<?php

declare(strict_types=1);

namespace Api\Audit\Application\UseCases;

use Api\Audit\Application\DTOs\CreateAuditLogDTO;
use Api\Audit\Domain\Entities\AuditLog;
use Api\Audit\Domain\Repositories\AuditLogRepositoryInterface;
use DateTimeImmutable;

class CreateAuditLog
{
    public function __construct(
        private AuditLogRepositoryInterface $auditLogRepository
    ) {}

    public function execute(CreateAuditLogDTO $dto): AuditLog
    {
        $auditLog = new AuditLog(
            id: null,
            userId: $dto->userId,
            service: $dto->service,
            method: $dto->method,
            requestBody: $dto->requestBody,
            responseCode: $dto->responseCode,
            responseBody: $dto->responseBody,
            ipAddress: $dto->ipAddress,
            userAgent: $dto->userAgent,
            createdAt: new DateTimeImmutable()
        );

        return $this->auditLogRepository->save($auditLog);
    }
}
