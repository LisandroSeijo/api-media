<?php

namespace Src\Auth\Application\DTOs;

/**
 * Login DTO
 * 
 * Data Transfer Object para el inicio de sesión.
 */
readonly class LoginDTO
{
    /**
     * @param string $email
     * @param string $password
     */
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
