<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Domain;

class XenditVerifier
{
    public function verify(Request $request, Domain $domain): bool
    {
        $token = $request->header('X-CALLBACK-TOKEN');
        return hash_equals($domain->secret_key, $token ?? '');
    }
}
