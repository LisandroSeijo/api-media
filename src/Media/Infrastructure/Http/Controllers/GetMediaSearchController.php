<?php

declare(strict_types=1);

namespace Api\Media\Infrastructure\Http\Controllers;

use Api\Media\Application\DTOs\SearchMediaDTO;
use Api\Media\Application\UseCases\SearchMedia;
use Api\Media\Domain\Specifications\MediaSearchSpecification;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMediaSearchController extends Controller
{
    public function __construct(
        private readonly SearchMedia $searchMedia,
        private readonly MediaSearchSpecification $searchSpec
    ) {}

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
