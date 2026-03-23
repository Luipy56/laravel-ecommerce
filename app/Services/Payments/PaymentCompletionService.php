<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentCompletionService
{
    public function markSucceeded(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED,
                'paid_at' => now(),
                'failure_code' => null,
                'failure_message' => null,
            ]);
        });
    }

    public function markFailed(Payment $payment, ?string $code, ?string $message): void
    {
        DB::transaction(function () use ($payment, $code, $message) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failure_code' => $code,
                'failure_message' => $message,
            ]);
        });
    }

    public function markCanceled(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $payment->update([
                'status' => Payment::STATUS_CANCELED,
            ]);
        });
    }
}
