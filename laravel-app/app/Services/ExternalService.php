<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ExternalService
{
    protected string $baseUrl;
    protected int $retries;
    protected int $timeout;

    public function __construct(int $retries = 3, int $timeout = 10)
    {
        $this->baseUrl = Config::get('app.external_service_base_url');
        $this->retries = $retries;
        $this->timeout = $timeout;
    }

    public function callMicroservice(string $endpoint = '/', array $query = [])
    {
        $response = $this->getHttpClient()
            ->get($this->baseUrl . $endpoint, $query);
        return $response->json();
    }

    protected function getHttpClient(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->retry($this->retries, 100);
    }
}
