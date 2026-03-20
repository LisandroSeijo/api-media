<?php

declare(strict_types=1);

namespace Api\Auth\Domain\Specifications;

use Api\Shared\Domain\Specifications\SpecificationInterface;

/**
 * Specification para validar la contraseña de registro (más estricta)
 */
final readonly class RegisterPasswordSpecification implements SpecificationInterface
{
    private const MIN_PASSWORD_LENGTH = 6;
    private const MAX_PASSWORD_LENGTH = 255;

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!is_string($candidate)) {
            return false;
        }

        $length = strlen($candidate);

        return $length >= self::MIN_PASSWORD_LENGTH && $length <= self::MAX_PASSWORD_LENGTH;
    }

    public function getErrorMessage(mixed $candidate): string
    {
        if (!is_string($candidate)) {
            return 'La contraseña debe ser una cadena de texto';
        }

        $length = strlen($candidate);

        if ($length < self::MIN_PASSWORD_LENGTH) {
            return sprintf('La contraseña debe tener al menos %d caracteres', self::MIN_PASSWORD_LENGTH);
        }

        if ($length > self::MAX_PASSWORD_LENGTH) {
            return sprintf('La contraseña no puede exceder %d caracteres', self::MAX_PASSWORD_LENGTH);
        }

        return 'La contraseña es inválida';
    }
}
