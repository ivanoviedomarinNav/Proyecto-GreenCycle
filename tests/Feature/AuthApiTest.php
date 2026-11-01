<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Yoel',
            'email' => 'yoel@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'yoel@test.com']);
    }

    public function test_register_validates_input(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'no-es-un-correo',
            'password' => '123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_rejects_a_duplicated_email(): void
    {
        User::factory()->create(['email' => 'yoel@test.com']);

        $this->postJson('/api/register', [
            'name' => 'Otro',
            'email' => 'yoel@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_a_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create(['email' => 'yoel@test.com']);

        $this->postJson('/api/login', [
            'email' => 'yoel@test.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create(['email' => 'yoel@test.com']);

        $this->postJson('/api/login', [
            'email' => 'yoel@test.com',
            'password' => 'incorrecta',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_private_endpoints_require_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
