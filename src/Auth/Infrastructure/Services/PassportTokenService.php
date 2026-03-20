<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Services;

use Api\Auth\Domain\Entities\User;
use Api\Auth\Domain\Services\TokenServiceInterface;
use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use RuntimeException;

class PassportTokenService implements TokenServiceInterface
{
    public function generateToken(User $user, string $tokenName): array
    {
        $userModel = UserModel::find($user->getId());
        
        if (!$userModel) {
            throw new RuntimeException("User model not found for ID: {$user->getId()}");
        }
        
        $tokenResult = $userModel->createToken($tokenName);
        
        return [
            'token' => $tokenResult->accessToken,
            'expires_at' => $tokenResult->token->expires_at->toIso8601String()
        ];
    }
}
