<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_integration_success()
    {
        User::factory()->create([
            'email' => 'tenant@test.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'tenant@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user'
            ]);
    }
}
