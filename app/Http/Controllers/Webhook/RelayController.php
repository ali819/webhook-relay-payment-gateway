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

        // Jika provider tidak dikenali, log dan return OK
        if ($provider === 'unknown') {
            WebhookLog::create([
                'domain_id'     => null,
                'provider'      => 'unknown',
                'event_type'    => null,
                'custom_field1' => null,
                'payload'       => $payload,
                'status'        => 'domain_not_found',
            ]);
            return response()->json(['message' => 'OK'], 200);
        }

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
        // Xendit selalu kirim X-CALLBACK-TOKEN
        if ($request->hasHeader('X-CALLBACK-TOKEN')) {
            return 'xendit';
        }

        // Midtrans selalu ada signature_key & transaction_status di payload
        $payload = $request->all();
        if (isset($payload['signature_key']) && isset($payload['transaction_status'])) {
            return 'midtrans';
        }

        // Fallback — coba tebak dari struktur payload
        if (isset($payload['custom_field1'])) {
            return 'midtrans';
        }

        return 'unknown';
    }

    private function extractDomainSlug(Request $request, string $provider): ?string
    {
        $payload = $request->all();

        return match ($provider) {
            'midtrans' => $this->findValueByKey($payload, 'custom_field1'),
            'xendit'   => $this->findMetadataDomain($payload)
                          ?? $this->parseFromExternalId($payload)
                          ?? null,
        };
    }

    /**
     * Scan payload secara rekursif untuk menemukan nilai dari sebuah key,
     * di mana pun lokasinya (top-level maupun nested). Hanya menerima nilai
     * skalar non-kosong. Mengembalikan kecocokan pertama (depth-first).
     */
    private function findValueByKey(array $payload, string $target): ?string
    {
        foreach ($payload as $key => $value) {
            if ($key === $target && is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }

            if (is_array($value)) {
                $found = $this->findValueByKey($value, $target);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Scan payload secara rekursif untuk menemukan domain di dalam "metadata",
     * di mana pun lokasinya (top-level metadata, data.metadata, qr_code.metadata, dst).
     * Mengembalikan domain pertama yang valid (non-empty).
     */
    private function findMetadataDomain(array $payload): ?string
    {
        foreach ($payload as $key => $value) {
            // Ketemu blok "metadata" yang punya "domain" terisi
            if ($key === 'metadata' && is_array($value) && !empty($value['domain'])) {
                return $value['domain'];
            }

            // Telusuri lebih dalam jika nilainya array
            if (is_array($value)) {
                $found = $this->findMetadataDomain($value);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
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
