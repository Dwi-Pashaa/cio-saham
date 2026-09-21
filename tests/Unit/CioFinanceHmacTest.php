<?php

namespace Tests\Unit;

use App\Services\FinanceClient\CioFinanceApiClient;
use Tests\TestCase;

class CioFinanceHmacTest extends TestCase
{
    /**
     * Test validasi pembentukan 7-baris Canonical String dan HMAC-SHA256 Signature.
     */
    public function test_hmac_signature_generation_has_required_headers_and_valid_format(): void
    {
        $client = new CioFinanceApiClient();

        $method = 'GET';
        $pathWithQuery = '/api/v1/analytics/chart?range=7d&interval=daily';
        $headers = $client->generateHeaders($method, $pathWithQuery);

        $this->assertArrayHasKey('X-Client-ID', $headers);
        $this->assertArrayHasKey('X-Key-ID', $headers);
        $this->assertArrayHasKey('X-Timestamp', $headers);
        $this->assertArrayHasKey('X-Nonce', $headers);
        $this->assertArrayHasKey('X-Signature', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Accept', $headers);

        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('application/json', $headers['Accept']);
        $this->assertNotEmpty($headers['X-Signature']);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9+\/]+={0,2}$/', $headers['X-Signature']);

        // Verifikasi manual signature
        $timestamp = $headers['X-Timestamp'];
        $nonce     = $headers['X-Nonce'];
        $clientId  = $headers['X-Client-ID'];
        $keyId     = $headers['X-Key-ID'];
        $secretKey = config('services.cio_finance.secret_key', env('CIO_FINANCE_SECRET_KEY'));
        $bodyHash  = hash('sha256', '');

        $expectedCanonical = implode("\n", [
            'GET',
            '/api/v1/analytics/chart?range=7d&interval=daily',
            $clientId,
            $keyId,
            $timestamp,
            $nonce,
            $bodyHash,
        ]);

        $expectedSignature = base64_encode(hash_hmac('sha256', $expectedCanonical, $secretKey, true));

        $this->assertEquals($expectedSignature, $headers['X-Signature']);
    }

    /**
     * Test HMAC Signature untuk request POST dengan JSON Body.
     */
    public function test_hmac_signature_for_post_request_with_body(): void
    {
        $client = new CioFinanceApiClient();

        $method = 'POST';
        $path = '/api/v1/history';
        $body = ['event' => 'created', 'amount' => 500000];

        $headers = $client->generateHeaders($method, $path, $body);

        $timestamp = $headers['X-Timestamp'];
        $nonce     = $headers['X-Nonce'];
        $clientId  = $headers['X-Client-ID'];
        $keyId     = $headers['X-Key-ID'];
        $secretKey = config('services.cio_finance.secret_key', env('CIO_FINANCE_SECRET_KEY'));
        $bodyHash  = hash('sha256', json_encode($body, JSON_UNESCAPED_SLASHES));

        $expectedCanonical = implode("\n", [
            'POST',
            '/api/v1/history',
            $clientId,
            $keyId,
            $timestamp,
            $nonce,
            $bodyHash,
        ]);

        $expectedSignature = base64_encode(hash_hmac('sha256', $expectedCanonical, $secretKey, true));

        $this->assertEquals($expectedSignature, $headers['X-Signature']);
    }
}
