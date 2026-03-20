<?php

declare(strict_types=1);

namespace Api\Audit\Infrastructure\Listeners;

use Api\Audit\Application\DTOs\CreateAuditLogDTO;
use Api\Audit\Application\UseCases\CreateAuditLog;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Log;

/**
 * Listener para el evento RequestHandled
 * 
 * Se ejecuta automáticamente después de cada request HTTP.
 * Registra la petición en el sistema de auditoría.
 */
class LogRequestAudited
{
    public function __construct(
        private CreateAuditLog $createAuditLog
    ) {}

    /**
     * Handle the event.
     */
    public function handle(RequestHandled $event): void
    {
        try {
            // Obtener request y response del evento
            $request = $event->request;
            $response = $event->response;

            // Filtrar rutas que no queremos auditar
            if ($this->shouldSkipAudit($request->path())) {
                return;
            }

            // Crear DTO
            $dto = new CreateAuditLogDTO(
                userId: $request->user()?->id,
                service: $request->path(),
                method: $request->method(),
                requestBody: $this->sanitizeRequestBody($request->all()),
                responseCode: $response->getStatusCode(),
                responseBody: $this->getResponseBody($response),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            // Guardar en auditoría
            $this->createAuditLog->execute($dto);

        } catch (\Exception $e) {
            // No fallar si falla la auditoría
            Log::error('Audit logging failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Determina si una ruta debe ser excluida de auditoría
     */
    private function shouldSkipAudit(string $path): bool
    {
        $skipPaths = [
            'api/v1/health',  // Health check
            '_debugbar',      // Laravel Debugbar
            'telescope',      // Laravel Telescope
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitiza el body del request para no guardar información sensible
     */
    private function sanitizeRequestBody(array $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        // Ocultar campos sensibles
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }

        return $data;
    }

    /**
     * Obtiene el body de la respuesta
     */
    private function getResponseBody($response): ?array
    {
        $content = $response->getContent();
        
        if (empty($content)) {
            return null;
        }

        // Intentar decodificar como JSON
        $decoded = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Retornar el response completo sin límites
            return $decoded;
        }

        // Si no es JSON válido, guardar como raw
        return ['raw' => $content];
    }

    /**
     * Limita el tamaño del array para evitar respuestas muy grandes
     * 
     * @deprecated Ya no se usa, guardamos todo
     */
    private function limitArraySize(array $data, int $maxLength): array
    {
        // Método deprecado, ahora guardamos todo sin límites
        return $data;
    }
}
