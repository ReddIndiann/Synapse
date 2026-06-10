<?php

namespace Tests\Feature;

use App\Console\Commands\ExportTrainingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTrainingDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_runs_without_examples(): void
    {
        $this->artisan('ai:export-training-data')
            ->assertSuccessful();
    }

    public function test_command_runs_with_examples(): void
    {
        $this->artisan('ai:export-training-data --include-examples')
            ->assertSuccessful();
    }
}
