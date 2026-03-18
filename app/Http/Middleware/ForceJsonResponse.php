<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force JSON Response Middleware
 * 
 * Fuerza a que todas las respuestas de la API sean en formato JSON,
 * incluso si el cliente no envía el header Accept: application/json
 */
class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forzar que el request acepte JSON
        $request->headers->set('Accept', 'application/json');
        
        return $next($request);
    }
}
