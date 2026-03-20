<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Laravel API REST - OAuth2 & GIPHY Integration',
    description: 'API REST construida con Laravel 12, OAuth2.0 (Passport), Doctrine ORM y Redis Cache, siguiendo arquitectura hexagonal.'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Ingrese el token JWT en formato: Bearer {token}'
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'Endpoints de autenticación y gestión de usuarios'
)]
#[OA\Tag(
    name: 'Media',
    description: 'Endpoints para búsqueda de GIFs a través de GIPHY API (con Redis Cache)'
)]
#[OA\Tag(
    name: 'System',
    description: 'Endpoints de sistema y health check'
)]
abstract class Controller
{
    //
}
