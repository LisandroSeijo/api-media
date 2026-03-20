<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Attributes as OA;

/**
 * Esquemas comunes para respuestas de la API
 */
class CommonSchemas
{
    //
}

/**
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     title="Success Response",
 *     description="Respuesta exitosa estándar de la API",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Operación exitosa"
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object"
 *     )
 * )
 */
class SuccessResponse {}

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Error Response",
 *     description="Respuesta de error estándar de la API",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Error en la operación"
 *     ),
 *     @OA\Property(
 *         property="error",
 *         type="string",
 *         example="Detalle del error"
 *     )
 * )
 */
class ErrorResponse {}

/**
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     title="Validation Error Response",
 *     description="Respuesta de error de validación (422)",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Error de validación"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties={
 *             "type": "array",
 *             "items": {"type": "string"}
 *         },
 *         example={"email": {"El campo email es requerido."}, "password": {"La contraseña debe tener al menos 6 caracteres."}}
 *     )
 * )
 */
class ValidationErrorResponse {}

/**
 * @OA\Schema(
 *     schema="UnauthorizedResponse",
 *     title="Unauthorized Response",
 *     description="Respuesta cuando no está autenticado (401)",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Unauthenticated."
 *     )
 * )
 */
class UnauthorizedResponse {}

/**
 * @OA\Schema(
 *     schema="ForbiddenResponse",
 *     title="Forbidden Response",
 *     description="Respuesta cuando no tiene permisos (403)",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Forbidden. Admin privileges required."
 *     )
 * )
 */
class ForbiddenResponse {}

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="Modelo de usuario",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"ADMIN", "USER"}, example="USER"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-03-20 10:30:00")
 * )
 */
class User {}

/**
 * @OA\Schema(
 *     schema="MediaItem",
 *     title="Media Item",
 *     description="Item de media (GIF) de GIPHY",
 *     @OA\Property(property="id", type="string", example="abc123xyz"),
 *     @OA\Property(property="title", type="string", example="Funny Cat GIF"),
 *     @OA\Property(property="url", type="string", format="url", example="https://giphy.com/gifs/abc123xyz"),
 *     @OA\Property(property="rating", type="string", example="g"),
 *     @OA\Property(property="username", type="string", example="catlovers"),
 *     @OA\Property(
 *         property="images",
 *         type="object",
 *         @OA\Property(
 *             property="original",
 *             type="object",
 *             @OA\Property(property="url", type="string", example="https://media.giphy.com/media/abc123/giphy.gif")
 *         ),
 *         @OA\Property(
 *             property="preview_gif",
 *             type="object",
 *             @OA\Property(property="url", type="string", example="https://media.giphy.com/media/abc123/200.gif")
 *         )
 *     )
 * )
 */
class MediaItem {}

/**
 * @OA\Schema(
 *     schema="Pagination",
 *     title="Pagination",
 *     description="Información de paginación",
 *     @OA\Property(property="total_count", type="integer", example=1000),
 *     @OA\Property(property="count", type="integer", example=25),
 *     @OA\Property(property="offset", type="integer", example=0)
 * )
 */
class Pagination {}

/**
 * @OA\Schema(
 *     schema="Meta",
 *     title="Meta",
 *     description="Metadatos de la respuesta",
 *     @OA\Property(property="status", type="integer", example=200),
 *     @OA\Property(property="msg", type="string", example="OK"),
 *     @OA\Property(property="response_id", type="string", example="abc123xyz")
 * )
 */
class Meta {}
