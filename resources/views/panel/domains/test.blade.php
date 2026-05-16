@extends('layouts.app')
@section('title', 'Test Webhook')
@section('content')
<div class="mb-4">
    <a href="{{ route('panel.domains.index') }}" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h5 class="fw-semibold mb-0 mt-1">Test Webhook — {{ $domain->name }}</h5>
</div>

@if(session('test_result'))
    @php $result = session('test_result'); @endphp
    <div class="alert {{ $result['status'] === 'success' ? 'alert-success' : 'alert-danger' }} alert-dismissible fade show mb-4">
        <strong>{{ $result['status'] === 'success' ? 'Berhasil!' : 'Gagal!' }}</strong>
        HTTP {{ $result['response_code'] ?? '-' }} · {{ $result['duration_ms'] }}ms
        @if($result['error_message'])
            <br><small>{{ $result['error_message'] }}</small>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">Info domain</h6>
                <table class="table table-sm small mb-0">
                    <tr>
                        <td class="text-muted" style="width:100px">Provider</td>
                        <td><span class="badge badge-{{ $domain->provider }}">{{ ucfirst($domain->provider) }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Domain</td>
                        <td><code>{{ $domain->domain }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Target URL</td>
                        <td class="small"><code>{{ $domain->target_url }}</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0">Payload JSON</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadSample()">
                            <i class="bi bi-file-code me-1"></i>Load sample
                        </button>
                    </div>
                </div>

                <form action="{{ route('panel.domains.test.send', $domain) }}" method="POST"
                    data-confirm
                    data-confirm-title="Kirim test webhook?"
                    data-confirm-text="Payload akan dikirim langsung ke target URL domain ini."
                    data-confirm-btn="Ya, kirim"
                    data-confirm-icon="question">
                    @csrf
                    <div class="mb-3">
                        <textarea name="payload" id="payload" rows="14"
                                  class="form-control font-monospace small @error('payload') is-invalid @enderror"
                                  placeholder='{"key": "value"}'></textarea>
                        @error('payload')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-send me-1"></i>Kirim ke target URL
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const samples = {
    midtrans: {
        transaction_status: "settlement",
        order_id: "TEST-{{ time() }}",
        gross_amount: "100000.00",
        payment_type: "gopay",
        custom_field1: "{{ $domain->domain }}",
        signature_key: "test-signature",
    },
    xendit: {
        id: "test-{{ time() }}",
        external_id: "TEST-{{ time() }}",
        status: "PAID",
        amount: 100000,
        metadata: {
            domain: "{{ $domain->domain }}"
        },
    },
};

function loadSample() {
    document.getElementById('payload').value = JSON.stringify(
        samples['{{ $domain->provider }}'],
        null, 2
    );
}
</script>
@endsection
