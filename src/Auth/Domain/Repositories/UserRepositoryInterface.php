<?php

namespace Src\Auth\Domain\Repositories;

use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\ValueObjects\Email;

/**
 * User Repository Interface
 * 
 * Define el contrato para la persistencia de usuarios.
 * Las implementaciones concretas estarán en la capa de Infrastructure.
 */
interface UserRepositoryInterface
{
    /**
     * Busca un usuario por su email
     * 
     * @param Email $email
     * @return User|null
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Busca un usuario por su ID
     * 
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Guarda un usuario (crear o actualizar)
     * 
     * @param User $user
     * @return User
     */
    public function save(User $user): User;

    /**
     * Verifica si existe un usuario con el email especificado
     * 
     * @param Email $email
     * @return bool
     */
    public function existsByEmail(Email $email): bool;
}
