<?php

declare(strict_types=1);

namespace Api\System\Domain\Entities;

use Api\System\Domain\ValueObjects\Version;
use DateTime;

/**
 * SystemHealth Entity
 * 
 * Representa el estado de salud del sistema.
 */
class SystemHealth
{
    public function __construct(
        private readonly string $status,
        private readonly Version $version,
        private readonly DateTime $timestamp,
        private readonly string $environment,
        private readonly bool $debug
    ) {}

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    public function toArray(): array
    {
        return [
            'success' => $this->isHealthy(),
            'status' => $this->status,
            'message' => 'API is running',
            'version' => $this->version->toString(),
            'environment' => $this->environment,
            'debug' => $this->debug,
            'timestamp' => $this->timestamp->format(DateTime::ATOM),
        ];
    }

    public static function create(): self
    {
        return new self(
            status: 'healthy',
            version: Version::fromString(config('app.version', '1.0.0')),
            timestamp: new DateTime(),
            environment: config('app.env', 'local'),
            debug: config('app.debug', false)
        );
    }
}
