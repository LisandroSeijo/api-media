<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Api\Auth\Application\UseCases\RegisterUser;
use Api\Auth\Application\DTOs\RegisterUserDTO;
use Api\Auth\Domain\Specifications\RegisterUserSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use DomainException;
use Exception;
use OpenApi\Attributes as OA;

class PostRegisterUserController extends Controller
{
    public function __construct(
        private readonly RegisterUser $registerUser,
        private readonly RegisterUserSpecification $registerSpec
    ) {}

    #[OA\Post(
        path: '/api/v1/register',
        tags: ['Authentication'],
        summary: 'Registro de nuevo usuario (Solo Admin)',
        description: 'Registra un nuevo usuario en el sistema. Requiere privilegios de administrador.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Usuario registrado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                                new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'USER'),
                                new OA\Property(property: 'created_at', type: 'string', example: '2026-03-20 10:30:00')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error de dominio (email duplicado, etc.)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'El email ya está registrado')
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
                response: 403,
                description: 'Sin permisos de administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden. Admin privileges required.')
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
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno del servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Registration failed'),
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $name = $request->input('name', '');
            $email = $request->input('email', '');
            $password = $request->input('password', '');

            if ($this->registerSpec->hasErrors($name, $email, $password)) {
                $errors = $this->registerSpec->getValidationErrors($name, $email, $password);
                
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

            $dto = new RegisterUserDTO(
                name: $name,
                email: $email,
                password: $password
            );

            $user = $this->registerUser->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail()->value(),
                    'role' => $user->getRole()->value,
                    'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ]
            ], 201);

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
