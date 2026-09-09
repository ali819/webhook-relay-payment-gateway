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
            // DOKU (Checkout v2) — target app butuh header ini untuk verifikasi signature
            'Client-Id'               => $request->header('Client-Id'),
            'Request-Id'              => $request->header('Request-Id'),
            'Request-Timestamp'       => $request->header('Request-Timestamp'),
            'Signature'               => $request->header('Signature'),
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

        $payload = $request->all();

        // DOKU kirim header Client-Id + Signature, dan payload berbentuk
        // { order: {...}, transaction: {...} }
        if ($request->hasHeader('Client-Id') && $request->hasHeader('Signature')) {
            return 'doku';
        }
        if (isset($payload['order']['invoice_number']) && isset($payload['transaction']['status'])) {
            return 'doku';
        }

        // Midtrans selalu ada signature_key & transaction_status di payload
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

        $slug = match ($provider) {
            'midtrans' => $this->findValueByKey($payload, 'custom_field1'),
            'xendit'   => $this->findMetadataDomain($payload),
            // DOKU: domain diambil dari additional_info, di mana pun letaknya.
            'doku'     => $this->findAdditionalInfoDomain($payload)
                          ?? $this->findMetadataDomain($payload),
            default    => null,
        };

        // Jalan terakhir: alias di akhir nomor invoice (cara opsional).
        return $slug ?? $this->resolveByAlias($payload);
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

    /**
     * Cara opsional: domain dikenali dari alias 3 karakter di akhir nomor
     * invoice, dipisah tanda hubung — mis. "INV-08314-S8K" untuk alias S8K.
     *
     * Tanda hubung tepat sebelum 3 karakter terakhir itu wajib: tanpa itu
     * akhiran apa pun diabaikan, supaya nomor invoice yang kebetulan
     * berakhiran 3 karakter tidak ikut tertangkap. Alias juga harus benar-benar
     * terdaftar; kalau tidak ada di tabel domains, hasilnya null (bukan tebakan).
     */
    private function resolveByAlias(array $payload): ?string
    {
        $invoice = $payload['order']['invoice_number']
                   ?? $payload['data']['external_id']
                   ?? $payload['data']['reference_id']
                   ?? $payload['external_id']
                   ?? $payload['reference_id']
                   ?? $payload['order_id']
                   ?? null;

        if (!is_string($invoice)) {
            return null;
        }

        // Alias selalu disimpan kapital. Nomor invoice dari payment gateway
        // dikapitalkan dulu supaya aplikasi yang menulis alias huruf kecil —
        // atau PG yang mengubah casing — tetap cocok.
        $invoice = strtoupper(trim($invoice));

        if (!preg_match('/-([A-Z0-9]{3})$/', $invoice, $m)) {
            return null;
        }

        // Kembalikan domain-nya, bukan langsung row: pencocokan provider &
        // status aktif tetap dikerjakan pemanggil seperti jalur identifier biasa.
        return Domain::where('alias', $m[1])->value('domain');
    }

    /**
     * Scan payload secara rekursif untuk menemukan domain di dalam
     * "additional_info" (format DOKU), di mana pun letaknya.
     */
    private function findAdditionalInfoDomain(array $payload): ?string
    {
        foreach ($payload as $key => $value) {
            if ($key === 'additional_info' && is_array($value) && !empty($value['domain'])) {
                return (string) $value['domain'];
            }

            if (is_array($value)) {
                $found = $this->findAdditionalInfoDomain($value);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function extractEventType(array $payload, string $provider): ?string
    {
        return match ($provider) {
            'midtrans' => $payload['transaction_status'] ?? null,
            'xendit'   => $payload['event']
                          ?? $payload['data']['status']
                          ?? null,
            'doku'     => $payload['transaction']['status']
                          ?? $payload['service']['id']
                          ?? null,
            default    => null,
        };
    }
}
