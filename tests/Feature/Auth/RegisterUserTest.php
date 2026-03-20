<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Tests\PassportTestCase;

class RegisterUserTest extends PassportTestCase
{

    public function test_admin_can_register_new_user(): void
    {
        $admin = UserModel::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $token = $admin->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'role' => 'user',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'user',
        ]);
    }

    public function test_regular_user_cannot_register_new_user(): void
    {
        $user = UserModel::factory()->create([
            'role' => 'user',
        ]);

        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Forbidden. Admin privileges required.',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_unauthenticated_user_cannot_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_registration_fails_with_invalid_email(): void
    {
        $admin = UserModel::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        UserModel::factory()->create(['email' => 'existing@example.com']);

        $admin = UserModel::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'Email already registered',
            ]);
    }

    public function test_registration_fails_with_missing_fields(): void
    {
        $admin = UserModel::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/register', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_fails_with_short_password(): void
    {
        $admin = UserModel::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('TestToken')->accessToken;

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
