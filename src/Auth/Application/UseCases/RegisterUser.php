<?php

namespace Src\Auth\Application\UseCases;

use Src\Auth\Application\DTOs\RegisterUserDTO;
use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\Password;
use DomainException;

/**
 * Register User Use Case
 * 
 * Caso de uso para registrar un nuevo usuario en el sistema.
 * Orquesta la lógica de negocio sin depender de detalles de implementación.
 */
class RegisterUser
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Ejecuta el caso de uso de registro
     * 
     * @param RegisterUserDTO $dto
     * @return User
     * @throws DomainException
     */
    public function execute(RegisterUserDTO $dto): User
    {
        $email = new Email($dto->email);
        
        // Validar que el email no esté registrado
        if ($this->userRepository->existsByEmail($email)) {
            throw new DomainException("Email already registered");
        }

        // Crear la entidad de usuario
        $user = new User(
            id: null,
            name: $dto->name,
            email: $email,
            password: Password::fromPlain($dto->password)
        );

        // Persistir el usuario
        return $this->userRepository->save($user);
    }
}
