<?php

namespace Src\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Src\Auth\Application\UseCases\RegisterUser;
use Src\Auth\Application\UseCases\LoginUser;
use Src\Auth\Application\UseCases\LogoutUser;
use Src\Auth\Application\DTOs\RegisterUserDTO;
use Src\Auth\Application\DTOs\LoginDTO;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DomainException;
use Exception;

/**
 * Auth Controller
 * 
 * Controlador HTTP para autenticación.
 * Delgado: solo valida, crea DTOs, llama use cases y retorna respuestas JSON.
 */
class AuthController extends Controller
{
    /**
     * @param RegisterUser $registerUser
     * @param LoginUser $loginUser
     * @param LogoutUser $logoutUser
     */
    public function __construct(
        private RegisterUser $registerUser,
        private LoginUser $loginUser,
        private LogoutUser $logoutUser
    ) {}

    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
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
            $userModel = \Src\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel::find($user->getId());
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

    /**
     * Login user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
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

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Ejecutar Use Case
            $this->logoutUser->execute($request);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        try {
            $userModel = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'email' => $userModel->email,
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
