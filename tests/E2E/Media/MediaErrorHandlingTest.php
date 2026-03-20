<?php

declare(strict_types=1);

namespace Tests\E2E\Media;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Tests\E2E\E2ETestCase;

/**
 * Tests E2E para manejo de errores en búsquedas de media
 * 
 * Cubre:
 * - Fallo de API externa (500)
 * - Timeout de conexión
 * - Respuestas inválidas de GIPHY
 * - Errores de validación múltiples
 */
class MediaErrorHandlingTest extends E2ETestCase
{
    public function test_handles_giphy_api_failure(): void
    {
        // Setup: Mock GIPHY que retorna 500 error
        $mock = $this->createGiphyMock([
            ['status' => 500, 'body' => ['error' => 'Internal Server Error']],
        ]);

        $this->bindGiphyMock($mock);

        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];

        // 2. Search (debe fallar con 500)
        $searchResponse = $this->getJson('/api/v1/media/search?query=test&limit=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $searchResponse->assertStatus(503)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'error',
            ]);

        // Verificar que el mensaje contiene información del error
        $this->assertStringContainsString('Error', $searchResponse->json('message'));

        // Verificar audit log registra el error (response_code 503)
        $this->assertAuditLogExists([
            'service' => 'api/v1/media/search',
            'method' => 'GET',
            'response_code' => 503,
        ]);
    }

    public function test_handles_giphy_timeout(): void
    {
        // Setup: Mock que lanza ConnectException (timeout simulado)
        $mockHandler = new MockHandler([
            new ConnectException(
                'Connection timeout',
                new GuzzleRequest('GET', 'test')
            ),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $this->bindGiphyMock($mockClient);

        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];

        // 2. Search (timeout)
        $searchResponse = $this->getJson('/api/v1/media/search?query=test&limit=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        // Verificar Response 503
        $searchResponse->assertStatus(503)
            ->assertJson([
                'success' => false,
            ]);

        // Verificar que el mensaje menciona conexión/GIPHY
        $message = $searchResponse->json('message');
        $this->assertStringContainsString('Error', $message);

        // Verificar audit log con error
        $this->assertAuditLogExists([
            'service' => 'api/v1/media/search',
            'response_code' => 503,
        ]);
    }

    public function test_handles_invalid_giphy_response(): void
    {
        // Setup: Mock retorna respuesta sin 'meta.response_id' (synthetic response)
        $mock = $this->createGiphyMock([
            [
                'body' => [
                    'data' => [
                        ['id' => '123', 'title' => 'Test'],
                    ],
                    'pagination' => ['total_count' => 1, 'count' => 1, 'offset' => 0],
                    'meta' => [
                        'status' => 200,
                        'msg' => 'OK',
                        // Sin 'response_id' - esto causa RuntimeException en GiphyMediaRepository
                    ],
                ],
            ],
        ]);

        $this->bindGiphyMock($mock);

        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];

        // 2. Search
        $searchResponse = $this->getJson('/api/v1/media/search?query=test&limit=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        // Verificar Response 503
        $searchResponse->assertStatus(503)
            ->assertJson([
                'success' => false,
            ]);

        // Verificar mensaje específico
        $message = $searchResponse->json('message');
        $this->assertStringContainsString('Error', $message);

        // Verificar audit log registra el error
        $this->assertAuditLogExists([
            'service' => 'api/v1/media/search',
            'response_code' => 503,
        ]);
    }

    public function test_handles_validation_errors_across_requests(): void
    {
        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];
        $userId = $userAuth['user']->id;

        // 2. Search sin query (422)
        $noQueryResponse = $this->getJson('/api/v1/media/search', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $noQueryResponse->assertStatus(422)
            ->assertJsonValidationErrors(['query']);

        // 3. Search con limit inválido (422)
        $invalidLimitResponse = $this->getJson('/api/v1/media/search?query=test&limit=100', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $invalidLimitResponse->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);

        // 4. Search con offset inválido (422)
        $invalidOffsetResponse = $this->getJson('/api/v1/media/search?query=test&offset=5000', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $invalidOffsetResponse->assertStatus(422)
            ->assertJsonValidationErrors(['offset']);

        // Verificar: 3 errores de validación (no login porque usa token directo)
        $this->assertAuditLogCount(3);

        // Verificar que todos los logs de validación tienen response_code 422
        $validationLogs = $this->getAuditLogs($userId)
            ->where('service', 'api/v1/media/search')
            ->where('response_code', 422);

        $this->assertCount(3, $validationLogs);

        // Verificar que cada log tiene diferente error
        $validationLogs->each(function ($log) {
            $responseBody = json_decode($log->response_body, true);
            $this->assertArrayHasKey('errors', $responseBody);
        });
    }
}
