<?php

declare(strict_types=1);

namespace Tests\E2E\Media;

use Tests\E2E\E2ETestCase;

/**
 * Tests E2E para flujos completos de búsqueda de media
 * 
 * Cubre:
 * - Flujo completo: Login -> Búsquedas múltiples -> GetById -> Logout
 * - Búsquedas con paginación
 * - Usuario no autenticado no puede buscar
 */
class MediaSearchFlowTest extends E2ETestCase
{
    public function test_complete_media_search_flow(): void
    {
        // Setup: Mock GIPHY con 3 respuestas (2 searches, 1 getById)
        $mock = $this->createGiphyMock([
            ['body' => $this->createGiphySearchResponse('cats', 5, 0)],
            ['body' => $this->createGiphySearchResponse('dogs', 10, 5)],
            ['body' => $this->createGiphyByIdResponse('abc123')],
        ]);

        $this->bindGiphyMock($mock);

        // 1. Usuario hace login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];
        $userId = $userAuth['user']->id;

        // 2. Usuario busca "cats" (limit=5)
        $catsResponse = $this->getJson('/api/v1/media/search?query=cats&limit=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $catsResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title', 'url', 'rating', 'username', 'images'],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
            ]);

        $this->assertCount(5, $catsResponse->json('data'));

        // 3. Usuario busca "dogs" (limit=10, offset=5)
        $dogsResponse = $this->getJson('/api/v1/media/search?query=dogs&limit=10&offset=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $dogsResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(10, $dogsResponse->json('data'));

        // 4. Usuario obtiene media por ID "abc123"
        $byIdResponse = $this->getJson('/api/v1/media/abc123', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $byIdResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
                'data' => [
                    'id' => 'abc123',
                ],
            ]);

        // 5. Usuario hace logout
        $logoutResponse = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $logoutResponse->assertStatus(200);

        // Verificaciones
        // Esperamos: 2 searches, getById, logout = 4 audit logs (no login porque usa token directo)
        $this->assertAuditLogCount(4);

        // Verificar que todos los logs son del mismo usuario
        $userLogs = $this->getAuditLogs($userId);
        $this->assertCount(4, $userLogs);

        // Verificar que los logs tienen request_body y response_body
        $searchLog = $userLogs->firstWhere('service', 'api/v1/media/search');
        $this->assertNotNull($searchLog);
        
        $requestBody = json_decode($searchLog->request_body, true);
        $this->assertArrayHasKey('query', $requestBody);
        $this->assertEquals('cats', $requestBody['query']);
    }

    public function test_media_search_with_pagination(): void
    {
        // Setup: Mock con 3 respuestas de paginación
        $mock = $this->createGiphyMock([
            ['body' => $this->createGiphySearchResponse('funny', 5, 0)],
            ['body' => $this->createGiphySearchResponse('funny', 5, 5)],
            ['body' => $this->createGiphySearchResponse('funny', 5, 10)],
        ]);

        $this->bindGiphyMock($mock);

        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];
        $userId = $userAuth['user']->id;

        // 2. Search página 1 (limit=5, offset=0)
        $page1Response = $this->getJson('/api/v1/media/search?query=funny&limit=5&offset=0', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $page1Response->assertStatus(200);
        $this->assertCount(5, $page1Response->json('data'));

        // 3. Search página 2 (limit=5, offset=5)
        $page2Response = $this->getJson('/api/v1/media/search?query=funny&limit=5&offset=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $page2Response->assertStatus(200);
        $this->assertCount(5, $page2Response->json('data'));

        // 4. Search página 3 (limit=5, offset=10)
        $page3Response = $this->getJson('/api/v1/media/search?query=funny&limit=5&offset=10', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $page3Response->assertStatus(200);
        $this->assertCount(5, $page3Response->json('data'));

        // Verificar audit logs con diferentes offsets
        $this->assertAuditLogCount(3); // 3 searches (no login porque usa token directo)

        $searchLogs = $this->getAuditLogs($userId)
            ->where('service', 'api/v1/media/search');

        $this->assertCount(3, $searchLogs);

        // Verificar que cada log tiene el offset correcto
        $offsets = $searchLogs->map(function ($log) {
            $requestBody = json_decode($log->request_body, true);
            return $requestBody['offset'] ?? 0;
        })->values()->toArray();

        $this->assertEquals([0, 5, 10], $offsets);
    }

    public function test_unauthenticated_user_cannot_search(): void
    {
        // 1. Hacer request sin token
        $response = $this->getJson('/api/v1/media/search?query=cats');

        // 2. Verificar 401
        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Unauthenticated.',
            ]);

        // 3. Verificar audit log con user_id = null
        $this->assertAuditLogCount(1);

        $log = $this->getLatestAuditLog();
        $this->assertNull($log->user_id);
        $this->assertEquals(401, $log->response_code);
        $this->assertEquals('api/v1/media/search', $log->service);
    }
}
