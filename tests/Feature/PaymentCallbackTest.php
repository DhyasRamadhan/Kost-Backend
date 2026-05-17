<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use App\Models\Payment;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function createPayment($status = 'pending')
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

        return Payment::create([
            'contract_id' => $contract->id,
            'owner_id' => $owner->id,
            'tenant_id' => $tenant->id,
            'amount' => 750000,
            'status' => $status,
            'payment_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'midtrans_order_id' => 'ORDER-123'
        ]);
    }

    public function test_settlement_callback_updates_payment_status()
    {
        $payment = $this->createPayment();

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

    public function test_pending_callback_updates_payment_status()
    {
        $payment = $this->createPayment();

        $response = $this->postJson('/api/payments/callback', [
            'order_id' => 'ORDER-123',
            'transaction_status' => 'pending'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending'
        ]);
    }

    public function test_expire_callback_updates_payment_status()
    {
        $payment = $this->createPayment();

        $response = $this->postJson('/api/payments/callback', [
            'order_id' => 'ORDER-123',
            'transaction_status' => 'expire'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed'
        ]);
    }

    public function test_callback_with_invalid_order_id()
    {
        $response = $this->postJson('/api/payments/callback', [
            'order_id' => 'INVALID-ORDER',
            'transaction_status' => 'settlement'
        ]);

        $response->assertStatus(404);
    }
}
