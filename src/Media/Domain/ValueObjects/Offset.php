<?php

declare(strict_types=1);

namespace Api\Media\Domain\ValueObjects;

use Api\Media\Domain\Specifications\OffsetSpecification;
use InvalidArgumentException;

/**
 * Value Object para offset de paginación
 */
final readonly class Offset
{
    public function __construct(
        private int $value = 0 // Default
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $spec = new OffsetSpecification();
        
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
        $spec = new OffsetSpecification();
        return new self($spec->getDefaultOffset());
    }
}
