<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use OpenApi\Attributes as OA;

class GetAuthenticatedUserController extends Controller
{
    #[OA\Get(
        path: '/api/v1/user',
        tags: ['Authentication'],
        summary: 'Obtener perfil del usuario autenticado',
        description: 'Retorna la información del usuario actualmente autenticado.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil del usuario',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Admin User'),
                                new OA\Property(property: 'email', type: 'string', example: 'admin@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'ADMIN'),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-03-20T10:30:00.000000Z')
                            ]
                        )
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
                        new OA\Property(property: 'message', type: 'string', example: 'Failed to get user'),
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $userModel = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'email' => $userModel->email,
                    'role' => $userModel->role,
                    'created_at' => $userModel->created_at,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
