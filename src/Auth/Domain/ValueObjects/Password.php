<?php

namespace Api\Auth\Domain\ValueObjects;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Password Value Object
 * 
 * Representa una contraseña hasheada en el dominio.
 * Encapsula la lógica de hashing y verificación.
 */
readonly class Password
{
    /**
     * @param string $hashedValue
     */
    private function __construct(private string $hashedValue) {}

    /**
     * Crea una instancia desde una contraseña en texto plano
     * 
     * @param string $plainPassword
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromPlain(string $plainPassword): self
    {
        if (strlen($plainPassword) < 6) {
            throw new InvalidArgumentException("Password must be at least 6 characters");
        }
        
        return new self(Hash::make($plainPassword));
    }

    /**
     * Crea una instancia desde una contraseña ya hasheada
     * 
     * @param string $hashedPassword
     * @return self
     */
    public static function fromHash(string $hashedPassword): self
    {
        return new self($hashedPassword);
    }

    /**
     * Obtiene el hash de la contraseña
     * 
     * @return string
     */
    public function hash(): string
    {
        return $this->hashedValue;
    }

    /**
     * Verifica si una contraseña en texto plano coincide con el hash
     * 
     * @param string $plainPassword
     * @return bool
     */
    public function verify(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->hashedValue);
    }
}
