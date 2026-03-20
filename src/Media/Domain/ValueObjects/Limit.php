<?php

declare(strict_types=1);

namespace Api\Media\Domain\ValueObjects;

use Api\Media\Domain\Specifications\LimitSpecification;
use InvalidArgumentException;

/**
 * Value Object para límite de resultados
 */
final readonly class Limit
{
    public function __construct(
        private int $value = 25 // Default
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $spec = new LimitSpecification();
        
        if (!$spec->isSatisfiedBy($this->value)) {
            throw new InvalidArgumentException($spec->getErrorMessage($this->value));
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public static function default(): self
    {
        $spec = new LimitSpecification();
        return new self($spec->getDefaultLimit());
    }
}
