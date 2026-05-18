<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyDetectionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacancy_detection_command_runs_successfully()
    {
        $response = $this->artisan('vacancies:detect');

        $response->assertExitCode(0);
    }
}
