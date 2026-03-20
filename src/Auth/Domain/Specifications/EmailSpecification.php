<?php

declare(strict_types=1);

namespace Api\Auth\Domain\Specifications;

use Api\Shared\Domain\Specifications\SpecificationInterface;

/**
 * Specification para validar el formato de email
 */
final readonly class EmailSpecification implements SpecificationInterface
{
    private const MAX_EMAIL_LENGTH = 255;

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!is_string($candidate)) {
            return false;
        }

        // Validar longitud
        if (strlen($candidate) > self::MAX_EMAIL_LENGTH) {
            return false;
        }

        // Validar formato usando filter_var
        return filter_var($candidate, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getErrorMessage(mixed $candidate): string
    {
        if (!is_string($candidate)) {
            return 'El email debe ser una cadena de texto';
        }

        if (strlen($candidate) > self::MAX_EMAIL_LENGTH) {
            return sprintf('El email no puede exceder %d caracteres', self::MAX_EMAIL_LENGTH);
        }

        if (empty($candidate)) {
            return 'El email es requerido';
        }

        return 'El formato del email es inválido';
    }
}
