<?php

namespace Tests\Feature;

use App\Services\ExternalService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use Mockery;
use Exception;
use JsonException;

class ExternalControllerTest extends TestCase
{
    public function test_index_returns_successful_response(): void
    {
        $mockService = Mockery::mock(ExternalService::class);

        $mockResponse = new JsonResponse(
            ['data' => ['message' => 'Hello World!']],
            200,
            ['Content-Type' => 'application/json']
        );

        $mockService->shouldReceive('callMicroservice')
            ->once()
            ->andReturn($mockResponse);

        $this->app->instance(ExternalService::class, $mockService);

        $response = $this->get(route('external.getAll'));
        $response->assertStatus(200);
        $response->assertJson(['data' => ['message' => 'Hello World!']]);
    }

    public function test_index_handles_generic_exception(): void
    {
        $mockService = Mockery::mock(ExternalService::class);

        $mockService->shouldReceive('callMicroservice')
            ->once()
            ->andThrow(new Exception('Test exception'));

        $this->app->instance(ExternalService::class, $mockService);

        $response = $this->get('/api/external');
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'An unexpected error occurred',
            'message' => 'Test exception'
        ]);
    }

    public function test_index_handles_connection_exception(): void
    {
        $mockService = Mockery::mock(ExternalService::class);

        $mockService->shouldReceive('callMicroservice')
            ->once()
            ->andThrow(new ConnectionException('Could not connect to the server'));

        $this->app->instance(ExternalService::class, $mockService);

        $response = $this->get('/api/external');
        $response->assertStatus(503);
        $response->assertJson([
            'error' => 'Failed to connect to the microservice',
            'message' => 'Could not connect to the server'
        ]);
    }

    public function test_index_handles_json_exception(): void
    {
        $mockService = Mockery::mock(ExternalService::class);
        $mockService->shouldReceive('callMicroservice')
            ->once()
            ->andThrow(new JsonException('Syntax error'));

        $this->app->instance(ExternalService::class, $mockService);
        $response = $this->get('/api/external');

        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'An unexpected error occurred',
            'message' => 'Syntax error'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
