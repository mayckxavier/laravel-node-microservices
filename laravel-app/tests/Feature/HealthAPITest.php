<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthAPITest extends TestCase
{
    public function test_health_ok(): void
    {
        $this->get('api/health')
            ->assertStatus(200);
    }
}
