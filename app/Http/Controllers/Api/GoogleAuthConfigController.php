<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GoogleAuthConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $clientId = config('services.google.client_id');

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => filled($clientId),
                'client_id' => filled($clientId) ? $clientId : null,
            ],
        ]);
    }
}
