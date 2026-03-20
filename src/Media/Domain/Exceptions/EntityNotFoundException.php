<?php

declare(strict_types=1);

namespace Api\Media\Domain\Exceptions;

use RuntimeException;

/**
 * Excepción lanzada cuando una entidad de Media no es encontrada
 */
class EntityNotFoundException extends RuntimeException
{
    public function __construct(string $entityName, string $id)
    {
        parent::__construct(
            sprintf('Entity %s with ID "%s" not found', $entityName, $id)
        );
    }
}
