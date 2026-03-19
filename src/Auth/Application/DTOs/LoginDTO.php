<?php

namespace Api\Auth\Application\DTOs;

/**
 * Login DTO
 * 
 * Data Transfer Object para el inicio de sesión.
 */
readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
