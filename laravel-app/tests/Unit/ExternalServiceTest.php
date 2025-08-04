<?php

namespace Tests\Unit;

use App\Services\ExternalService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use Mockery;

class ExternalServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_constructor_sets_default_values()
    {
        Config::shouldReceive('get')
            ->with('app.external_service_base_url')
            ->once()
            ->andReturn('http://test-url.com');

        $service = new ExternalService();

        $this->assertEquals('http://test-url.com', $this->getProtectedProperty($service, 'baseUrl'));
        $this->assertEquals(3, $this->getProtectedProperty($service, 'retries'));
        $this->assertEquals(10, $this->getProtectedProperty($service, 'timeout'));
    }

    public function test_constructor_sets_custom_values()
    {
        Config::shouldReceive('get')
            ->with('app.external_service_base_url')
            ->once()
            ->andReturn('http://test-url.com');

        $service = new ExternalService(5, 20);

        $this->assertEquals('http://test-url.com', $this->getProtectedProperty($service, 'baseUrl'));
        $this->assertEquals(5, $this->getProtectedProperty($service, 'retries'));
        $this->assertEquals(20, $this->getProtectedProperty($service, 'timeout'));
    }

    public function test_call_microservice_with_default_parameters()
    {
        Config::shouldReceive('get')
            ->with('app.external_service_base_url')
            ->once()
            ->andReturn('http://test-url.com');

        $mockResponse = Mockery::mock(Response::class);
        $mockPendingRequest = Mockery::mock(PendingRequest::class);
        $expectedJson = ['data' => ['message' => 'Hello World!']];

        Http::shouldReceive('timeout')
            ->with(10)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('retry')
            ->with(3, 100)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('get')
            ->with('http://test-url.com/', [])
            ->once()
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($expectedJson);

        $service = new ExternalService();

        $result = $service->callMicroservice();

        $this->assertEquals($expectedJson, $result);
    }

    public function test_call_microservice_with_custom_parameters()
    {
        Config::shouldReceive('get')
            ->with('app.external_service_base_url')
            ->once()
            ->andReturn('http://test-url.com');

        $mockResponse = Mockery::mock(Response::class);
        $mockPendingRequest = Mockery::mock(PendingRequest::class);
        $expectedJson = ['data' => ['message' => 'Custom endpoint data']];

        Http::shouldReceive('timeout')
            ->with(10)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('retry')
            ->with(3, 100)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('get')
            ->with('http://test-url.com/api/data', ['param1' => 'value1'])
            ->once()
            ->andReturn($mockResponse);

        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($expectedJson);

        $service = new ExternalService();

        $result = $service->callMicroservice('/api/data', ['param1' => 'value1']);
        $this->assertEquals($expectedJson, $result);
    }

    public function test_call_microservice_handles_connection_exception()
    {
        Config::shouldReceive('get')
            ->with('app.external_service_base_url')
            ->once()
            ->andReturn('http://test-url.com');

        $mockPendingRequest = Mockery::mock(PendingRequest::class);

        Http::shouldReceive('timeout')
            ->with(10)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('retry')
            ->with(3, 100)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('get')
            ->with('http://test-url.com/', [])
            ->once()
            ->andThrow(new ConnectionException('Connection failed'));

        $service = new ExternalService();

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Connection failed');
        $service->callMicroservice();
    }

    public function test_get_http_client_configures_client_correctly()
    {
        Config::shouldReceive('get')
            ->with('app.external_service_base_url')
            ->once()
            ->andReturn('http://test-url.com');

        $mockPendingRequest = Mockery::mock(PendingRequest::class);

        Http::shouldReceive('timeout')
            ->with(15)
            ->once()
            ->andReturn($mockPendingRequest);

        $mockPendingRequest->shouldReceive('retry')
            ->with(5, 100)
            ->once()
            ->andReturn($mockPendingRequest);

        $service = new ExternalService(5, 15);

        $result = $this->invokeProtectedMethod($service, 'getHttpClient');
        $this->assertSame($mockPendingRequest, $result);
    }

    protected function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }
    protected function invokeProtectedMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
