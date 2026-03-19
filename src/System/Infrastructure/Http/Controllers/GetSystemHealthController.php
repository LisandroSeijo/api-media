<?php

declare(strict_types=1);

namespace Api\System\Infrastructure\Http\Controllers;

use Api\System\Application\UseCases\GetSystemHealth;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Single Action Controller para obtener el estado del sistema
 */
class GetSystemHealthController extends Controller
{
    public function __construct(
        private readonly GetSystemHealth $getSystemHealth
    ) {}

    public function __invoke(): JsonResponse
    {
        $health = $this->getSystemHealth->execute();
        
        return response()->json($health, 200);
    }
}
