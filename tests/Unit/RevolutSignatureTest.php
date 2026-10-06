<?php

namespace Tests\Unit;

use App\Services\Payments\Revolut\RevolutSignature;
use PHPUnit\Framework\TestCase;

class RevolutSignatureTest extends TestCase
{
    public function test_valid_signature_matches_documented_algorithm(): void
    {
        $secret = 'wsk_test_secret';
        $timestamp = '1683650202360';
        $rawBody = '{"event":"ORDER_COMPLETED","order_id":"6634c172-3398-ac93-aee9-50de0282e3ac"}';
        $payloadToSign = 'v1.'.$timestamp.'.'.$rawBody;
        $sig = 'v1='.hash_hmac('sha256', $payloadToSign, $secret);

        $this->assertTrue(RevolutSignature::isValid(
            $rawBody,
            $timestamp,
            $sig,
            $secret,
            (int) $timestamp,
        ));
    }

    public function test_rejects_bad_signature(): void
    {
        $timestamp = (string) (int) floor(microtime(true) * 1000);
        $this->assertFalse(RevolutSignature::isValid(
            '{"event":"ORDER_COMPLETED"}',
            $timestamp,
            'v1=deadbeef',
            'wsk_test_secret',
            (int) $timestamp,
        ));
    }

    public function test_accepts_one_of_multiple_signatures(): void
    {
        $secret = 'wsk_test_secret';
        $timestamp = '1683650202360';
        $rawBody = '{"a":1}';
        $good = 'v1='.hash_hmac('sha256', 'v1.'.$timestamp.'.'.$rawBody, $secret);
        $header = 'v1=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa,'.$good;

        $this->assertTrue(RevolutSignature::isValid($rawBody, $timestamp, $header, $secret, (int) $timestamp));
    }
}
