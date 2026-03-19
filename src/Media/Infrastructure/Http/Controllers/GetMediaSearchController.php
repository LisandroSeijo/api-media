<?php

declare(strict_types=1);

namespace Api\Media\Infrastructure\Http\Controllers;

use Api\Media\Application\DTOs\SearchMediaDTO;
use Api\Media\Application\UseCases\SearchMedia;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Single Action Controller para buscar media
 */
class GetMediaSearchController extends Controller
{
    public function __construct(
        private readonly SearchMedia $searchMedia
    ) {}

    /**
     * Busca media por término o frase
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Validar entrada
            $validated = $request->validate([
                'query' => 'required|string|max:50',
                'limit' => 'nullable|integer|min:1|max:50',
                'offset' => 'nullable|integer|min:0|max:4999',
            ], [
                'query.required' => 'El parámetro query es requerido',
                'query.string' => 'El parámetro query debe ser una cadena de texto',
                'query.max' => 'El parámetro query no puede exceder 50 caracteres',
                'limit.integer' => 'El parámetro limit debe ser numérico',
                'limit.min' => 'El parámetro limit debe ser al menos 1',
                'limit.max' => 'El parámetro limit no puede exceder 50',
                'offset.integer' => 'El parámetro offset debe ser numérico',
                'offset.min' => 'El parámetro offset debe ser al menos 0',
                'offset.max' => 'El parámetro offset no puede exceder 4999',
            ]);

            // Crear DTO (con casting a int)
            $dto = new SearchMediaDTO(
                query: $validated['query'],
                limit: isset($validated['limit']) ? (int) $validated['limit'] : null,
                offset: isset($validated['offset']) ? (int) $validated['offset'] : null,
            );

            // Ejecutar caso de uso
            $result = $this->searchMedia->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'meta' => $result['meta'],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);

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
