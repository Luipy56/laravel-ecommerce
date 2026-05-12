<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PersonalizedSolution;
use App\Models\ReturnRequest;
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
            ->where(fn ($q) => $q
                ->where('status', 'like', '%pending%')
                ->orWhere('status', 'awaiting_installation_price'))
            ->exists();

        $personalizedNeedAttention = PersonalizedSolution::query()
            ->where('is_active', true)
            ->where('status', 'like', '%pending%')
            ->exists();

        $returnsNeedAttention = ReturnRequest::query()
            ->where('status', 'like', '%pending%')
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'orders_need_attention' => $ordersNeedAttention,
                'personalized_solutions_need_attention' => $personalizedNeedAttention,
                'returns_need_attention' => $returnsNeedAttention,
            ],
        ]);
    }
}
