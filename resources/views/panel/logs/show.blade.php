@extends('layouts.app')
@section('title', 'Detail Log')
@section('content')
<div class="mb-4">
    <a href="{{ route('panel.logs.index') }}" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Logs
    </a>
    <h5 class="fw-semibold mb-0 mt-1">Detail Log</h5>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">

    {{-- Info umum --}}
    <div class="col-12 col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-semibold mb-3">Informasi</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 small">
                        <tr>
                            <td class="text-muted" style="width:120px; white-space:nowrap">Waktu</td>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Domain</td>
                            <td>{{ $log->domain?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Provider</td>
                            <td><span class="badge badge-{{ $log->provider }}">{{ ucfirst($log->provider) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Event</td>
                            <td>{{ $log->event_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Domain key</td>
                            <td><code class="text-break">{{ $log->custom_field1 ?? '-' }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php
                                    $badge = match($log->status) {
                                        'success'            => 'bg-success-subtle text-success',
                                        'failed'             => 'bg-danger-subtle text-danger',
                                        'invalid_signature'  => 'bg-warning-subtle text-warning',
                                        'domain_not_found'   => 'bg-secondary-subtle text-secondary',
                                        default              => 'bg-light text-muted',
                                    };
                                    $label = match($log->status) {
                                        'success'            => 'Sukses',
                                        'failed'             => 'Gagal',
                                        'invalid_signature'  => 'Signature invalid',
                                        'domain_not_found'   => 'Domain tidak ditemukan',
                                        default              => $log->status,
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $label }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">HTTP</td>
                            <td>{{ $log->response_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Durasi</td>
                            <td>{{ $log->duration_ms ? $log->duration_ms . ' ms' : '-' }}</td>
                        </tr>
                        @if($log->error_message)
                        <tr>
                            <td class="text-muted">Error</td>
                            <td class="text-danger small text-break">{{ $log->error_message }}</td>
                        </tr>
                        @endif
                        @if($log->domain?->target_url)
                        <tr>
                            <td class="text-muted">Target URL</td>
                            <td><code class="small text-break">{{ $log->domain->target_url }}</code></td>
                        </tr>
                        @endif
                    </table>
                </div>

                {{-- Retry --}}
                @if(in_array($log->status, ['failed', 'invalid_signature']) && $log->domain)
                <div class="border-top pt-3 mt-3">
                    <form action="{{ route('panel.logs.retry', $log) }}" method="POST"
                          data-confirm
                          data-confirm-title="Retry webhook?"
                          data-confirm-text="Payload akan dikirim ulang ke target URL."
                          data-confirm-btn="Ya, kirim ulang"
                          data-confirm-icon="question">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-dark w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i>Retry sekarang
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Payload --}}
    <div class="col-12 col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0">Payload</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="copyPayload(this)">
                        <i class="bi bi-copy me-1"></i>Copy
                    </button>
                </div>
                <pre id="payload-json"
                     class="bg-light rounded p-3 small mb-0"
                     style="max-height:1000px; overflow-y:auto; overflow-x:auto; word-break:break-all; white-space:pre-wrap">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>

</div>

<script>
function copyPayload(btn) {
    const text = document.getElementById('payload-json').innerText;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => showCopied(btn));
    } else {
        const el = document.createElement('textarea');
        el.value = text;
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showCopied(btn);
    }
}

function showCopied(btn) {
    const icon = btn.querySelector('i');
    icon.classList.replace('bi-copy', 'bi-check-lg');
    setTimeout(() => icon.classList.replace('bi-check-lg', 'bi-copy'), 2000);
}
</script>
@endsection
