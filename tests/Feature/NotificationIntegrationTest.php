<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_reminder_command_runs_successfully()
    {
        $response = $this->artisan('payments:send-reminders');

        $response->assertExitCode(0);
    }

    public function test_contract_reminder_command_runs_successfully()
    {
        $response = $this->artisan('contracts:send-reminders');

        $response->assertExitCode(0);
    }
}
