<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use Illuminate\Http\JsonResponse;

class ShopPublicSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $showBadge = (bool) ShopSetting::get(ShopSetting::KEY_SHOW_LOW_STOCK_BADGE, false);

        return response()->json([
            'success' => true,
            'data' => [
                'accept_personalized_solutions' => (bool) ShopSetting::get(
                    ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS,
                    true
                ),
                'show_low_stock_badge' => $showBadge,
                'low_stock_threshold' => $showBadge
                    ? (int) ShopSetting::get(ShopSetting::KEY_LOW_STOCK_THRESHOLD, 10)
                    : 0,
                'terms_ca' => (string) (ShopSetting::get(ShopSetting::KEY_TERMS_CA) ?? ''),
                'terms_es' => (string) (ShopSetting::get(ShopSetting::KEY_TERMS_ES) ?? ''),
                'terms_en' => (string) (ShopSetting::get(ShopSetting::KEY_TERMS_EN) ?? ''),
            ],
        ]);
    }
}
