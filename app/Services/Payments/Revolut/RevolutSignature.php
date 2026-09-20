<?php

namespace App\Services\Payments\Revolut;

/**
 * Verify Merchant webhook HMAC (v1).
 *
 * payload_to_sign = v1.{Revolut-Request-Timestamp}.{raw_body}
 * expected header  = v1={hex hmac-sha256}
 *
 * @see https://developer.revolut.com/docs/guides/merchant/monitor-and-observe/webhooks/verify-the-payload-signature
 */
class RevolutSignature
{
    /** Reject timestamps older/newer than this window (ms). */
    public const TOLERANCE_MS = 300_000;

    public static function isValid(
        string $rawBody,
        string $timestampHeader,
        string $signatureHeader,
        string $signingSecret,
        ?int $nowMs = null,
    ): bool {
        if ($timestampHeader === '' || $signatureHeader === '' || $signingSecret === '') {
            return false;
        }

        if (! ctype_digit($timestampHeader)) {
            return false;
        }

        $ts = (int) $timestampHeader;
        $tsMs = strlen($timestampHeader) <= 10 ? $ts * 1000 : $ts;
        $now = $nowMs ?? (int) floor(microtime(true) * 1000);
        if (abs($now - $tsMs) > self::TOLERANCE_MS) {
            return false;
        }

        $payloadToSign = 'v1.'.$timestampHeader.'.'.$rawBody;
        $expected = 'v1='.hash_hmac('sha256', $payloadToSign, $signingSecret);

        foreach (explode(',', $signatureHeader) as $part) {
            $candidate = trim($part);
            if ($candidate !== '' && hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }
}
