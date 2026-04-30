<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\ShopSetting;

/**
 * Reads shop settings for offline payment display and availability.
 */
final class PaymentOfflineInstructions
{
    /**
     * @param  array<string, mixed>  $merged  ShopSetting::allMerged()
     * @return array{
     *   bank_transfer: array{iban: string, beneficiary: string, reference_hint: string},
     *   bizum_manual: array{phone: string, instructions: string}
     * }
     */
    public static function fromMerged(array $merged): array
    {
        $iban = self::stringSetting($merged, ShopSetting::KEY_BANK_TRANSFER_IBAN);
        $beneficiary = self::stringSetting($merged, ShopSetting::KEY_BANK_TRANSFER_BENEFICIARY);
        $referenceHint = self::stringSetting($merged, ShopSetting::KEY_BANK_TRANSFER_REFERENCE_HINT);
        $phone = self::stringSetting($merged, ShopSetting::KEY_BIZUM_MANUAL_PHONE);
        $instructions = self::stringSetting($merged, ShopSetting::KEY_BIZUM_MANUAL_INSTRUCTIONS);

        return [
            'bank_transfer' => [
                'iban' => $iban,
                'beneficiary' => $beneficiary,
                'reference_hint' => $referenceHint,
            ],
            'bizum_manual' => [
                'phone' => $phone,
                'instructions' => $instructions,
            ],
        ];
    }

    public static function bankTransferConfigured(array $merged): bool
    {
        $iban = trim(self::stringSetting($merged, ShopSetting::KEY_BANK_TRANSFER_IBAN));

        return $iban !== '';
    }

    public static function bizumManualConfigured(array $merged): bool
    {
        $phone = trim(self::stringSetting($merged, ShopSetting::KEY_BIZUM_MANUAL_PHONE));
        $instructions = trim(self::stringSetting($merged, ShopSetting::KEY_BIZUM_MANUAL_INSTRUCTIONS));

        return $phone !== '' || $instructions !== '';
    }

    /**
     * Safe subset for public APIs (no secrets beyond what operators choose to publish).
     *
     * @param  array<string, mixed>  $merged
     * @return array<string, mixed>
     */
    public static function publicPayload(array $merged): array
    {
        $block = self::fromMerged($merged);

        return [
            'bank_transfer' => $block['bank_transfer'],
            'bizum_manual' => $block['bizum_manual'],
        ];
    }

    /**
     * @param  array<string, mixed>  $merged
     * @return array<string, mixed>
     */
    public static function paymentInstructionsForMethod(string $method, array $merged): array
    {
        if ($method === Payment::METHOD_BANK_TRANSFER) {
            return [
                'type' => 'bank_transfer',
                'lines' => self::fromMerged($merged)['bank_transfer'],
            ];
        }
        if ($method === Payment::METHOD_BIZUM_MANUAL) {
            return [
                'type' => 'bizum_manual',
                'lines' => self::fromMerged($merged)['bizum_manual'],
            ];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $merged
     */
    private static function stringSetting(array $merged, string $key): string
    {
        $v = $merged[$key] ?? ShopSetting::DEFAULTS[$key] ?? '';

        return is_string($v) ? $v : '';
    }
}
