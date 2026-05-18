<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use Laravel\Sanctum\Sanctum;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_creation_integration()
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

        $response = $this->postJson('/api/payments/create', [
            'contract_id' => $contract->id,
            'amount' => 750000,
            'due_date' => now()->addDays(7)->toDateString()
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'contract_id' => $contract->id,
            'status' => 'pending'
        ]);
    }
}
