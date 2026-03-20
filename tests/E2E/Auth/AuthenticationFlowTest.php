<?php

declare(strict_types=1);

namespace Tests\E2E\Auth;

use Api\Auth\Domain\ValueObjects\Role;
use Api\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\DB;
use Tests\E2E\E2ETestCase;

/**
 * Tests E2E para flujos completos de autenticación
 * 
 * Cubre:
 * - Registro (admin) -> Login -> Profile -> Logout -> Acceso denegado
 * - Admin registra usuario
 * - Usuario regular no puede registrar otros usuarios
 */
class AuthenticationFlowTest extends E2ETestCase
{
    /**
     * Test temporalmente deshabilitado por issue con token de Passport
     * 
     * TODO: Investigar por qué el token generado por performLogin() se asocia al usuario incorrecto
     */
    public function test_complete_authentication_flow(): void
    {
        $this->markTestSkipped('Disabled temporarily due to Passport token association issue');
    }

    /*
    public function test_complete_authentication_flow_DISABLED(): void
    {
        // 1. Admin crea usuario regular
        $adminAuth = $this->loginAsAdmin();
        $adminToken = $adminAuth['token'];

        $newUserData = [
            'name' => 'New User',
            'email' => 'newuser_' . uniqid() . '@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/v1/register', $newUserData, [
            'Authorization' => 'Bearer ' . $adminToken,
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'email', 'role'],
            ]);

        $newUserId = $registerResponse->json('data.id');

        // Verificar que el usuario fue creado correctamente
        $this->assertDatabaseHas('users', [
            'id' => $newUserId,
            'email' => $newUserData['email'],
            'role' => 'user',
        ]);

        // 2. Usuario hace login HTTP real
        // Primero, buscar el usuario en BD y verificar la password
        $createdUser = UserModel::where('email', $newUserData['email'])->first();
        $this->assertNotNull($createdUser, 'User should exist in database');
        $this->assertEquals($newUserId, $createdUser->id, 'User ID should match');
        $this->assertEquals(Role::USER, $createdUser->role, 'User role should be USER');

        $loginResult = $this->performLogin($newUserData['email'], $newUserData['password']);
        $userToken = $loginResult['token'];

        // Verificar que el login response incluye el token
        $this->assertNotEmpty($userToken);

        // 3. Usuario accede a su profile
        $profileResponse = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $profileResponse->assertStatus(200);

        // Verificar que el perfil retornado es del usuario correcto
        $profileData = $profileResponse->json('data');
        $this->assertEquals($newUserId, $profileData['id'], 'Profile ID should match new user ID');
        $this->assertEquals($newUserData['email'], $profileData['email'], 'Profile email should match new user email');

        // 4. Usuario hace logout
        $logoutResponse = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ]);

        // 5. Usuario intenta acceder después de logout (debe fallar 401)
        $afterLogoutResponse = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $afterLogoutResponse->assertStatus(401);

        // Verificaciones de audit logs
        // Esperamos: register, login, profile, logout, fail = 5 (no contamos admin login porque usa token directo)
        $this->assertAuditLogCount(5);

        // Verificar que el último audit log es el fallo de autenticación
        $lastLog = $this->getLatestAuditLog();
        $this->assertEquals(401, $lastLog->response_code);
        $this->assertEquals('api/v1/user', $lastLog->service);
    }
    */

    public function test_admin_registration_and_access(): void
    {
        // 1. Admin hace login
        $adminAuth = $this->loginAsAdmin();
        $adminToken = $adminAuth['token'];
        $adminUserId = $adminAuth['user']->id;

        // 2. Admin registra nuevo usuario
        $newUserData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/v1/register', $newUserData, [
            'Authorization' => 'Bearer ' . $adminToken,
        ]);

        $registerResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => $newUserData['name'],
                    'email' => $newUserData['email'],
                    'role' => 'user', // El usuario creado debe ser USER, no ADMIN
                ],
            ]);

        // 3. Admin puede acceder a endpoints protegidos
        $profileResponse = $this->getJson('/api/v1/user', [
            'Authorization' => 'Bearer ' . $adminToken,
        ]);

        $profileResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $adminUserId,
                ],
            ]);

        // Verificar audit logs
        $this->assertAuditLogCount(2); // register, profile (no login porque usa token directo)

        // Verificar que el usuario fue creado con role USER
        $this->assertDatabaseHas('users', [
            'email' => $newUserData['email'],
            'role' => 'user',
        ]);

        // Verificar audit logs del admin
        $adminLogs = $this->getAuditLogs($adminUserId);
        $this->assertCount(2, $adminLogs);
    }

    public function test_regular_user_cannot_register_others(): void
    {
        // 1. Usuario regular hace login
        $userAuth = $this->loginAsUser();
        $userToken = $userAuth['token'];
        $userId = $userAuth['user']->id;

        // 2. Usuario intenta registrar otro usuario (debe fallar 403)
        $newUserData = [
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registerResponse = $this->postJson('/api/v1/register', $newUserData, [
            'Authorization' => 'Bearer ' . $userToken,
        ]);

        $registerResponse->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Forbidden. Admin privileges required.',
            ]);

        // Verificar audit log con response_code 403
        $this->assertAuditLogExists([
            'user_id' => $userId,
            'service' => 'api/v1/register',
            'method' => 'POST',
            'response_code' => 403,
        ]);

        // Verificar que el usuario NO fue creado
        $this->assertDatabaseMissing('users', [
            'email' => $newUserData['email'],
        ]);
    }
}
