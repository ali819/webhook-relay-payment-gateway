<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\WebhookLog;
use App\Services\WebhookForwarder;
use Illuminate\Http\Request;

class RelayController extends Controller
{
    public function __construct(
        protected WebhookForwarder $forwarder,
    ) {}

    public function handle(Request $request)
    {
        // NOTE: Relay ini TIDAK melakukan verifikasi signature.
        // Verifikasi dilakukan oleh app tujuan masing-masing.
        // MidtransVerifier & XenditVerifier tersedia di app/Services/
        // tapi sengaja tidak dipakai di sini.

        $payload  = $request->all();
        $provider = $this->detectProvider($request);
        $slug     = $this->extractDomainSlug($request, $provider);

        $domain = Domain::where('domain', $slug)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();

        if (!$domain) {
            WebhookLog::create([
                'domain_id'     => null,
                'provider'      => $provider,
                'event_type'    => $this->extractEventType($payload, $provider),
                'custom_field1' => $slug,
                'payload'       => $payload,
                'status'        => 'domain_not_found',
            ]);
            return response()->json(['message' => 'OK'], 200);
        }

        // Kumpulkan header asli yang relevan untuk diteruskan
        $forwardHeaders = array_filter([
            'X-CALLBACK-TOKEN'        => $request->header('X-CALLBACK-TOKEN'),
            'X-Midtrans-Signature'    => $request->header('X-Midtrans-Signature'),
            'X-Midtrans-Event'        => $request->header('X-Midtrans-Event'),
        ]);

        $result = $this->forwarder->forward($domain, $payload, $forwardHeaders);

        WebhookLog::create([
            'domain_id'     => $domain->id,
            'provider'      => $provider,
            'event_type'    => $this->extractEventType($payload, $provider),
            'custom_field1' => $slug,
            'payload'       => $payload,
            'response_code' => $result['response_code'],
            'duration_ms'   => $result['duration_ms'],
            'status'        => $result['status'],
            'error_message' => $result['error_message'],
        ]);

        return response()->json(['message' => 'OK'], 200);
    }

    private function detectProvider(Request $request): string
    {
        if ($request->hasHeader('X-CALLBACK-TOKEN')) {
            return 'xendit';
        }
        return 'midtrans';
    }

    private function extractDomainSlug(Request $request, string $provider): ?string
    {
        $payload = $request->all();

        return match ($provider) {
            'midtrans' => $payload['custom_field1'] ?? null,
            'xendit'   => $payload['data']['metadata']['domain']
                          ?? $payload['metadata']['domain']
                          ?? $this->parseFromExternalId($payload)
                          ?? null,
        };
    }

    private function parseFromExternalId(array $payload): ?string
    {
        $externalId = $payload['data']['reference_id']
                      ?? $payload['data']['external_id']
                      ?? $payload['reference_id']
                      ?? $payload['external_id']
                      ?? null;

        if (!$externalId || !str_contains($externalId, '|')) {
            return null;
        }

        return explode('|', $externalId)[0];
    }

    private function extractEventType(array $payload, string $provider): ?string
    {
        return match ($provider) {
            'midtrans' => $payload['transaction_status'] ?? null,
            'xendit'   => $payload['event']
                          ?? $payload['data']['status']
                          ?? null,
        };
    }
}
