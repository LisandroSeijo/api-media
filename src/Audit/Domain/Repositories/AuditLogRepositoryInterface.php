<?php

declare(strict_types=1);

namespace Api\Audit\Domain\Repositories;

use Api\Audit\Domain\Entities\AuditLog;

interface AuditLogRepositoryInterface
{
    public function save(AuditLog $auditLog): AuditLog;
}
