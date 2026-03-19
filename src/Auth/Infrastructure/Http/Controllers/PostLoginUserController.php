<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Api\Auth\Application\UseCases\LoginUser;
use Api\Auth\Application\DTOs\LoginDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use DomainException;
use Exception;

/**
 * Single Action Controller para login de usuario
 */
class PostLoginUserController extends Controller
{
    public function __construct(
        private readonly LoginUser $loginUser
    ) {}

    /**
     * Inicia sesión de usuario
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Validar request
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Crear DTO
            $dto = new LoginDTO(
                email: $validated['email'],
                password: $validated['password']
            );

            // Ejecutar Use Case
            $result = $this->loginUser->execute($dto);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->getId(),
                        'name' => $result['user']->getName(),
                        'email' => $result['user']->getEmail()->value(),
                    ],
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
