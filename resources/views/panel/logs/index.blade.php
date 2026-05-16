@extends('layouts.app')
@section('title', 'Logs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-semibold mb-0">Webhook Logs</h5>
        <p class="text-muted small mb-0">Riwayat semua webhook masuk</p>
    </div>
    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#pruneModal">
        <i class="bi bi-trash me-1"></i>Bersihkan log
    </button>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" autocomplete="off" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Domain</label>
                <select name="domain_id" class="form-select form-select-sm">
                    <option value="">Semua domain</option>
                    @foreach($domains as $d)
                        <option value="{{ $d->id }}" {{ request('domain_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Provider</label>
                <select name="provider" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="midtrans" {{ request('provider') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                    <option value="xendit"   {{ request('provider') == 'xendit'   ? 'selected' : '' }}>Xendit</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="success"           {{ request('status') == 'success'           ? 'selected' : '' }}>Sukses</option>
                    <option value="failed"            {{ request('status') == 'failed'            ? 'selected' : '' }}>Gagal</option>
                    <option value="invalid_signature" {{ request('status') == 'invalid_signature' ? 'selected' : '' }}>Signature invalid</option>
                    <option value="domain_not_found"  {{ request('status') == 'domain_not_found'  ? 'selected' : '' }}>Domain tidak ditemukan</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-dark btn-sm">Filter</button>
                <a href="{{ route('panel.logs.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Waktu</th>
                    <th>Domain</th>
                    <th>Provider</th>
                    <th>Event</th>
                    <th>Domain key</th>
                    <th>Status</th>
                    <th>HTTP</th>
                    <th>Durasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="cursor:pointer" onclick="window.location='{{ route('panel.logs.show', $log) }}'">
                    <td class="ps-3 text-muted">{{ $log->created_at->format('d/m H:i:s') }}</td>
                    <td>{{ $log->domain?->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $log->provider }}">{{ ucfirst($log->provider) }}</span></td>
                    <td class="text-muted">{{ $log->event_type ?? '-' }}</td>
                    <td><code class="text-muted">{{ $log->custom_field1 ?? '-' }}</code></td>
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
                    <td class="text-muted">{{ $log->response_code ?? '-' }}</td>
                    <td class="text-muted">{{ $log->duration_ms ? $log->duration_ms . 'ms' : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">Belum ada log.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links() }}
</div>

{{-- Modal prune --}}
<div class="modal fade" id="pruneModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold">Bersihkan log</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('panel.logs.prune') }}" method="POST"
                data-confirm
                data-confirm-title="Bersihkan log?"
                data-confirm-text="Log lama akan dihapus permanen dan tidak bisa dikembalikan."
                data-confirm-btn="Ya, hapus"
                data-confirm-icon="warning">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <p class="text-muted small mb-3">Pilih berapa log terbaru yang ingin <strong>dipertahankan</strong>. Log sisanya akan dihapus permanen.</p>
                    <div class="d-flex flex-column gap-2">
                        <label class="border rounded p-3 d-flex align-items-center gap-3" style="cursor:pointer">
                            <input type="radio" name="keep" value="1000" checked class="form-check-input mt-0">
                            <div>
                                <div class="fw-medium">Sisakan 1.000 log terbaru</div>
                                <div class="text-muted small">Hapus semua log di luar 1.000 terbaru</div>
                            </div>
                        </label>
                        <label class="border rounded p-3 d-flex align-items-center gap-3" style="cursor:pointer">
                            <input type="radio" name="keep" value="100" class="form-check-input mt-0">
                            <div>
                                <div class="fw-medium">Sisakan 100 log terbaru</div>
                                <div class="text-muted small">Hapus semua log di luar 100 terbaru</div>
                            </div>
                        </label>
                        <label class="border rounded p-3 d-flex align-items-center gap-3" style="cursor:pointer">
                            <input type="radio" name="keep" value="50" class="form-check-input mt-0">
                            <div>
                                <div class="fw-medium">Sisakan 50 log terbaru</div>
                                <div class="text-muted small">Hapus semua log di luar 50 terbaru</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>Hapus sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
