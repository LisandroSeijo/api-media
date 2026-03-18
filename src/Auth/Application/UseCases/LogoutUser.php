<?php

namespace Api\Auth\Application\UseCases;

use Illuminate\Http\Request;

/**
 * Logout User Use Case
 * 
 * Caso de uso para cerrar sesión revocando el token actual.
 */
class LogoutUser
{
    /**
     * Ejecuta el caso de uso de logout
     * 
     * @param Request $request
     * @return void
     */
    public function execute(Request $request): void
    {
        // Revocar el token actual usando Passport
        $request->user()->token()->revoke();
    }
}
