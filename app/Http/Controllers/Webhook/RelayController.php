<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\WebhookLog;
use App\Services\MidtransVerifier;
use App\Services\WebhookForwarder;
use App\Services\XenditVerifier;
use Illuminate\Http\Request;

class RelayController extends Controller
{
    public function __construct(
        protected MidtransVerifier $midtrans,
        protected XenditVerifier   $xendit,
        protected WebhookForwarder $forwarder,
    ) {}

    public function handle(Request $request)
    {
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
            return response()->json(['message' => 'Domain not found'], 404);
        }

        $valid = match ($provider) {
            'midtrans' => $this->midtrans->verify($payload, $domain),
            'xendit'   => $this->xendit->verify($request, $domain),
        };

        if (!$valid) {
            WebhookLog::create([
                'domain_id'     => $domain->id,
                'provider'      => $provider,
                'event_type'    => $this->extractEventType($payload, $provider),
                'custom_field1' => $slug,
                'payload'       => $payload,
                'status'        => 'invalid_signature',
            ]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $result = $this->forwarder->forward($domain, $payload);

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

        return response()->json(['message' => 'Forwarded'], 200);
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
            'xendit'   => $payload['data']['metadata']['domain']  // Payment Request V2/V3 ✓
                        ?? $payload['metadata']['domain']        // QRIS lama
                        ?? $this->parseFromExternalId($payload)  // FVA lama fallback
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
            'xendit'   => $payload['event']                        // Payment Request format
                        ?? $payload['data']['status']             ?? null,
        };
    }
}
