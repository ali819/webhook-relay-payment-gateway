<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Support\Facades\Http;

class WebhookForwarder
{
    public function forward(Domain $domain, array $payload): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(10)
                ->post($domain->target_url, $payload);

            return [
                'response_code' => $response->status(),
                'duration_ms'   => (int) ((microtime(true) - $start) * 1000),
                'status'        => $response->successful() ? 'success' : 'failed',
                'error_message' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'response_code' => null,
                'duration_ms'   => (int) ((microtime(true) - $start) * 1000),
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
