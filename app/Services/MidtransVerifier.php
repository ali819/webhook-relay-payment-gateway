<?php

namespace App\Services;

use App\Models\Domain;

/**
 * NOTE: Verifier ini TIDAK digunakan oleh relay.
 * Disediakan sebagai referensi atau jika suatu saat
 * relay perlu diaktifkan verifikasinya.
 * Verifikasi dilakukan oleh app tujuan (target_url).
 */
class MidtransVerifier
{
    public function verify(array $payload, Domain $domain): bool
    {
        $orderId           = $payload['order_id'] ?? '';
        $statusCode        = $payload['status_code'] ?? '';
        $grossAmount       = $payload['gross_amount'] ?? '';
        $serverKey         = $domain->secret_key;

        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($signatureKey, $payload['signature_key'] ?? '');
    }
}
