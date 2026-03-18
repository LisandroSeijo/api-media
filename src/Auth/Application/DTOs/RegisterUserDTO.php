<?php

namespace Src\Auth\Application\DTOs;

/**
 * Register User DTO
 * 
 * Data Transfer Object para el registro de usuarios.
 * Transporta datos entre la capa de Infrastructure y Application.
 */
readonly class RegisterUserDTO
{
    /**
     * @param string $name
     * @param string $email
     * @param string $password
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
