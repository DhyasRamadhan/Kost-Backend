<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_tenant_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Tenant Test',
            'email' => 'tenant@test.com',
            'password' => 'password123',
            'role' => 'tenant',
            'phone' => '08123456789'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Register success'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'tenant@test.com',
            'role' => 'tenant'
        ]);

        $user = User::where('email', 'tenant@test.com')->first();

        $this->assertDatabaseHas('tenants', [
            'user_id' => $user->id
        ]);
    }

    public function test_register_owner_with_pending_status()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Owner Test',
            'email' => 'owner@test.com',
            'password' => 'password123',
            'role' => 'owner',
            'phone' => '08123456789'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Register success'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'owner@test.com',
            'role' => 'owner',
            'verification_status' => 'pending'
        ]);
    }

    public function test_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'login@test.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
            'verification_status' => 'approved'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user'
            ]);
    }

    public function test_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'wrong@test.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Wrong password'
            ]);
    }

    public function test_owner_cannot_login_before_verification()
    {
        $user = User::factory()->create([
            'email' => 'ownerpending@test.com',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'verification_status' => 'pending'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ownerpending@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Owner account is not verified yet'
            ]);
    }

    public function test_logout_successfully()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout success'
            ]);
    }
}
