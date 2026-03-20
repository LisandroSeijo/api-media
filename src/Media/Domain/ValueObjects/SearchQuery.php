<?php

declare(strict_types=1);

namespace Api\Media\Domain\ValueObjects;

use Api\Media\Domain\Specifications\SearchQuerySpecification;
use InvalidArgumentException;

/**
 * Value Object para consultas de búsqueda de media
 */
final readonly class SearchQuery
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $spec = new SearchQuerySpecification();
        
        if (!$spec->isSatisfiedBy($this->value)) {
            throw new InvalidArgumentException($spec->getErrorMessage($this->value));
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getUrlEncoded(): string
    {
        return urlencode($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
