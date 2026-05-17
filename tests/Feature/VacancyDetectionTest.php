<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RentalContract;

class VacancyDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_detect_potential_vacancy()
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

        RentalContract::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'owner_id' => $owner->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'monthly_rent' => 750000,
            'status' => 'active'
        ]);

        $response = $this->artisan('vacancies:detect');

        $response->assertExitCode(0);
    }

    public function test_ignore_safe_contract()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenantUser = User::factory()->create(['role' => 'tenant']);
        $tenant = Tenant::create(['user_id' => $tenantUser->id]);

        $room = Room::create([
            'owner_id' => $owner->id,
            'room_number' => 'A-02',
            'price' => 750000,
            'status' => 'occupied'
        ]);

        RentalContract::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'owner_id' => $owner->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'monthly_rent' => 750000,
            'status' => 'active'
        ]);

        $response = $this->artisan('vacancies:detect');

        $response->assertExitCode(0);
    }

    public function test_vacancy_detection_command_runs_successfully()
    {
        $response = $this->artisan('vacancies:detect');

        $response->assertExitCode(0);
    }
}
