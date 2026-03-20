<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Api\Auth\Application\UseCases\LogoutUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class PostLogoutUserController extends Controller
{
    public function __construct(
        private readonly LogoutUser $logoutUser
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->logoutUser->execute($request);

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
