<?php

namespace App\Http\Controllers\Api;

use App\Events\ReturnRequestApprovedEvent;
use App\Events\ReturnRequestRejectedEvent;
use App\Events\ReturnRequestRefundedEvent;
use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\ReturnRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminReturnRequestController extends Controller
{
    public function __construct(
        private readonly ReturnRequestService $returnRequestService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ReturnRequest::query()
            ->with(['order:id,status,order_date', 'client:id,login_email'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search'));
            if (is_numeric($term)) {
                $query->where('order_id', (int) $term);
            } else {
                $query->whereHas('client', fn ($q) => $q->where('login_email', 'like', '%'.$term.'%'));
            }
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $rmas = $query->paginate($perPage);

        $data = $rmas->getCollection()->map(fn (ReturnRequest $r) => $this->formatRma($r))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $rmas->currentPage(),
                'last_page' => $rmas->lastPage(),
                'per_page' => $rmas->perPage(),
                'total' => $rmas->total(),
            ],
        ]);
    }

    public function show(ReturnRequest $rma): JsonResponse
    {
        $rma->load(['order.client:id,login_email,identification', 'order.lines.product:id,name,code', 'order.lines.pack:id,name', 'order.payments', 'client:id,login_email', 'payment']);

        return response()->json([
            'success' => true,
            'data' => $this->formatRmaDetail($rma),
        ]);
    }

    public function update(Request $request, ReturnRequest $rma): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $rma->isPendingReview()) {
            return response()->json(['success' => false, 'message' => 'Return request is not pending review.'], 422);
        }

        if ($validated['action'] === 'approve') {
            $rma = $this->returnRequestService->approve($rma, $validated['admin_notes'] ?? null);
            ReturnRequestApprovedEvent::dispatch($rma->load(['order.client', 'order.lines']));
        } else {
            $notes = $validated['admin_notes'] ?? '';
            $rma = $this->returnRequestService->reject($rma, $notes);
            ReturnRequestRejectedEvent::dispatch($rma->load(['order.client', 'order.lines']));
        }

        return response()->json(['success' => true, 'data' => $this->formatRma($rma)]);
    }

    public function refund(Request $request, ReturnRequest $rma): JsonResponse
    {
        $validated = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        if (! $rma->isApproved()) {
            return response()->json(['success' => false, 'message' => 'Return request must be approved before issuing a refund.'], 422);
        }

        try {
            $rma = $this->returnRequestService->issueRefund($rma, (float) $validated['refund_amount']);
            ReturnRequestRefundedEvent::dispatch($rma->load(['order.client', 'order.lines']));
        } catch (\App\Exceptions\PaymentProviderNotConfiguredException $e) {
            return response()->json(['success' => false, 'message' => 'Payment provider not configured.'], 503);
        } catch (\Throwable $e) {
            Log::error('RMA refund failed', ['rma_id' => $rma->id, 'message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Refund failed: '.$e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'data' => $this->formatRma($rma)]);
    }

    private function formatRma(ReturnRequest $rma): array
    {
        return [
            'id' => $rma->id,
            'order_id' => $rma->order_id,
            'client_id' => $rma->client_id,
            'client_login_email' => $rma->relationLoaded('client') && $rma->client ? $rma->client->login_email : null,
            'status' => $rma->status,
            'reason' => $rma->reason,
            'admin_notes' => $rma->admin_notes,
            'refund_amount' => $rma->refund_amount !== null ? (float) $rma->refund_amount : null,
            'refunded_at' => $rma->refunded_at?->toIso8601String(),
            'gateway_refund_reference' => $rma->gateway_refund_reference,
            'created_at' => $rma->created_at?->toIso8601String(),
            'updated_at' => $rma->updated_at?->toIso8601String(),
            'order' => $rma->relationLoaded('order') && $rma->order ? [
                'id' => $rma->order->id,
                'status' => $rma->order->status,
                'order_date' => $rma->order->order_date?->toIso8601String(),
            ] : null,
        ];
    }

    private function formatRmaDetail(ReturnRequest $rma): array
    {
        $base = $this->formatRma($rma);

        if ($rma->relationLoaded('order') && $rma->order) {
            $order = $rma->order;
            $base['order'] = [
                'id' => $order->id,
                'status' => $order->status,
                'order_date' => $order->order_date?->toIso8601String(),
                'client' => $order->client ? [
                    'id' => $order->client->id,
                    'login_email' => $order->client->login_email,
                    'identification' => $order->client->identification,
                    '_decryption_error' => $order->client->hasDecryptionErrors(),
                ] : null,
                'lines' => ($order->relationLoaded('lines') ? $order->lines : collect())->map(fn ($l) => [
                    'id' => $l->id,
                    'product' => $l->product ? ['id' => $l->product->id, 'name' => $l->product->name, 'code' => $l->product->code] : null,
                    'pack' => $l->pack ? ['id' => $l->pack->id, 'name' => $l->pack->name] : null,
                    'quantity' => $l->quantity,
                    'unit_price' => $l->unit_price !== null ? (float) $l->unit_price : null,
                    'line_total' => (float) $l->line_total,
                ])->values()->all(),
                'payments' => ($order->relationLoaded('payments') ? $order->payments : collect())->map(fn ($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'currency' => $p->currency,
                    'status' => $p->status,
                    'gateway' => $p->gateway,
                    'payment_method' => $p->payment_method,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                ])->values()->all(),
            ];
        }

        if ($rma->relationLoaded('payment') && $rma->payment) {
            $base['payment'] = [
                'id' => $rma->payment->id,
                'amount' => (float) $rma->payment->amount,
                'currency' => $rma->payment->currency,
                'status' => $rma->payment->status,
                'gateway' => $rma->payment->gateway,
                'payment_method' => $rma->payment->payment_method,
                'paid_at' => $rma->payment->paid_at?->toIso8601String(),
            ];
        }

        return $base;
    }
}
