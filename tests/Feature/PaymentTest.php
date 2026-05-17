<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;
use App\Models\Payment;
use Laravel\Sanctum\Sanctum;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_payment_successfully()
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
            'amount' => 750000,
            'status' => 'pending'
        ]);
    }

    public function test_tenant_can_get_payment_list()
    {
        $tenantUser = User::factory()->create(['role' => 'tenant']);

        Sanctum::actingAs($tenantUser);

        $response = $this->getJson('/api/payments');

        $response->assertStatus(200);
    }

    public function test_cancel_payment_successfully()
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
            'due_date' => now()->addDays(7)->toDateString()
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/payments/{$payment->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_paid_payment_cannot_be_cancelled()
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
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString()
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/payments/{$payment->id}/cancel");

        $response->assertStatus(400);
    }

    public function test_overdue_payment_detection()
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

        Payment::create([
            'contract_id' => $contract->id,
            'owner_id' => $owner->id,
            'tenant_id' => $tenant->id,
            'amount' => 750000,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'due_date' => now()->subDays(3)->toDateString()
        ]);

        $this->assertDatabaseHas('payments', [
            'status' => 'pending'
        ]);
    }
}
