<?php

declare(strict_types=1);

namespace Api\Media\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para límite de resultados
 */
final readonly class Limit
{
    private const DEFAULT_LIMIT = 25;
    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 50;

    public function __construct(
        private int $value = self::DEFAULT_LIMIT
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < self::MIN_LIMIT) {
            throw new InvalidArgumentException(
                sprintf('Limit must be at least %d', self::MIN_LIMIT)
            );
        }

        if ($this->value > self::MAX_LIMIT) {
            throw new InvalidArgumentException(
                sprintf('Limit cannot exceed %d', self::MAX_LIMIT)
            );
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_LIMIT);
    }
}
