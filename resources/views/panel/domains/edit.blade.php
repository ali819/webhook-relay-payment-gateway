@extends('layouts.app')
@section('title', 'Edit Domain')
@section('content')
<div class="mb-4">
    <a href="{{ route('panel.domains.index') }}" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h5 class="fw-semibold mb-0 mt-1">Edit Domain</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body p-4">
        <form action="{{ route('panel.domains.update', $domain) }}" method="POST" autocomplete="off">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-medium">Provider <span class="text-danger">*</span></label>
                <select name="provider" id="provider" class="form-select"
                        onchange="onProviderChange(this.value)">
                    <option value="midtrans" {{ old('provider', $domain->provider) == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                    <option value="xendit"   {{ old('provider', $domain->provider) == 'xendit'   ? 'selected' : '' }}>Xendit</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Nama <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $domain->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Domain <span class="text-danger">*</span></label>
                <input type="text" name="domain" id="domain"
                    class="form-control @error('domain') is-invalid @enderror"
                    value="{{ old('domain', $domain->domain) }}" placeholder="contoh: domain.com">
                <div id="domain-hint" class="form-text"></div>
                @if($domain->logs()->exists())
                    <div class="form-text text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Domain ini sudah punya log. Mengubahnya bisa menyebabkan webhook baru tidak terdeteksi jika nilai di transaksi belum diperbarui.
                    </div>
                @endif
                @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Target URL <span class="text-danger">*</span></label>
                <input type="url" name="target_url" class="form-control @error('target_url') is-invalid @enderror"
                       value="{{ old('target_url', $domain->target_url) }}">
                @error('target_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Secret Key <span class="text-danger">*</span></label>
                <input type="text" name="secret_key" id="secret_key"
                       class="form-control @error('secret_key') is-invalid @enderror"
                       value="{{ old('secret_key', $domain->secret_key) }}">
                <div id="secret-hint" class="form-text"></div>
                @error('secret_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                       {{ old('is_active', $domain->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>

            <div class="border-top pt-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Perbarui</button>
                <a href="{{ route('panel.domains.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>

        </form>
    </div>
</div>

<script>
const hints = {
    midtrans: {
        domain: 'Isi nilai ini di <code>custom_field1</code> saat membuat transaksi Midtrans.',
        secret: 'Server Key Midtrans — dipakai untuk validasi signature webhook.',
        secretPlaceholder: 'SB-Mid-server-xxxxxxxxxxxxxxxx',
    },
    xendit: {
        domain: 'Isi nilai ini di <code>metadata.domain</code> saat membuat transaksi Xendit.',
        secret: 'Callback Token Xendit — ada di Settings → Developers → Webhook.',
        secretPlaceholder: 'xnd_xxxxxxxxxxxxxxxx',
    },
};

function onProviderChange(val) {
    document.getElementById('domain-hint').innerHTML = hints[val].domain;
    document.getElementById('secret-hint').innerHTML = hints[val].secret;
    document.getElementById('secret_key').placeholder = hints[val].secretPlaceholder;
}

document.addEventListener('DOMContentLoaded', () => {
    onProviderChange(document.getElementById('provider').value);
});
</script>
@endsection
