<?php

namespace Api\Auth\Application\UseCases;

use Api\Auth\Application\DTOs\LoginDTO;
use Api\Auth\Domain\Repositories\UserRepositoryInterface;
use Api\Auth\Domain\Services\TokenServiceInterface;
use Api\Auth\Domain\ValueObjects\Email;
use DomainException;

/**
 * Login User Use Case
 * 
 * Autentica un usuario y genera un token de acceso.
 * Usa TokenService para mantener la capa de Application independiente de Passport.
 */
class LoginUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenServiceInterface $tokenService
    ) {}

    /**
     * Ejecuta el caso de uso de login
     * 
     * @return array{user: \Api\Auth\Domain\Entities\User, token: string, expires_at: string}
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

        // Generar token usando el servicio de tokens
        $tokenData = $this->tokenService->generateToken($user, 'API Token');

        return [
            'user' => $user,
            'token' => $tokenData['token'],
            'expires_at' => $tokenData['expires_at']
        ];
    }
}
