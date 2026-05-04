<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnRequestSeeder extends Seeder
{
    /**
     * Seeds demo return requests covering all RMA statuses.
     * Runs after PaymentSeeder so payments exist.
     *
     * order 2  (in_transit, client 1, card)       → rejected
     * order 3  (sent, client 1, card)              → pending_review
     * order 5  (installation_confirmed, client 2)  → approved
     * order 6  (installation_confirmed, client 3)  → refunded  (also marks order=returned, payment=refunded)
     */
    public function run(): void
    {
        $now = now();

        $paymentByOrder = Payment::query()
            ->whereIn('order_id', [2, 3, 5, 6])
            ->where('status', Payment::STATUS_SUCCEEDED)
            ->pluck('id', 'order_id');

        $rows = [
            [
                'order_id' => 2,
                'client_id' => 1,
                'payment_id' => $paymentByOrder[2] ?? null,
                'status' => ReturnRequest::STATUS_REJECTED,
                'reason' => 'El producte no era el que esperava. La descripció no corresponia amb el que vaig rebre.',
                'admin_notes' => 'La comanda estava en trànsit en el moment de la sol·licitud. No és elegible per a devolució.',
                'refund_amount' => null,
                'refunded_at' => null,
                'gateway_refund_reference' => null,
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(7),
            ],
            [
                'order_id' => 3,
                'client_id' => 1,
                'payment_id' => $paymentByOrder[3] ?? null,
                'status' => ReturnRequest::STATUS_PENDING_REVIEW,
                'reason' => 'He rebut el producte però té un defecte de fàbrica. La tapa posterior no tanca correctament i hi ha una escantonada visible.',
                'admin_notes' => null,
                'refund_amount' => null,
                'refunded_at' => null,
                'gateway_refund_reference' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],
            [
                'order_id' => 5,
                'client_id' => 2,
                'payment_id' => $paymentByOrder[5] ?? null,
                'status' => ReturnRequest::STATUS_APPROVED,
                'reason' => 'El producte no funciona correctament. He intentat configurar-lo seguint les instruccions però no arrenca.',
                'admin_notes' => 'Aprovada. Hem verificat el defecte amb el proveïdor. S\'emetrà el reemborsament en breu.',
                'refund_amount' => null,
                'refunded_at' => null,
                'gateway_refund_reference' => null,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(3),
            ],
            [
                'order_id' => 6,
                'client_id' => 3,
                'payment_id' => $paymentByOrder[6] ?? null,
                'status' => ReturnRequest::STATUS_REFUNDED,
                'reason' => 'El producte no s\'ajusta a les mides indicades al web. He mesurat i hi ha una diferència significativa.',
                'admin_notes' => 'Aprovada i reemborsada via PayPal. El client ha confirmat la recepció del reemborsament.',
                'refund_amount' => null,
                'refunded_at' => $now->copy()->subDays(10),
                'gateway_refund_reference' => null,
                'created_at' => $now->copy()->subDays(18),
                'updated_at' => $now->copy()->subDays(10),
            ],
        ];

        foreach ($rows as $row) {
            // Resolve refund_amount from the order total for the refunded RMA
            if ($row['status'] === ReturnRequest::STATUS_REFUNDED) {
                $order = Order::with('lines')->find($row['order_id']);
                if ($order) {
                    $row['refund_amount'] = round($order->grand_total, 2);
                }
            }
            DB::table('return_requests')->insert($row);
        }

        // Mark order 6 as returned and its payment as refunded
        $order6Payment = $paymentByOrder[6] ?? null;
        if ($order6Payment) {
            DB::table('payments')->where('id', $order6Payment)->update([
                'status' => Payment::STATUS_REFUNDED,
                'updated_at' => $now,
            ]);
        }
        DB::table('orders')->where('id', 6)->update([
            'status' => Order::STATUS_RETURNED,
            'updated_at' => $now,
        ]);
    }
}
