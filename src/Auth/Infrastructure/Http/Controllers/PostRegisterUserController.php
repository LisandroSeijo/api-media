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

class PostRegisterUserController extends Controller
{
    public function __construct(
        private readonly RegisterUser $registerUser,
        private readonly RegisterUserSpecification $registerSpec
    ) {}

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
