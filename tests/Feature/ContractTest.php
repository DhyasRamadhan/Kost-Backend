<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use Laravel\Sanctum\Sanctum;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_contract_successfully()
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'verification_status' => 'approved'
        ]);

        $tenantUser = User::factory()->create(['role' => 'tenant']);
        $tenant = Tenant::create(['user_id' => $tenantUser->id]);

        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'available'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/contracts', [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'monthly_rent' => 750000
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contract created'
            ]);

        $this->assertDatabaseHas('rental_contracts', [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'active'
        ]);
    }

    public function test_cannot_create_contract_for_occupied_room()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenantUser = User::factory()->create(['role' => 'tenant']);
        $tenant = Tenant::create(['user_id' => $tenantUser->id]);

        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'occupied'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/contracts', [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'monthly_rent' => 750000
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Room already occupied'
            ]);
    }

    public function test_room_status_updated_after_contract_created()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenantUser = User::factory()->create(['role' => 'tenant']);
        $tenant = Tenant::create(['user_id' => $tenantUser->id]);

        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'available'
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/contracts', [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'monthly_rent' => 750000
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'occupied'
        ]);
    }

    public function test_delete_contract_successfully()
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-01',
            'price' => 750000,
            'status' => 'occupied'
        ]);

        $tenantUser = User::factory()->create(['role' => 'tenant']);
        $tenant = Tenant::create(['user_id' => $tenantUser->id]);

        $contract = RentalContract::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'owner_id' => $owner->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'monthly_rent' => 750000,
            'status' => 'active'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/contracts/{$contract->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contract deleted'
            ]);

        $this->assertDatabaseMissing('rental_contracts', [
            'id' => $contract->id
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'available'
        ]);
    }
}
