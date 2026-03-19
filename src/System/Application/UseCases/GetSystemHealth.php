<?php

declare(strict_types=1);

namespace Api\System\Application\UseCases;

use Api\System\Domain\Entities\SystemHealth;

/**
 * Get System Health Use Case
 * 
 * Obtiene el estado de salud del sistema.
 */
class GetSystemHealth
{
    /**
     * Ejecuta el caso de uso
     */
    public function execute(): array
    {
        $health = SystemHealth::create();
        
        return $health->toArray();
    }
}
