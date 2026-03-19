<?php

declare(strict_types=1);

namespace Api\System\Domain\ValueObjects;

/**
 * Version Value Object
 * 
 * Representa una versión semántica del sistema (SemVer).
 */
final readonly class Version
{
    public function __construct(
        private int $major,
        private int $minor,
        private int $patch
    ) {}

    public static function fromString(string $version): self
    {
        $parts = explode('.', $version);
        
        return new self(
            major: (int) ($parts[0] ?? 1),
            minor: (int) ($parts[1] ?? 0),
            patch: (int) ($parts[2] ?? 0)
        );
    }

    public function toString(): string
    {
        return "{$this->major}.{$this->minor}.{$this->patch}";
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function getPatch(): int
    {
        return $this->patch;
    }
}
