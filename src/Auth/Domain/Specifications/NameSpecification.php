<?php

declare(strict_types=1);

namespace Api\Auth\Domain\Specifications;

use Api\Shared\Domain\Specifications\SpecificationInterface;

/**
 * Specification para validar el nombre de usuario
 */
final readonly class NameSpecification implements SpecificationInterface
{
    private const MIN_NAME_LENGTH = 1;
    private const MAX_NAME_LENGTH = 255;

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!is_string($candidate)) {
            return false;
        }

        $length = strlen(trim($candidate));

        return $length >= self::MIN_NAME_LENGTH && $length <= self::MAX_NAME_LENGTH;
    }

    public function getErrorMessage(mixed $candidate): string
    {
        if (!is_string($candidate)) {
            return 'El nombre debe ser una cadena de texto';
        }

        $length = strlen(trim($candidate));

        if ($length < self::MIN_NAME_LENGTH) {
            return 'El nombre es requerido';
        }

        if ($length > self::MAX_NAME_LENGTH) {
            return sprintf('El nombre no puede exceder %d caracteres', self::MAX_NAME_LENGTH);
        }

        return 'El nombre es inválido';
    }
}
