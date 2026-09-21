<?php

namespace App\Services\FinanceClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class CioFinanceApiClient
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $keyId;
    protected string $secretKey;
    protected int $timeout;
    protected Client $httpClient;

    public function __construct()
    {
        $setting = null;
        try {
            $setting = \App\Models\Setting::first();
        } catch (\Throwable $e) {
            // fallback jika database belum termigrasi
        }

        $this->baseUrl   = rtrim(!empty($setting?->cio_finance_base_url) ? $setting->cio_finance_base_url : config('services.cio_finance.base_url', env('CIO_FINANCE_BASE_URL', 'https://finance.cionetwork.id')), '/');
        $this->clientId  = (string) (!empty($setting?->cio_finance_client_id) ? $setting->cio_finance_client_id : config('services.cio_finance.client_id', env('CIO_FINANCE_CLIENT_ID', 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22')));
        $this->keyId     = (string) (!empty($setting?->cio_finance_key_id) ? $setting->cio_finance_key_id : config('services.cio_finance.key_id', env('CIO_FINANCE_KEY_ID', 'kid_4e0479ba4b715ac5')));
        $this->secretKey = (string) (!empty($setting?->cio_finance_secret_key) ? $setting->cio_finance_secret_key : config('services.cio_finance.secret_key', env('CIO_FINANCE_SECRET_KEY', 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7')));
        $this->timeout   = (int) (!empty($setting?->cio_finance_timeout) ? $setting->cio_finance_timeout : config('services.cio_finance.timeout', env('CIO_FINANCE_TIMEOUT', 30)));

        $this->httpClient = new Client([
            'base_uri'    => $this->baseUrl,
            'timeout'     => $this->timeout,
            'http_errors' => false,
            'headers'     => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            ],
        ]);
    }

    /**
     * Hitung HMAC-SHA256 Signature sesuai format 7-baris Canonical String.
     * [METHOD, PATH_WITH_QUERY, CLIENT_ID, KEY_ID, TIMESTAMP, NONCE, SHA256_BODY_HASH]
     */
    public function generateHeaders(string $method, string $pathWithQuery, array $body = []): array
    {
        $timestamp = (string) time();
        $nonce     = bin2hex(random_bytes(16));
        $bodyJson  = !empty($body) ? json_encode($body, JSON_UNESCAPED_SLASHES) : '';
        $bodyHash  = hash('sha256', $bodyJson);
        $cleanPath = '/' . ltrim($pathWithQuery, '/');

        $canonical = implode("\n", [
            strtoupper($method),
            $cleanPath,
            $this->clientId,
            $this->keyId,
            $timestamp,
            $nonce,
            $bodyHash,
        ]);

        $signature = base64_encode(hash_hmac('sha256', $canonical, $this->secretKey, true));

        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'X-Client-ID'  => $this->clientId,
            'X-Key-ID'     => $this->keyId,
            'X-Timestamp'  => $timestamp,
            'X-Nonce'      => $nonce,
            'X-Signature'  => $signature,
        ];
    }

    /**
     * Helper sentral request HTTP terotentikasi HMAC dengan safe fallback.
     */
    protected function request(string $method, string $path, array $query = [], array $body = []): array
    {
        $queryString = !empty($query) ? '?' . http_build_query($query) : '';
        $pathWithQuery = $path . $queryString;
        $fullUrl = $this->baseUrl . $pathWithQuery;

        try {
            $headers = $this->generateHeaders($method, $pathWithQuery, $body);

            $options = [
                'headers' => $headers,
            ];

            if (!empty($body)) {
                $options['json'] = $body;
            }

            $response = $this->httpClient->request(strtoupper($method), $fullUrl, $options);
            $statusCode = $response->getStatusCode();
            $bodyContent = json_decode((string) $response->getBody(), true) ?? [];

            $isSuccess = ($statusCode >= 200 && $statusCode < 300) &&
                         (($bodyContent['status'] ?? '') === 'success' || ($bodyContent['success'] ?? false) === true);

            if ($isSuccess) {
                return [
                    'success' => true,
                    'message' => $bodyContent['message'] ?? 'OK',
                    'data'    => $bodyContent['data'] ?? [],
                ];
            }

            Log::warning("CIO Finance API request warning: {$method} {$pathWithQuery} returned {$statusCode}", [
                'response' => $bodyContent,
            ]);

            return [
                'success' => false,
                'message' => $bodyContent['message'] ?? "Request failed with status {$statusCode}",
                'data'    => $bodyContent['data'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error("CIO Finance API exception: {$method} {$pathWithQuery} - " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    /**
     * 1. GET /api/v1/analytics/overview
     * Ringkasan Eksekutif Saldo Konsolidasi, Revenue Bulan Ini, Laba Bersih, & Status Gateway.
     */
    public function getOverview(string $clientCode = 'ALL'): array
    {
        $res = $this->request('GET', '/api/v1/analytics/overview', ['client_code' => $clientCode]);
        if (!$res['success'] || empty($res['data'])) {
            $res['data'] = $this->getDefaultOverview();
        }
        return $res;
    }

    /**
     * 2. GET /api/v1/analytics/chart
     * Dataset time-series Inflow vs Outflow vs Net Profit untuk ApexCharts.
     */
    public function getChart(string $range = '7d', string $interval = 'daily', string $clientCode = 'ALL', ?string $date = null): array
    {
        $query = [
            'range'       => $range,
            'interval'    => $interval,
            'client_code' => $clientCode,
        ];
        if (!empty($date)) {
            $query['date'] = $date;
        }
        $res = $this->request('GET', '/api/v1/analytics/chart', $query);
        if (!$res['success'] || empty($res['data'])) {
            $res['data'] = $this->getDefaultChart($range);
        }
        return $res;
    }

    /**
     * 3. GET /api/v1/analytics/growth
     * Indikator Pertumbuhan Bisnis & Valuasi Saham (MoM, YoY, Margin, Runway, Health Score).
     */
    public function getGrowth(string $clientCode = 'ALL'): array
    {
        $res = $this->request('GET', '/api/v1/analytics/growth', ['client_code' => $clientCode]);
        if (!$res['success'] || empty($res['data'])) {
            $res['data'] = $this->getDefaultGrowth();
        }
        return $res;
    }

    /**
     * 4. GET /api/v1/history
     * Log Riwayat Mutasi Transaksi Multi-Web dengan filter dan pagination.
     */
    public function getHistory(array $params = []): array
    {
        $res = $this->request('GET', '/api/v1/history', $params);
        if (!$res['success'] || empty($res['data'])) {
            $res['data'] = [
                'items'      => [],
                'pagination' => [
                    'current_page' => (int) ($params['page'] ?? 1),
                    'last_page'    => 1,
                    'per_page'     => (int) ($params['per_page'] ?? 15),
                    'total'        => 0,
                ],
            ];
        }
        return $res;
    }

    /**
     * 5. GET /api/v1/balance
     * Rincian Saldo Manual & Saldo Xendit.
     */
    public function getBalance(): array
    {
        $res = $this->request('GET', '/api/v1/balance');
        if (!$res['success'] || empty($res['data'])) {
            $res['data'] = $this->getDefaultZeroBalance();
        }
        return $res;
    }

    /**
     * Alias method untuk kompatibilitas.
     */
    public function getActivityLogs(): array
    {
        return $this->getHistory();
    }

    /**
     * Fallback Data: Overview
     */
    protected function getDefaultOverview(): array
    {
        return [
            'balance' => [
                'total'  => 0.00,
                'manual' => 0.00,
                'xendit' => 0.00,
            ],
            'current_month' => [
                'revenue'       => 0.00,
                'expenses'      => 0.00,
                'net_profit'    => 0.00,
                'profit_margin' => 0.00,
            ],
            'previous_month' => [
                'revenue'    => 0.00,
                'expenses'   => 0.00,
                'net_profit' => 0.00,
            ],
            'active_clients_count' => 0,
            'xendit_status'        => 'connected',
        ];
    }

    /**
     * Fallback Data: Chart Time-Series
     */
    protected function getDefaultChart(string $range = '7d'): array
    {
        return [
            'categories' => [],
            'series'     => [
                ['name' => 'Pemasukan (Inflow)', 'data' => []],
                ['name' => 'Pengeluaran (Outflow)', 'data' => []],
                ['name' => 'Laba Bersih (Net Profit)', 'data' => []],
            ],
            'summary' => [
                'total_inflow'      => 0.00,
                'total_outflow'     => 0.00,
                'net_profit'        => 0.00,
                'profit_margin_pct' => 0.00,
            ],
            'web_breakdown' => [],
        ];
    }

    /**
     * Fallback Data: Growth & Valuation
     */
    protected function getDefaultGrowth(): array
    {
        return [
            'growth_rate_mom'        => 0.00,
            'growth_rate_yoy'        => 0.00,
            'financial_health_score' => 80,
            'status'                 => 'Stabil & Beroperasi Normal',
            'profitability'          => [
                'gross_margin'      => 0.00,
                'net_margin'        => 0.00,
                'monthly_burn_rate' => 0.00,
                'runway_months'     => 12.0,
            ],
            'estimated_company_valuation' => 100000000.00,
        ];
    }

    /**
     * Fallback Data: Saldo 0
     */
    protected function getDefaultZeroBalance(): array
    {
        return [
            'client_code'    => 'CIO_SAHAM_PORTAL',
            'client_name'    => 'CIO Saham & Ekuitas',
            'balance'        => '0.00',
            'balance_manual' => '0.00',
            'balance_xendit' => '0.00',
            'total_balance'  => '0.00',
            'channel_status' => [
                'manual' => true,
                'xendit' => true,
            ],
            'retrieved_at'   => now()->toIso8601String(),
        ];
    }
}
