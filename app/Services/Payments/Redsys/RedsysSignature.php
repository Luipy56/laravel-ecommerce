<?php

namespace App\Services\Payments\Redsys;

/**
 * Redsys HMAC_SHA256_V1 (REST / inSite style): sign base64-encoded merchant parameters.
 *
 * @see https://pagosonline.redsys.es/desarrolladores-inicio/documentacion-tipos-de-integracion/modulos-pago/
 */
final class RedsysSignature
{
    public static function signMerchantParameters(string $merchantParametersBase64, string $secretKeyBase64): string
    {
        $key = base64_decode($secretKeyBase64, true);
        if ($key === false) {
            throw new \InvalidArgumentException('Invalid Redsys secret key (base64).');
        }

        $digest = hash_hmac('sha256', $merchantParametersBase64, $key, true);

        return base64_encode($digest);
    }

    public static function verifyResponse(string $merchantParametersBase64, string $signatureBase64, string $secretKeyBase64): bool
    {
        $expected = self::signMerchantParameters($merchantParametersBase64, $secretKeyBase64);

        return hash_equals($expected, $signatureBase64);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public static function encodeMerchantParameters(array $params): string
    {
        $json = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return base64_encode((string) $json);
    }

    /** @return array<string, mixed> */
    public static function decodeMerchantParameters(string $merchantParametersBase64): array
    {
        $json = base64_decode($merchantParametersBase64, true);
        if ($json === false) {
            return [];
        }
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}
