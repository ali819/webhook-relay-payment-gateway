@extends('layouts.app')
@section('title', 'Domains')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-semibold mb-0">Domains</h5>
        <p class="text-muted small mb-0">Kelola domain & endpoint relay</p>
    </div>
    <a href="{{ route('panel.domains.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Domain
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Nama</th>
                    <th>Provider</th>
                    <th>Relay URL <span style="font-weight:normal; font-style:italic; color:grey;">(copy ini di dashboard PG)</span></th>
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
                    <td>
                        @php
                            $relayUrl = route('handleApi');
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <code class="text-muted small">{{ $relayUrl }}</code>
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2 copy-btn"
                                    data-url="{{ $relayUrl }}"
                                    data-domain="{{ $domain->domain }}"
                                    data-provider="{{ $domain->provider }}"
                                    title="Copy relay URL">
                                <i class="bi bi-copy" style="font-size:12px"></i>
                            </button>
                        </div>
                    </td>
                    <td class="text-muted small" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">
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
                            <i class="bi bi-journal-text"></i> lihat log
                        </a>
                    </td>
                    <td class="text-end pe-3">
                        <a href="{{ route('panel.domains.edit', $domain) }}"
                           class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('panel.domains.destroy', $domain) }}"
                            method="POST" class="d-inline"
                            data-confirm
                            data-confirm-title="Hapus domain?"
                            data-confirm-text="Domain {{ $domain->name }} akan dihapus permanen."
                            data-confirm-btn="Ya, hapus"
                            data-confirm-icon="warning">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                        <a href="{{ route('panel.domains.test', $domain) }}"
                        class="btn btn-sm btn-outline-secondary">Test</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
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
});

document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const url = this.dataset.url;

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
