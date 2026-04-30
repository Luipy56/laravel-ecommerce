<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use App\Support\PaymentOfflineInstructions;
use Illuminate\Http\JsonResponse;

class ShopPublicSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $merged = ShopSetting::allMerged();

        return response()->json([
            'success' => true,
            'data' => [
                'accept_personalized_solutions' => (bool) ShopSetting::get(
                    ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS,
                    true
                ),
                'offline_payment_instructions' => PaymentOfflineInstructions::publicPayload($merged),
            ],
        ]);
    }
}
