<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Api\Auth\Application\UseCases\LoginUser;
use Api\Auth\Application\DTOs\LoginDTO;
use Api\Auth\Domain\Specifications\LoginCredentialsSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use DomainException;
use Exception;
use OpenApi\Attributes as OA;

class PostLoginUserController extends Controller
{
    public function __construct(
        private readonly LoginUser $loginUser,
        private readonly LoginCredentialsSpecification $credentialsSpec
    ) {}

    #[OA\Post(
        path: '/api/v1/login',
        tags: ['Authentication'],
        summary: 'Login de usuario',
        description: 'Autentica un usuario y retorna un token JWT de acceso',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@test.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login exitoso'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', example: '2027-03-20 10:30:00')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales inválidas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Credenciales inválidas')
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
            $email = $request->input('email', '');
            $password = $request->input('password', '');

            if ($this->credentialsSpec->hasErrors($email, $password)) {
                $errors = $this->credentialsSpec->getValidationErrors($email, $password);
                
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

            $dto = new LoginDTO(
                email: $email,
                password: $password
            );

            $result = $this->loginUser->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                    'expires_at' => $result['expires_at'],
                ]
            ], 200);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
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
