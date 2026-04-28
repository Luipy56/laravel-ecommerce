<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PersonalizedSolutionImprovementRequestedAdminMail;
use App\Models\Order;
use App\Models\PersonalizedSolution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Token-based access for personalized solutions (LOPD: manage data without account).
 */
class PublicPersonalizedSolutionController extends Controller
{
    private function findSolution(string $token): ?PersonalizedSolution
    {
        return PersonalizedSolution::query()
            ->where('public_token', $token)
            ->where('is_active', true)
            ->first();
    }

    public function show(Request $request, string $token): JsonResponse
    {
        $solution = $this->findSolution($token);
        if (! $solution) {
            return response()->json(['success' => false, 'message' => __('client_portal.solution_not_found')], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeForClient($solution),
        ]);
    }

    public function update(Request $request, string $token): JsonResponse
    {
        $solution = $this->findSolution($token);
        if (! $solution) {
            return response()->json(['success' => false, 'message' => __('client_portal.solution_not_found')], 404);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_province' => ['nullable', 'string', 'max:100'],
            'address_postal_code' => ['required', 'string', 'regex:/^\d{1,20}$/'],
            'address_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $solution->fill([
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address_street' => $validated['address_street'] ?? null,
            'address_city' => $validated['address_city'] ?? null,
            'address_province' => $validated['address_province'] ?? null,
            'address_postal_code' => $validated['address_postal_code'],
            'address_note' => $validated['address_note'] ?? null,
        ]);
        $solution->save();

        return response()->json(['success' => true, 'data' => $this->serializeForClient($solution->fresh())]);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        $solution = $this->findSolution($token);
        if (! $solution) {
            return response()->json(['success' => false, 'message' => __('client_portal.solution_not_found')], 404);
        }

        $solution->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    public function requestImprovements(Request $request, string $token): JsonResponse
    {
        $solution = $this->findSolution($token);
        if (! $solution) {
            return response()->json(['success' => false, 'message' => __('client_portal.solution_not_found')], 404);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $solution->update([
            'iterations_count' => $solution->iterations_count + 1,
            'improvement_feedback' => $validated['message'],
            'improvement_feedback_at' => now(),
        ]);
        $solution->refresh();

        $adminTo = config('mail.admin_notification_address');
        if (! empty($adminTo) && filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            Mail::to($adminTo)->send(new PersonalizedSolutionImprovementRequestedAdminMail($solution, $validated['message']));
        }

        return response()->json(['success' => true, 'data' => $this->serializeForClient($solution)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeForClient(PersonalizedSolution $solution): array
    {
        $orderPayload = null;
        if ($solution->order_id) {
            $order = Order::query()->find($solution->order_id);
            if ($order && $order->kind === Order::KIND_ORDER) {
                $orderPayload = [
                    'id' => $order->id,
                    'grand_total' => $order->grand_total,
                    'has_payment' => $order->hasSuccessfulPayment(),
                    'can_pay' => $order->clientMayPay(),
                    'pay_path' => '/orders/'.$order->id,
                ];
            }
        }

        return [
            'id' => $solution->id,
            'status' => $solution->status,
            'problem_description' => $solution->problem_description,
            'resolution' => $solution->resolution,
            'improvement_feedback' => $solution->improvement_feedback,
            'improvement_feedback_at' => $solution->improvement_feedback_at?->toIso8601String(),
            'iterations_count' => (int) $solution->iterations_count,
            'email' => $solution->email,
            'phone' => $solution->phone,
            'address_street' => $solution->address_street,
            'address_city' => $solution->address_city,
            'address_province' => $solution->address_province,
            'address_postal_code' => $solution->address_postal_code,
            'address_note' => $solution->address_note,
            'order' => $orderPayload,
            'updated_at' => $solution->updated_at?->toIso8601String(),
        ];
    }
}
