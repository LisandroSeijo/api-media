<?php

declare(strict_types=1);

namespace Api\Auth\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para asegurar que el usuario autenticado es ADMIN
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Verificar que el usuario esté autenticado
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Verificar que el usuario tenga rol admin usando el método isAdmin()
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Admin privileges required.'
            ], 403);
        }

        return $next($request);
    }
}
