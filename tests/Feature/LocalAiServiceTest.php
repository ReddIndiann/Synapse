<?php

namespace Tests\Feature;

use App\Services\LocalAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalAiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_returns_null_when_server_unreachable(): void
    {
        $service = new LocalAiService();

        $result = $service->parse('Schedule a task for tomorrow', 1);

        $this->assertNull($result);
    }

    public function test_is_available_returns_false_when_server_offline(): void
    {
        $service = new LocalAiService();

        $this->assertFalse($service->isAvailable());
    }

    public function test_can_be_resolved_from_container(): void
    {
        $service = app(LocalAiService::class);

        $this->assertInstanceOf(LocalAiService::class, $service);
    }
}
