<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void {

        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token', 'user'
        ]);
    }

    public function test_user_cannot_login_with_incorrect_credentials(): void {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'ljlkjlkjlkj',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('email');
    }
}
