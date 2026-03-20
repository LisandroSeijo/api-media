<?php

declare(strict_types=1);

namespace Api\Auth\Domain\Services;

use Api\Auth\Domain\Entities\User;

interface TokenServiceInterface
{
    /**
     * Generate an authentication token for a user
     * 
     * @return array{token: string, expires_at: string}
     */
    public function generateToken(User $user, string $tokenName): array;
}
