<?php

declare(strict_types=1);

namespace Api\Media\Infrastructure\Http\Controllers;

use Api\Media\Application\DTOs\GetMediaByIdDTO;
use Api\Media\Application\UseCases\GetMediaById;
use Api\Media\Domain\Exceptions\EntityNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Single Action Controller para obtener un media por ID
 */
class GetMediaByIdController extends Controller
{
    public function __construct(
        private readonly GetMediaById $getMediaById
    ) {}

    #[OA\Get(
        path: '/api/v1/media/{id}',
        tags: ['Media'],
        summary: 'Obtener GIF por ID (con Redis Cache)',
        description: 'Obtiene un GIF específico de GIPHY por su ID. Las respuestas se cachean en Redis (Cache key: media:id:{id}).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del GIF en GIPHY',
                schema: new OA\Schema(type: 'string', example: 'abc123xyz')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'GIF encontrado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Media encontrado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'string', example: 'abc123xyz'),
                                new OA\Property(property: 'title', type: 'string', example: 'Funny Cat GIF'),
                                new OA\Property(property: 'url', type: 'string', example: 'https://giphy.com/gifs/abc123xyz'),
                                new OA\Property(property: 'rating', type: 'string', example: 'g'),
                                new OA\Property(property: 'username', type: 'string', example: 'catlovers'),
                                new OA\Property(
                                    property: 'images',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'original',
                                            type: 'object',
                                            properties: [
                                                new OA\Property(property: 'url', type: 'string', example: 'https://media.giphy.com/media/abc123/giphy.gif')
                                            ]
                                        ),
                                        new OA\Property(
                                            property: 'preview_gif',
                                            type: 'object',
                                            properties: [
                                                new OA\Property(property: 'url', type: 'string', example: 'https://media.giphy.com/media/abc123/200.gif')
                                            ]
                                        )
                                    ]
                                )
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
                response: 404,
                description: 'GIF no encontrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Media no encontrado'),
                        new OA\Property(property: 'error', type: 'string', example: 'Media with ID "abc123xyz" not found.')
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Error al conectar con GIPHY API',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Error al conectar con el proveedor de media'),
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno del servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Error interno del servidor'),
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $dto = new GetMediaByIdDTO($id);
            $result = $this->getMediaById->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
                'data' => $result->toArray(),
            ], 200);

        } catch (EntityNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Media no encontrado',
                'error' => $e->getMessage(),
            ], 404);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al conectar con el proveedor de media',
                'error' => $e->getMessage(),
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error procesando la solicitud',
            ], 500);
        }
    }
}
