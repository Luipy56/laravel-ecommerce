<?php

namespace App\Http\Controllers\Api;

use App\Events\ReturnRequestCreated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Services\ReturnRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    /** Order statuses that allow a return request. */
    private const RETURNABLE_STATUSES = [
        Order::STATUS_SENT,
        Order::STATUS_INSTALLATION_CONFIRMED,
    ];

    public function __construct(
        private readonly ReturnRequestService $returnRequestService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $client = $request->user();

        $rmas = ReturnRequest::query()
            ->where('client_id', $client->id)
            ->with(['order:id,status,order_date'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ReturnRequest $r) => $this->formatRma($r))
            ->values()
            ->all();

        return response()->json(['success' => true, 'data' => $rmas]);
    }

    public function store(Request $request, Order $order): JsonResponse
    {
        $client = $request->user();

        if ($order->client_id !== $client->id) {
            return response()->json(['success' => false, 'message' => __('shop.returns.error_not_your_order')], 403);
        }

        if ($order->kind !== Order::KIND_ORDER || ! in_array($order->status, self::RETURNABLE_STATUSES, true)) {
            return response()->json(['success' => false, 'message' => __('shop.returns.error_not_returnable')], 422);
        }

        $hasSuccessfulPayment = $order->payments()
            ->successful()
            ->whereIn('payment_method', [Payment::METHOD_CARD, Payment::METHOD_PAYPAL, Payment::METHOD_REVOLUT])
            ->exists();

        if (! $hasSuccessfulPayment) {
            return response()->json(['success' => false, 'message' => __('shop.returns.error_no_payment')], 422);
        }

        $hasOpenRma = ReturnRequest::query()
            ->where('order_id', $order->id)
            ->whereIn('status', ReturnRequest::OPEN_STATUSES)
            ->exists();

        if ($hasOpenRma) {
            return response()->json(['success' => false, 'message' => __('shop.returns.error_already_open')], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $rma = $this->returnRequestService->create($order, $validated['reason']);

        ReturnRequestCreated::dispatch($rma->load(['order.client', 'order.lines']));

        return response()->json(['success' => true, 'data' => $this->formatRma($rma)], 201);
    }

    private function formatRma(ReturnRequest $rma): array
    {
        return [
            'id' => $rma->id,
            'order_id' => $rma->order_id,
            'status' => $rma->status,
            'reason' => $rma->reason,
            'refund_amount' => $rma->refund_amount !== null ? (float) $rma->refund_amount : null,
            'refunded_at' => $rma->refunded_at?->toIso8601String(),
            'created_at' => $rma->created_at?->toIso8601String(),
            'order' => $rma->relationLoaded('order') && $rma->order ? [
                'id' => $rma->order->id,
                'status' => $rma->order->status,
                'order_date' => $rma->order->order_date?->toIso8601String(),
            ] : null,
        ];
    }
}
