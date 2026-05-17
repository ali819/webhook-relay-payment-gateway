@extends('layouts.app')
@section('title', 'Domains')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-semibold mb-0">Domains</h5>
        <p class="text-muted small mb-0">Kelola domain & endpoint relay</p>
    </div>
    <a href="{{ route('panel.domains.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Domain
    </a>
</div>

{{-- Relay URL info --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-danger">* Catatan : </span><span class="small text-muted">Webhook URL <span class="fst-italic">(daftarkan ini di dashboard Midtrans/Xendit):</span></span>
            <code class="bg-light px-3 py-1 rounded small" id="relay-url">{{ route('handleApi') }}</code>
            <button class="btn btn-sm btn-outline-secondary py-0 px-2" id="copy-relay-btn" title="Copy relay URL">
                <i class="bi bi-copy" style="font-size:12px"></i>
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Nama</th>
                    <th>Provider</th>
                    <th>Target URL</th>
                    <th>Status</th>
                    <th>Log</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($domains as $domain)
                <tr>
                    <td class="ps-3 fw-medium">{{ $domain->name }}</td>
                    <td>
                        <span class="badge badge-{{ $domain->provider }}">{{ ucfirst($domain->provider) }}</span>
                    </td>
                    <td class="text-muted small" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">
                        {{ $domain->target_url }}
                    </td>
                    <td>
                        @if($domain->is_active)
                            <span class="badge bg-success-subtle text-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('panel.logs.index', ['domain_id' => $domain->id]) }}"
                           class="text-muted small text-decoration-none">
                            <i class="bi bi-journal-text"></i> lihat
                        </a>
                    </td>
                    <td class="text-end pe-3">
                        <a href="{{ route('panel.domains.edit', $domain) }}" class="btn btn-sm btn-outline-secondary mt-2">Edit</a>
                        <form action="{{ route('panel.domains.destroy', $domain) }}"
                            method="POST" class="d-inline"
                            data-confirm
                            data-confirm-title="Hapus domain?"
                            data-confirm-text="Domain {{ $domain->name }} akan dihapus permanen."
                            data-confirm-btn="Ya, hapus"
                            data-confirm-icon="warning">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger mt-2">Hapus</button>
                        </form>
                        <a href="{{ route('panel.domains.test', $domain) }}" class="btn btn-sm btn-outline-secondary mt-2">Test</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        Belum ada domain. <a href="{{ route('panel.domains.create') }}">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $domains->links() }}
</div>

{{-- Toast notif copy --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="copyToast" class="toast align-items-center text-bg-dark border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>Relay URL berhasil disalin
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
let toast;

document.addEventListener('DOMContentLoaded', function () {
    toast = new bootstrap.Toast(document.getElementById('copyToast'), { delay: 2000 });

    document.getElementById('copy-relay-btn').addEventListener('click', function () {
        const url = document.getElementById('relay-url').innerText.trim();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => showCopied(this));
        } else {
            const el = document.createElement('textarea');
            el.value = url;
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showCopied(this);
        }
    });
});

function showCopied(btn) {
    const icon = btn.querySelector('i');
    icon.classList.replace('bi-copy', 'bi-check-lg');
    toast.show();
    setTimeout(() => icon.classList.replace('bi-check-lg', 'bi-copy'), 2000);
}
</script>
@endsection
