<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Domain;
use Illuminate\Support\Facades\Log;

/**
 * NOTE: Verifier ini TIDAK digunakan oleh relay.
 * Disediakan sebagai referensi atau jika suatu saat
 * relay perlu diaktifkan verifikasinya.
 * Verifikasi dilakukan oleh app tujuan (target_url).
 */
class XenditVerifier
{
    public function verify(Request $request, Domain $domain): bool
    {
        $token = $request->header('X-CALLBACK-TOKEN');
        // Log::info('Xendit verify', [
        //     'token_received' => $token,
        //     'token_expected' => $domain->secret_key,
        //     'match'          => hash_equals($domain->secret_key, $token ?? ''),
        // ]);
        return hash_equals($domain->secret_key, $token ?? '');
    }
}
