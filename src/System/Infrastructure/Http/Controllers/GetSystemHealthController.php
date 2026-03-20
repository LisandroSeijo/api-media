<?php

declare(strict_types=1);

namespace Api\System\Infrastructure\Http\Controllers;

use Api\System\Application\UseCases\GetSystemHealth;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Single Action Controller para obtener el estado del sistema
 */
class GetSystemHealthController extends Controller
{
    public function __construct(
        private readonly GetSystemHealth $getSystemHealth
    ) {}

    #[OA\Get(
        path: '/api/v1/health',
        tags: ['System'],
        summary: 'Health check del sistema',
        description: 'Verifica el estado de salud de la API y sus componentes (Base de datos, Redis, etc.). Este endpoint NO requiere autenticación y NO se audita.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado del sistema',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'healthy'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-03-20T10:30:00Z'),
                        new OA\Property(property: 'uptime', type: 'string', example: '5 days, 3 hours'),
                        new OA\Property(
                            property: 'services',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'database',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'status', type: 'string', example: 'up'),
                                        new OA\Property(property: 'connection', type: 'string', example: 'mysql')
                                    ]
                                ),
                                new OA\Property(
                                    property: 'cache',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'status', type: 'string', example: 'up'),
                                        new OA\Property(property: 'driver', type: 'string', example: 'redis')
                                    ]
                                )
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $health = $this->getSystemHealth->execute();
        
        return response()->json($health, 200);
    }
}
