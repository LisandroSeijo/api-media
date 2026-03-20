<?php

declare(strict_types=1);

namespace Tests\E2E;

use Api\Auth\Domain\ValueObjects\Role;
use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository;
use Api\Shared\Domain\Services\CacheServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\PassportTestCase;

/**
 * Base class para tests End-to-End
 * 
 * Proporciona helpers reutilizables para:
 * - Mocking de Guzzle/GIPHY
 * - Mocking de Cache Service
 * - Login de usuarios y admins
 * - Verificación de audit logs
 * - Verificación de tokens revocados
 * - Generación de datos de prueba realistas
 */
abstract class E2ETestCase extends PassportTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock cache service para tests E2E
        $this->mockCacheService();
    }

    /**
     * Mockea el CacheServiceInterface para tests
     */
    protected function mockCacheService(): void
    {
        $cacheMock = Mockery::mock(CacheServiceInterface::class);
        $cacheMock->shouldReceive('has')->andReturn(false);
        $cacheMock->shouldReceive('get')->andReturn(null);
        $cacheMock->shouldReceive('put')->andReturn(null);
        
        $this->app->instance(CacheServiceInterface::class, $cacheMock);
    }

    /**
     * Crea un mock de Guzzle Client con respuestas predefinidas
     * 
     * @param array $responses Array de responses, cada uno con 'status', 'headers', 'body'
     * @return Client
     */
    protected function createGiphyMock(array $responses): Client
    {
        $mockResponses = array_map(function ($response) {
            return new Response(
                $response['status'] ?? 200,
                $response['headers'] ?? [],
                is_string($response['body'] ?? '') 
                    ? $response['body'] 
                    : json_encode($response['body'])
            );
        }, $responses);

        $mock = new MockHandler($mockResponses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * Bindea un mock de Guzzle al contenedor de Laravel
     * 
     * @param Client $mock
     * @return void
     */
    protected function bindGiphyMock(Client $mock): void
    {
        $this->app->when(GiphyMediaRepository::class)
            ->needs(Client::class)
            ->give(fn() => $mock);
    }

    /**
     * Verifica que existe un audit log con los criterios dados
     * 
     * @param array $criteria
     * @return void
     */
    protected function assertAuditLogExists(array $criteria): void
    {
        $this->assertDatabaseHas('audit_logs', $criteria);
    }

    /**
     * Verifica la cantidad exacta de audit logs en BD
     * 
     * @param int $expected
     * @return void
     */
    protected function assertAuditLogCount(int $expected): void
    {
        $this->assertDatabaseCount('audit_logs', $expected);
    }

    /**
     * Obtiene audit logs, opcionalmente filtrados por user_id
     * 
     * @param int|null $userId
     * @return Collection
     */
    protected function getAuditLogs(?int $userId = null): Collection
    {
        $query = DB::table('audit_logs')->orderBy('created_at', 'asc');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return collect($query->get());
    }

    /**
     * Obtiene el último audit log
     * 
     * @return object|null
     */
    protected function getLatestAuditLog(): ?object
    {
        return DB::table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Verifica que un audit log contiene un valor en un campo
     * 
     * @param string $field
     * @param string $value
     * @return void
     */
    protected function assertAuditLogContains(string $field, string $value): void
    {
        $log = $this->getLatestAuditLog();
        $this->assertNotNull($log, 'No audit log found');

        // Si el campo es JSON (request_body, response_body), decodificar
        $fieldValue = is_string($log->$field)
            ? json_decode($log->$field, true)
            : $log->$field;

        $this->assertStringContainsString($value, json_encode($fieldValue));
    }

    /**
     * Crea un usuario y genera token directamente (Passport)
     * 
     * @return array ['user' => UserModel, 'token' => string]
     */
    protected function loginAsUser(): array
    {
        $user = UserModel::factory()->create([
            'role' => Role::USER,
        ]);

        $token = $user->createToken('E2E Test Token')->accessToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Crea un admin y genera token directamente (Passport)
     * 
     * @return array ['user' => UserModel, 'token' => string]
     */
    protected function loginAsAdmin(): array
    {
        $admin = UserModel::factory()->create([
            'role' => Role::ADMIN,
        ]);

        $token = $admin->createToken('E2E Admin Test Token')->accessToken;

        return [
            'user' => $admin,
            'token' => $token,
        ];
    }

    /**
     * Hace login HTTP real y retorna token
     * 
     * @param string $email
     * @param string $password
     * @return array ['response' => TestResponse, 'token' => string]
     */
    protected function performLogin(string $email, string $password): array
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertStatus(200);

        return [
            'response' => $response,
            'token' => $response->json('data.access_token'),
        ];
    }

    /**
     * Verifica que un token fue revocado en la BD
     * 
     * @param string $token
     * @return void
     */
    protected function assertTokenRevoked(string $token): void
    {
        // Buscar el token en la tabla oauth_access_tokens
        $accessToken = DB::table('oauth_access_tokens')
            ->where('id', $token)
            ->first();

        $this->assertNotNull($accessToken, 'Token not found in database');
        $this->assertTrue((bool)$accessToken->revoked, 'Token is not revoked');
    }

    /**
     * Crea una respuesta realista de búsqueda de GIPHY
     * 
     * @param string $query
     * @param int $count
     * @param int $offset
     * @return array
     */
    protected function createGiphySearchResponse(
        string $query,
        int $count = 5,
        int $offset = 0
    ): array {
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $id = 'test_' . uniqid();
            $items[] = [
                'id' => $id,
                'title' => ucfirst($query) . ' GIF ' . ($i + 1),
                'url' => "https://giphy.com/gifs/{$id}",
                'rating' => 'g',
                'username' => 'testuser',
                'images' => [
                    'original' => [
                        'url' => "https://media.giphy.com/media/{$id}/giphy.gif",
                        'width' => '480',
                        'height' => '270',
                    ],
                    'preview_gif' => [
                        'url' => "https://media.giphy.com/media/{$id}/200.gif",
                        'width' => '200',
                        'height' => '112',
                    ],
                ],
            ];
        }

        return [
            'data' => $items,
            'pagination' => [
                'total_count' => 1000,
                'count' => $count,
                'offset' => $offset,
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test_response_' . uniqid(),
            ],
        ];
    }

    /**
     * Crea una respuesta realista de GIPHY para obtener por ID
     * 
     * @param string $id
     * @return array
     */
    protected function createGiphyByIdResponse(string $id): array
    {
        return [
            'data' => [
                'id' => $id,
                'title' => 'Test GIF ' . $id,
                'url' => "https://giphy.com/gifs/{$id}",
                'rating' => 'g',
                'username' => 'testuser',
                'images' => [
                    'original' => [
                        'url' => "https://media.giphy.com/media/{$id}/giphy.gif",
                        'width' => '480',
                        'height' => '270',
                    ],
                    'preview_gif' => [
                        'url' => "https://media.giphy.com/media/{$id}/200.gif",
                        'width' => '200',
                        'height' => '112',
                    ],
                ],
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test_response_' . uniqid(),
            ],
        ];
    }
}
