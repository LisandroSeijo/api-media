<?php

namespace Src\Auth\Application\UseCases;

use Src\Auth\Application\DTOs\LoginDTO;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\ValueObjects\Email;
use DomainException;

/**
 * Login User Use Case
 * 
 * Caso de uso para autenticar un usuario.
 * Retorna el usuario y un token de acceso OAuth2.
 */
class LoginUser
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Ejecuta el caso de uso de login
     * 
     * @param LoginDTO $dto
     * @return array{user: \Src\Auth\Domain\Entities\User, token: string}
     * @throws DomainException
     */
    public function execute(LoginDTO $dto): array
    {
        $email = new Email($dto->email);
        $user = $this->userRepository->findByEmail($email);

        // Validar que el usuario exista
        if (!$user) {
            throw new DomainException("Invalid credentials");
        }

        // Validar la contraseña usando lógica de dominio (Tell Don't Ask)
        if (!$user->verifyPassword($dto->password)) {
            throw new DomainException("Invalid credentials");
        }

        // Generar token usando Passport (detalle de implementación)
        // Nota: Accedemos al modelo de Eloquent solo para generar el token
        $userModel = \Src\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel::find($user->getId());
        $token = $userModel->createToken('API Token')->accessToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
