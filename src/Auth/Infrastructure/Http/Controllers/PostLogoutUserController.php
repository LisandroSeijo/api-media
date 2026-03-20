<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Api\Auth\Application\UseCases\LogoutUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use OpenApi\Attributes as OA;

class PostLogoutUserController extends Controller
{
    public function __construct(
        private readonly LogoutUser $logoutUser
    ) {}

    #[OA\Post(
        path: '/api/v1/logout',
        tags: ['Authentication'],
        summary: 'Logout de usuario',
        description: 'Cierra la sesión del usuario autenticado revocando el token de acceso.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada exitosamente')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno del servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Logout failed'),
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->logoutUser->execute($request);

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
