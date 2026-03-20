<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Tests\PassportTestCase;

class LoginUserTest extends PassportTestCase
{

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = UserModel::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ]);
    }

    public function test_admin_can_login(): void
    {
        $admin = UserModel::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        UserModel::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Credenciales inválidas',
            ]);
    }

    public function test_login_fails_with_non_existent_user(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Credenciales inválidas',
            ]);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = UserModel::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ]);
    }
}
