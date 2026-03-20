<?php

declare(strict_types=1);

namespace Api\Audit\Infrastructure\Persistence\Eloquent\Repositories;

use Api\Audit\Domain\Entities\AuditLog;
use Api\Audit\Domain\Repositories\AuditLogRepositoryInterface;
use Api\Audit\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use DateTimeImmutable;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function save(AuditLog $auditLog): AuditLog
    {
        $model = AuditLogModel::create([
            'user_id' => $auditLog->getUserId(),
            'service' => $auditLog->getService(),
            'method' => $auditLog->getMethod(),
            'request_body' => $auditLog->getRequestBody(),
            'response_code' => $auditLog->getResponseCode(),
            'response_body' => $auditLog->getResponseBody(),
            'ip_address' => $auditLog->getIpAddress(),
            'user_agent' => $auditLog->getUserAgent(),
            'created_at' => $auditLog->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        return $this->toDomain($model);
    }

    private function toDomain(AuditLogModel $model): AuditLog
    {
        return new AuditLog(
            id: $model->id,
            userId: $model->user_id,
            service: $model->service,
            method: $model->method,
            requestBody: $model->request_body,
            responseCode: $model->response_code,
            responseBody: $model->response_body,
            ipAddress: $model->ip_address,
            userAgent: $model->user_agent,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at)
        );
    }
}
