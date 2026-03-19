<?php

declare(strict_types=1);

namespace Api\Auth\Application\UseCases;

use Api\Auth\Application\DTOs\RegisterUserDTO;
use Api\Auth\Domain\Entities\User;
use Api\Auth\Domain\Repositories\UserRepositoryInterface;
use Api\Auth\Domain\ValueObjects\Email;
use Api\Auth\Domain\ValueObjects\Password;
use Api\Auth\Domain\ValueObjects\Role;
use DomainException;

/**
 * Create Admin User Use Case
 * 
 * Caso de uso para crear un usuario administrador.
 * Solo debe ser usado por comandos de consola o procesos internos.
 */
class CreateAdminUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Ejecuta el caso de uso de creación de admin
     * 
     * @throws DomainException
     */
    public function execute(RegisterUserDTO $dto): User
    {
        $email = new Email($dto->email);
        
        // Validar que el email no esté registrado
        if ($this->userRepository->existsByEmail($email)) {
            throw new DomainException("Email already registered");
        }

        // Crear la entidad de usuario con rol ADMIN
        $user = new User(
            id: null,
            name: $dto->name,
            email: $email,
            password: Password::fromPlain($dto->password),
            role: Role::ADMIN
        );

        // Persistir el usuario
        return $this->userRepository->save($user);
    }
}
