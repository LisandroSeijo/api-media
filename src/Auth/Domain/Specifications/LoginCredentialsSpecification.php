<?php

declare(strict_types=1);

namespace Api\Auth\Domain\Specifications;

/**
 * Composite Specification para validar credenciales de login
 * 
 * Agrupa las validaciones de email y password
 */
final readonly class LoginCredentialsSpecification
{
    private EmailSpecification $emailSpec;
    private PasswordSpecification $passwordSpec;

    public function __construct()
    {
        $this->emailSpec = new EmailSpecification();
        $this->passwordSpec = new PasswordSpecification();
    }

    /**
     * Verifica si las credenciales satisfacen todas las especificaciones
     */
    public function isSatisfiedBy(string $email, string $password): bool
    {
        return $this->emailSpec->isSatisfiedBy($email) 
            && $this->passwordSpec->isSatisfiedBy($password);
    }

    /**
     * Obtiene todos los errores de validación
     *
     * @return array<string, string> Array asociativo con campo => mensaje de error
     */
    public function getValidationErrors(string $email, string $password): array
    {
        $errors = [];

        if (!$this->emailSpec->isSatisfiedBy($email)) {
            $errors['email'] = $this->emailSpec->getErrorMessage($email);
        }

        if (!$this->passwordSpec->isSatisfiedBy($password)) {
            $errors['password'] = $this->passwordSpec->getErrorMessage($password);
        }

        return $errors;
    }

    /**
     * Verifica si hay errores de validación
     */
    public function hasErrors(string $email, string $password): bool
    {
        return !empty($this->getValidationErrors($email, $password));
    }
}
