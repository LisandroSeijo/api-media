<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Api\Shared\Domain\Services\CacheServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\PassportTestCase;

class SearchMediaTest extends PassportTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock cache service para tests
        $cacheMock = Mockery::mock(CacheServiceInterface::class);
        $cacheMock->shouldReceive('has')->andReturn(false);
        $cacheMock->shouldReceive('get')->andReturn(null);
        $cacheMock->shouldReceive('put')->andReturn(null);
        
        $this->app->instance(CacheServiceInterface::class, $cacheMock);
    }

    public function test_authenticated_user_can_search_media(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $mockResponse = [
            'data' => [
                [
                    'id' => '123',
                    'title' => 'Funny Cat',
                    'url' => 'https://giphy.com/gifs/123',
                    'rating' => 'g',
                    'username' => 'testuser',
                    'images' => [
                        'original' => ['url' => 'https://media.giphy.com/media/123/giphy.gif'],
                        'preview_gif' => ['url' => 'https://media.giphy.com/media/123/200.gif'],
                    ],
                ],
            ],
            'pagination' => [
                'total_count' => 100,
                'count' => 1,
                'offset' => 0,
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test123',
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $this->app->when(\Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository::class)
            ->needs(Client::class)
            ->give(function () use ($mockClient) {
                return $mockClient;
            });

        $response = $this->getJson('/api/v1/media/search?query=cats&limit=1', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'url',
                        'rating',
                        'username',
                        'images',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
            ]);
    }

    public function test_search_fails_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/media/search?query=cats');

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_search_fails_without_query_parameter(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->getJson('/api/v1/media/search', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    public function test_search_fails_with_invalid_limit(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->getJson('/api/v1/media/search?query=cats&limit=100', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    public function test_search_fails_with_invalid_offset(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->getJson('/api/v1/media/search?query=cats&offset=5000', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['offset']);
    }

    public function test_search_with_custom_limit_and_offset(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $mockResponse = [
            'data' => [],
            'pagination' => [
                'total_count' => 100,
                'count' => 0,
                'offset' => 10,
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test456',
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $this->app->when(\Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository::class)
            ->needs(Client::class)
            ->give(function () use ($mockClient) {
                return $mockClient;
            });

        $response = $this->getJson('/api/v1/media/search?query=cats&limit=5&offset=10', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_get_media_by_id_returns_media_item(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $mockResponse = [
            'data' => [
                'id' => '123',
                'title' => 'Funny Cat',
                'url' => 'https://giphy.com/gifs/123',
                'rating' => 'g',
                'username' => 'testuser',
                'images' => [
                    'original' => ['url' => 'https://media.giphy.com/media/123/giphy.gif'],
                    'preview_gif' => ['url' => 'https://media.giphy.com/media/123/200.gif'],
                ],
            ],
            'meta' => [
                'status' => 200,
                'msg' => 'OK',
                'response_id' => 'test123',
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Bind el mock client al repositorio
        $this->app->when(\Api\Media\Infrastructure\Persistence\Http\GiphyMediaRepository::class)
            ->needs(Client::class)
            ->give(function () use ($mockClient) {
                return $mockClient;
            });

        $response = $this->getJson('/api/v1/media/123', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Media encontrado exitosamente',
                'data' => [
                    'id' => '123',
                    'title' => 'Funny Cat',
                ],
            ]);
    }

    public function test_get_media_by_id_fails_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/media/123');

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Unauthenticated.',
            ]);
    }
}
