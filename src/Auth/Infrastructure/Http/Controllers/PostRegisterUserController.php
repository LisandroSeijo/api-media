<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Api\Auth\Application\UseCases\RegisterUser;
use Api\Auth\Application\DTOs\RegisterUserDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use DomainException;
use Exception;

/**
 * Single Action Controller para registrar usuario
 */
class PostRegisterUserController extends Controller
{
    public function __construct(
        private readonly RegisterUser $registerUser
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Validar request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);

            // Crear DTO
            $dto = new RegisterUserDTO(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password']
            );

            // Ejecutar Use Case
            $user = $this->registerUser->execute($dto);

            // Generar token
            $userModel = \Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel::find($user->getId());
            $token = $userModel->createToken('auth_token')->accessToken;

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail()->value(),
                        'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

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
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
