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

class PostLoginUserController extends Controller
{
    public function __construct(
        private readonly LoginUser $loginUser,
        private readonly LoginCredentialsSpecification $credentialsSpec
    ) {}

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
