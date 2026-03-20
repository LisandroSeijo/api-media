<?php

declare(strict_types=1);

namespace Api\Media\Infrastructure\Http\Controllers;

use Api\Media\Application\DTOs\SearchMediaDTO;
use Api\Media\Application\UseCases\SearchMedia;
use Api\Media\Domain\Specifications\MediaSearchSpecification;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GetMediaSearchController extends Controller
{
    public function __construct(
        private readonly SearchMedia $searchMedia,
        private readonly MediaSearchSpecification $searchSpec
    ) {}

    #[OA\Get(
        path: '/api/v1/media/search',
        tags: ['Media'],
        summary: 'Buscar GIFs en GIPHY (con Redis Cache)',
        description: 'Busca GIFs a través de GIPHY API. Las respuestas se cachean en Redis para mejorar performance (Cache Hit: ~5ms, Cache Miss: ~100-300ms).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'query',
                in: 'query',
                required: true,
                description: 'Término de búsqueda (1-50 caracteres)',
                schema: new OA\Schema(type: 'string', minLength: 1, maxLength: 50, example: 'funny cats')
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Cantidad de resultados (1-50, default: 25)',
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 25, example: 10)
            ),
            new OA\Parameter(
                name: 'offset',
                in: 'query',
                required: false,
                description: 'Offset para paginación (0-4999, default: 0)',
                schema: new OA\Schema(type: 'integer', minimum: 0, maximum: 4999, default: 0, example: 0)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Búsqueda exitosa',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Media encontrado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: 'abc123'),
                                    new OA\Property(property: 'title', type: 'string', example: 'Funny Cat GIF'),
                                    new OA\Property(property: 'url', type: 'string', example: 'https://giphy.com/gifs/abc123'),
                                    new OA\Property(property: 'rating', type: 'string', example: 'g'),
                                    new OA\Property(property: 'username', type: 'string', example: 'catlovers'),
                                    new OA\Property(property: 'images', type: 'object')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_count', type: 'integer', example: 1000),
                                new OA\Property(property: 'count', type: 'integer', example: 10),
                                new OA\Property(property: 'offset', type: 'integer', example: 0)
                            ]
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'integer', example: 200),
                                new OA\Property(property: 'msg', type: 'string', example: 'OK'),
                                new OA\Property(property: 'response_id', type: 'string', example: 'abc123xyz')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Parámetros inválidos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Parámetros inválidos'),
                        new OA\Property(property: 'error', type: 'string')
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
                response: 422,
                description: 'Error de validación',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Error de validación'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: ['query' => ['El parámetro query es requerido']]
                        )
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
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;
            $offset = $request->has('offset') ? (int) $request->input('offset') : null;

            if ($this->searchSpec->hasErrors($query, $limit, $offset)) {
                $errors = $this->searchSpec->getValidationErrors($query, $limit, $offset);
                
                $formattedErrors = [];
                foreach ($errors as $field => $message) {
                    $formattedErrors[$field] = [$message];
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $formattedErrors,
                ], 422);
            }

            $dto = new SearchMediaDTO(
                query: $query,
                limit: $limit,
                offset: $offset
            );

            $result = $this->searchMedia->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'meta' => $result['meta'],
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros inválidos',
                'error' => $e->getMessage(),
            ], 400);

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
