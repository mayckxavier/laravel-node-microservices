<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use JsonException;

class ExternalController extends Controller
{
    public function index(ExternalService $externalService)
    {
        try {
            return $externalService->callMicroservice();
        } catch (ConnectionException $e) {
            return response()->json([
                'error' => 'Failed to connect to the microservice',
                'message' => $e->getMessage()
            ], 503);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
