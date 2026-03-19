<?php

declare(strict_types=1);

namespace Api\Media\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para offset de paginación
 */
final readonly class Offset
{
    private const DEFAULT_OFFSET = 0;
    private const MIN_OFFSET = 0;
    private const MAX_OFFSET = 4999;

    public function __construct(
        private int $value = self::DEFAULT_OFFSET
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN_OFFSET) {
            throw new InvalidArgumentException(
                sprintf('Offset must be at least %d', self::MIN_OFFSET)
            );
        }

        if ($this->value > self::MAX_OFFSET) {
            throw new InvalidArgumentException(
                sprintf('Offset cannot exceed %d', self::MAX_OFFSET)
            );
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_OFFSET);
    }
}
