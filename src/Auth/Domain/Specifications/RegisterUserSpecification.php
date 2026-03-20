<?php

declare(strict_types=1);

namespace Api\Auth\Domain\Specifications;

/**
 * Composite Specification para validar datos de registro de usuario
 * 
 * Agrupa las validaciones de name, email y password
 */
final readonly class RegisterUserSpecification
{
    private NameSpecification $nameSpec;
    private EmailSpecification $emailSpec;
    private RegisterPasswordSpecification $passwordSpec;

    public function __construct()
    {
        $this->nameSpec = new NameSpecification();
        $this->emailSpec = new EmailSpecification();
        $this->passwordSpec = new RegisterPasswordSpecification();
    }

    /**
     * Verifica si los datos de registro satisfacen todas las especificaciones
     */
    public function isSatisfiedBy(string $name, string $email, string $password): bool
    {
        return $this->nameSpec->isSatisfiedBy($name)
            && $this->emailSpec->isSatisfiedBy($email)
            && $this->passwordSpec->isSatisfiedBy($password);
    }

    /**
     * Obtiene todos los errores de validación
     *
     * @return array<string, string> Array asociativo con campo => mensaje de error
     */
    public function getValidationErrors(string $name, string $email, string $password): array
    {
        $errors = [];

        if (!$this->nameSpec->isSatisfiedBy($name)) {
            $errors['name'] = $this->nameSpec->getErrorMessage($name);
        }

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
    public function hasErrors(string $name, string $email, string $password): bool
    {
        return !empty($this->getValidationErrors($name, $email, $password));
    }
}
