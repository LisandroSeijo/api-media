<?php

declare(strict_types=1);

namespace Api\Media\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para consultas de búsqueda de media
 */
final readonly class SearchQuery
{
    private const MAX_QUERY_LENGTH = 50;
    private const MIN_QUERY_LENGTH = 1;

    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $length = mb_strlen(trim($this->value));

        if ($length < self::MIN_QUERY_LENGTH) {
            throw new InvalidArgumentException('Search query cannot be empty');
        }

        if ($length > self::MAX_QUERY_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Search query exceeds maximum length of %d characters', self::MAX_QUERY_LENGTH)
            );
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
