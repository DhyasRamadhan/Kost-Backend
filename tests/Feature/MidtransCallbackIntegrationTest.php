<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use App\Models\Payment;

class MidtransCallbackIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_callback_updates_payment_status()
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

        $payment = Payment::create([
            'contract_id' => $contract->id,
            'owner_id' => $owner->id,
            'tenant_id' => $tenant->id,
            'amount' => 750000,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'midtrans_order_id' => 'ORDER-123'
        ]);

        $response = $this->postJson('/api/payments/callback', [
            'order_id' => 'ORDER-123',
            'transaction_status' => 'settlement'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid'
        ]);
    }
}
