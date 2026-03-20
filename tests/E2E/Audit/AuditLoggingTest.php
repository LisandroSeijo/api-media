<?php

declare(strict_types=1);

namespace Tests\E2E\Audit;

use Api\Audit\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use Tests\E2E\E2ETestCase;

/**
 * Tests E2E para verificar el sistema de audit logging
 * 
 * Cubre:
 * - Captura de request/response completos (sin truncamiento)
 * - Sanitización de datos sensibles (passwords, tokens)
 * - Tracking de journey completo de usuario
 * - Exclusión de endpoints específicos (health check)
 */
class AuditLoggingTest extends E2ETestCase
{
    public function test_audit_logs_capture_complete_request_response(): void
    {
        // Setup: Mock GIPHY con respuesta grande (>5000 chars)
        $largeResponse = $this->createGiphySearchResponse('test', 50, 0);
        
        $mock = $this->createGiphyMock([
            ['body' => $largeResponse],
        ]);

        $this->bindGiphyMock($mock);

        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];

        // 2. Search (respuesta grande)
        $searchResponse = $this->getJson('/api/v1/media/search?query=test&limit=50', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $searchResponse->assertStatus(200);

        // Verificar audit log existe
        $this->assertAuditLogExists([
            'service' => 'api/v1/media/search',
            'method' => 'GET',
            'response_code' => 200,
        ]);

        // Obtener el audit log de la búsqueda
        $searchLog = AuditLogModel::where('service', 'api/v1/media/search')->first();
        $this->assertNotNull($searchLog);

        // Verificar response_body NO está truncado (LONGTEXT)
        $responseBody = $searchLog->response_body;
        $this->assertIsArray($responseBody);
        $this->assertArrayHasKey('data', $responseBody);
        
        $responseBodyJson = json_encode($responseBody);
        $this->assertGreaterThan(5000, strlen($responseBodyJson), 'Response should be larger than 5000 chars');

        // Verificar request_body contiene todos los parámetros
        $requestBody = $searchLog->request_body;
        $this->assertIsArray($requestBody);
        $this->assertArrayHasKey('query', $requestBody);
        $this->assertEquals('test', $requestBody['query']);
        $this->assertArrayHasKey('limit', $requestBody);
        $this->assertEquals('50', $requestBody['limit']);
    }

    public function test_audit_logs_sanitize_sensitive_data(): void
    {
        // 1. Admin registra usuario (con password)
        $adminAuth = $this->loginAsAdmin();
        $adminToken = $adminAuth['token'];

        $newUserData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'supersecretpassword123',
            'password_confirmation' => 'supersecretpassword123',
        ];

        $registerResponse = $this->postJson('/api/v1/register', $newUserData, [
            'Authorization' => 'Bearer ' . $adminToken,
        ]);

        $registerResponse->assertStatus(201);

        // 2. Usuario hace login (con password)
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $newUserData['email'],
            'password' => $newUserData['password'],
        ]);

        $loginResponse->assertStatus(200);

        // Verificar audit logs existen
        $this->assertAuditLogCount(2); // register, user login (no admin login porque usa token directo)

        // Verificar que passwords están sanitizados en register
        $registerLog = AuditLogModel::where('service', 'api/v1/register')->first();
        $this->assertNotNull($registerLog);
        
        $registerRequestBody = $registerLog->request_body;
        $this->assertIsArray($registerRequestBody);
        $this->assertArrayHasKey('password', $registerRequestBody);
        $this->assertEquals('***REDACTED***', $registerRequestBody['password']);
        $this->assertEquals('***REDACTED***', $registerRequestBody['password_confirmation']);

        // Verificar que passwords están sanitizados en login
        $loginLog = AuditLogModel::where('service', 'api/v1/login')
            ->orderBy('created_at', 'desc')
            ->first();
        $this->assertNotNull($loginLog);
        
        $loginRequestBody = $loginLog->request_body;
        $this->assertIsArray($loginRequestBody);
        $this->assertArrayHasKey('password', $loginRequestBody);
        $this->assertEquals('***REDACTED***', $loginRequestBody['password']);

        // Verificar que el email NO está sanitizado
        $this->assertEquals($newUserData['email'], $loginRequestBody['email']);
    }

    public function test_audit_logs_track_user_journey(): void
    {
        // Setup mock GIPHY
        $mock = $this->createGiphyMock([
            ['body' => $this->createGiphySearchResponse('cats', 5, 0)],
            ['body' => $this->createGiphyByIdResponse('test123')],
            ['body' => $this->createGiphySearchResponse('dogs', 5, 0)],
        ]);

        $this->bindGiphyMock($mock);

        // 1. Login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];
        $userId = $userAuth['user']->id;

        // 2. Search "cats"
        $this->getJson('/api/v1/media/search?query=cats&limit=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ])->assertStatus(200);

        // 3. Get by ID
        $this->getJson('/api/v1/media/test123', [
            'Authorization' => 'Bearer ' . $userToken,
        ])->assertStatus(200);

        // 4. Search "dogs"
        $this->getJson('/api/v1/media/search?query=dogs&limit=5', [
            'Authorization' => 'Bearer ' . $userToken,
        ])->assertStatus(200);

        // 5. Logout
        $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $userToken,
        ])->assertStatus(200);

        // Verificar: 4 audit logs en orden cronológico (no login porque usa token directo)
        $userLogs = $this->getAuditLogs($userId);
        $this->assertCount(4, $userLogs);

        // Verificar que todos tienen mismo user_id
        $userLogs->each(function ($log) use ($userId) {
            $this->assertEquals($userId, $log->user_id);
        });

        // Verificar timestamps en orden ascendente
        $timestamps = $userLogs->pluck('created_at')->map(fn($ts) => strtotime($ts));
        $sortedTimestamps = $timestamps->sort()->values();
        $this->assertEquals($sortedTimestamps->toArray(), $timestamps->toArray());

        // Verificar IP address consistente
        $ipAddresses = $userLogs->pluck('ip_address')->unique();
        $this->assertCount(1, $ipAddresses, 'All logs should have same IP address');

        // Verificar servicios en orden esperado (sin login)
        $services = $userLogs->pluck('service')->toArray();
        $this->assertEquals([
            'api/v1/media/search',
            'api/v1/media/test123',
            'api/v1/media/search',
            'api/v1/logout',
        ], $services);
    }

    public function test_health_endpoint_not_audited(): void
    {
        // Registrar cuántos logs hay antes
        $logCountBefore = AuditLogModel::count();

        // 1. GET /api/v1/health
        $healthResponse = $this->getJson('/api/v1/health');

        $healthResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'API is running',
            ]);

        // Verificar: NO existe audit log para health check
        $logCountAfter = AuditLogModel::count();
        $this->assertEquals($logCountBefore, $logCountAfter, 'Health endpoint should not be audited');

        // Verificar explícitamente que no hay log de health
        $healthLog = AuditLogModel::where('service', 'api/v1/health')->first();
        $this->assertNull($healthLog, 'Health check should not create audit log');
    }
}
