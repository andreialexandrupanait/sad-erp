<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'version',
            'checks',
        ]);
    }

    public function test_health_endpoint_returns_healthy_status_when_all_services_up(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
        ]);
    }

    public function test_health_endpoint_includes_database_check(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'checks' => [
                'database' => ['status'],
            ],
        ]);
    }

    public function test_health_endpoint_includes_disk_check(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'checks' => [
                'disk' => ['status'],
            ],
        ]);
    }

    public function test_health_endpoint_is_rate_limited(): void
    {
        // Make 61 requests (limit is 60 per minute)
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/health');
        }

        $response = $this->getJson('/health');

        $response->assertStatus(429);
    }

    public function test_health_endpoint_includes_timestamp(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('timestamp', $json);
        $this->assertNotEmpty($json['timestamp']);
    }

    public function test_health_endpoint_includes_version(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('version', $json);
    }
}
