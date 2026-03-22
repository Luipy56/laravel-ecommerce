<?php

namespace App\Services\Payments\Redsys;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Models\Payment;
use RuntimeException;

class RedsysCheckoutStarter implements PaymentCheckoutStarter
{
    public function gateway(): string
    {
        return Payment::GATEWAY_REDSYS;
    }

    public function start(Payment $payment): array
    {
        $merchantCode = config('services.redsys.merchant_code');
        $secretKey = config('services.redsys.secret_key');
        $terminal = config('services.redsys.terminal', '001');

        if (! is_string($merchantCode) || $merchantCode === '' || ! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('Redsys is not configured (REDSYS_MERCHANT_CODE, REDSYS_SECRET_KEY).');
        }

        $environment = config('services.redsys.environment', 'test');
        $actionUrl = $environment === 'production'
            ? 'https://sis.redsys.es/sis/realizarPago'
            : 'https://sis-t.redsys.es:25443/sis/realizarPago';

        $orderNumber = $this->uniqueOrderNumber($payment);
        $amountCents = (int) round((float) $payment->amount * 100);

        $merchantUrl = route('payments.redsys.notify', [], true);
        $urlOk = url('/orders/'.$payment->order_id.'?payment=ok');
        $urlKo = url('/orders/'.$payment->order_id.'?payment=ko');

        $params = [
            'DS_MERCHANT_AMOUNT' => (string) $amountCents,
            'DS_MERCHANT_ORDER' => $orderNumber,
            'DS_MERCHANT_MERCHANTCODE' => $merchantCode,
            'DS_MERCHANT_CURRENCY' => '978',
            'DS_MERCHANT_TRANSACTIONTYPE' => '0',
            'DS_MERCHANT_TERMINAL' => (string) $terminal,
            'DS_MERCHANT_MERCHANTURL' => $merchantUrl,
            'DS_MERCHANT_URLOK' => $urlOk,
            'DS_MERCHANT_URLKO' => $urlKo,
            'DS_MERCHANT_CONSUMERLANGUAGE' => '001',
            'DS_MERCHANT_PRODUCTDESCRIPTION' => 'Order '.$payment->order_id,
        ];

        if ($payment->payment_method === Payment::METHOD_BIZUM) {
            $params['DS_MERCHANT_PAYMETHODS'] = 'z';
        }

        $merchantParameters = RedsysSignature::encodeMerchantParameters($params);
        $signature = RedsysSignature::signMerchantParameters($merchantParameters, $secretKey);

        $payment->update([
            'gateway' => Payment::GATEWAY_REDSYS,
            'gateway_reference' => $orderNumber,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'metadata' => array_merge($payment->metadata ?? [], [
                'redsys_order' => $orderNumber,
            ]),
        ]);

        return [
            'gateway' => Payment::GATEWAY_REDSYS,
            'action_url' => $actionUrl,
            'fields' => [
                'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
                'Ds_MerchantParameters' => $merchantParameters,
                'Ds_Signature' => $signature,
            ],
        ];
    }

    private function uniqueOrderNumber(Payment $payment): string
    {
        $base = str_pad((string) ((int) (microtime(true) * 1000) % 10000000000), 10, '0', STR_PAD_LEFT);
        $suffix = substr((string) $payment->id, -2);

        return substr($base.$suffix, 0, 12);
    }
}
