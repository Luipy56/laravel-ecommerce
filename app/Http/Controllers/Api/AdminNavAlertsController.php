<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PersonalizedSolution;
use Illuminate\Http\JsonResponse;

/**
 * Lightweight counts for admin sidebar attention badges (orders / personalized solutions).
 */
class AdminNavAlertsController extends Controller
{
    public function show(): JsonResponse
    {
        $ordersNeedAttention = Order::query()
            ->where('kind', Order::KIND_ORDER)
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_AWAITING_PAYMENT,
                Order::STATUS_AWAITING_INSTALLATION_PRICE,
                Order::STATUS_INSTALLATION_PENDING,
            ])
            ->exists();

        $personalizedNeedAttention = PersonalizedSolution::query()
            ->where('is_active', true)
            ->where('status', PersonalizedSolution::STATUS_PENDING_REVIEW)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'orders_need_attention' => $ordersNeedAttention,
                'personalized_solutions_need_attention' => $personalizedNeedAttention,
            ],
        ]);
    }
}
