<?php

declare(strict_types=1);

namespace Api\Media\Infrastructure\Http\Controllers;

use Api\Media\Application\DTOs\GetMediaByIdDTO;
use Api\Media\Application\UseCases\GetMediaById;
use Api\Media\Domain\Exceptions\EntityNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Single Action Controller para obtener un media por ID
 */
class GetMediaByIdController extends Controller
{
    public function __construct(
        private readonly GetMediaById $getMediaById
    ) {}

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
