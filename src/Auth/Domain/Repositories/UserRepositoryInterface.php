<?php

namespace Api\Auth\Domain\Repositories;

use Api\Auth\Domain\Entities\User;
use Api\Auth\Domain\ValueObjects\Email;

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
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Busca un usuario por su ID
     */
    public function findById(int $id): ?User;

    /**
     * Guarda un usuario (crear o actualizar)
     */
    public function save(User $user): User;

    /**
     * Verifica si existe un usuario con el email especificado
     */
    public function existsByEmail(Email $email): bool;
}
